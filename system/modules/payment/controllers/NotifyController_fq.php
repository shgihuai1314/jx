<?php
/**
 * 支付宝微信的回调的控制器
 * Created by PhpStorm.
 * User: THINKPAD
 * Date: 2018/5/4
 * Time: 16:25
 */

namespace system\modules\payment\controllers;

use system\modules\payment\models\WxPay;
use Yii;
use yii\web\Controller;
use system\core\utils\Tool;
use system\modules\payment\models\paymentApp;
use system\modules\payment\models\PayTrade;
use system\modules\payment\models\Alipay;

class NotifyController extends Controller
{

    public $enableCsrfValidation = false;

    /**
     *微信支付的回调
     */
    public function actionNotify()
    {
        //  配置项：微信支付参数
        //$postStr = $GLOBALS["HTTP_RAW_POST_DATA"]; //此方法跟$_POST差不多
        $postStr = Yii::$app->request->getRawBody();//返回网页的内容
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($postObj === false) {
            die('parse xml error');
        }

        if ($postObj->return_code != 'SUCCESS') {
            die($postObj->return_msg);
        }

        if ($postObj->result_code != 'SUCCESS') {
            die($postObj->err_code);
        }

        $arr = (array)$postObj;

        $pay_config = paymentApp::getConfig($arr['attach'], 'wechat');
        //获取openid
        isset($arr['sub_openid']) ? $openid = $arr['sub_openid'] : $openid = $arr['openid'];

        //验证签名
        unset($arr['sign']);
        if (WxPay::getSign($arr, $pay_config['key']) != $postObj->sign) {
            exit('sing error');
        }

        //查询订单
        $pay_msg = PayTrade::findOne(['transaction_id' => $arr['transaction_id']]);
        if (!$pay_msg) {
            //isset($arr['sub_openid'])?$openid=$arr['sub_openid']:$openid=$arr['openid'];
            $updateData = [
                'state' => 1,
                'pay_at' => strtotime($arr['time_end']),
                'data' => WxPay::arrayToXml($arr),
                'pay_user_id' => $openid,
                'transaction_id' => $arr['transaction_id'],
            ];

            $res = Yii::$app->db->createCommand()->update('tab_pay_trade', $updateData,
                ['trade_no' => $arr['out_trade_no']])->execute();

            if (!$res) {
                echo 'app error';
                exit;
            }
        }

        //是否有回调，验证是否充值到微信
        if ($arr['attach']) {
            $model = paymentApp::findOne(['code' => $arr['attach']]);
            //Yii::trace('info', 'userName:' . $model->notify_class);
            if (!$model->notify_class) {
                exit('callback error');
            }

            $params = [
                'appid' => $pay_config['appid'], // 服务商的appid
                'mch_id' => $pay_config['mch_id'], // 服务商的商户号
                'sub_appid' => $pay_config['sub_appid'], // 湖北大学企业号的corpid
                'sub_mch_id' => $pay_config['sub_mch_id'], // 湖北大学微信支付商户号
                'out_trade_no' => $arr['out_trade_no'], //本地订单号
                'nonce_str' => Yii::$app->security->generateRandomString(), //随机字符串
            ];

            // 排序
            ksort(array_filter($params));
            // 生成签名 // 注意：在组成加密字符串的时候是不需要urlencdoe的
            //$stringA = http_build_query($params)."&key=".$key;
            $stringArr = [];
            foreach ($params as $key => $value) {
                $stringArr[] = $key . '=' . $value;
            }
            $stringA = implode($stringArr, '&') . "&key=" . $this->pay_key;
            $sign = strtoupper(md5($stringA));

            $params['sign'] = $sign;
            // 转化为xml格式
            $post = Tool::arrayToXml($params);
            // 提交数据
            $res = Tool::postData($model->notify_class, $post);

            $data = (array)simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);

            if (isset($data['trade_state']) && $data['trade_state'] == 'SUCCESS') {
                Yii::$app->db->createCommand()->update('tab_pay_trade', [
                    'state' => 2,  // 支付已完成
                ], ['trade_no' => $arr['out_trade_no']])->execute();
            }
        }

        //支付完成给微信发送通知
        return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }

    /**
     * 支付宝同步回调
     */
    public function actionSynchro()
    {

        //验证签名
        $result = Alipay::rsaCheck($_GET);
        //print_r($_GET);
        if ($result === true) {
            // todo 同步回调一般不处理业务逻辑，显示一个付款成功的页面，或者跳转到别的页面。
            //todo 这个跳转地址可在,可在支付设置里面设置回调地址，然后直接跳转，列如：header('location:$_GET['body']);
            //这里暂时随便写个跳转地址，实现逻辑
            header('location:http://test.shuidinet.com/mobile.php');
            //业务编码
            echo '<h1>付款成功</h1>';
        }
        //print_r($result);die;
        echo '不合法的请求';
        exit();
    }

    /**
     * 支付宝异步回调
     */
    public function actionAlipay()
    {
        //验证签名
        $result = Alipay::rsaCheck($_POST);
        if ($result === true) {
            $updateData = [
                'state' => 1,
                'pay_at' => strtotime($_POST['gmt_payment']),
                'data' => WxPay::arrayToXml($_POST),
                'pay_user_id' => $_POST['buyer_id'],
                'transaction_id' => $_POST['trade_no'],
            ];
            $res = Yii::$app->db->createCommand()->update('tab_pay_trade', $updateData,
                ['trade_no' => $_POST['out_trade_no']])->execute();
            if (!$res) {
                echo 'app error';
                exit;
            }

            echo 'success';
            exit();//通知支付宝
        }
        echo 'error';
        exit();
    }
}