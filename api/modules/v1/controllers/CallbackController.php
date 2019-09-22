<?php
/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2018/11/19
 * Time: 9:55
 */

namespace Api;


use system\core\utils\Tool;
use system\modules\payment\models\paymentApp;
use system\modules\payment\models\PaymentDetail;
use Yii;

class CallbackController extends BaseApiController
{

    public $notAuthAction = ['*'];

    /**
     * @info 所有的支付方式都在这里回调
     */
    public function actionNotify($type)
    {
        if (!isset($type)) {
            echo '缺少支付参数';
            exit();
        }

        //返回网页内容  包括xml字符串  以及数组
        $type == 'wechat' ? $postStr = Yii::$app->request->getRawBody() : $postStr = $_POST;

        //支付的类文件
        $payClass = Yii::$app->systemConfig->getValue('PAYMENT_PAY_CLASS');

        //支付类型
        $classType = array_keys($payClass);

        //是否存在
        if (in_array($type, $classType)) {

            $result = $payClass[$type]::callback($postStr);

            Yii::info('result'.$result['msg']);
//            die;
            if ($result['code'] == 1) {
                echo $result['msg'];
                exit();
            }

            $updateData = [
                'pay_status' => $result['pay_status'],
                'pay_time' => $result['pay_time'],
                'data' => $result['data'],
                'pay_user_id' => $result['pay_user_id'] ? $result['pay_user_id'] : false,
                'transaction_id' => $result['transaction_id'],
            ];

            $res = Yii::$app->db->createCommand()->update('tab_payment_detail', $updateData,
                ['trade_no' => $result['out_trade_no']])->execute();

            if (!$res) {
                echo 'app error';
                exit;
            }
            //是否有回调
            $order = PaymentDetail::getOneData($result['out_trade_no']);
            //获取支付配置
            $paymentApp = paymentApp::getOneData($order->app_code);
            //是否有回调
            $url = $paymentApp->notify_class;

            if (!$url) {
                echo 'no callback';
                exit();
            }

            $secret = $paymentApp->secret;

            if (!$secret) {
                echo 'no secret';
                exit();
            }
            $order = PaymentDetail::getOneData($result['out_trade_no']);
            //向第三方平台发送数据
            $thirdData = [
                'third_order_number' => $order->third_order_number,//第三方的订单号
                'trade_no' => $order->trade_no,//平台的订单号
                'pay_type' => $order->pay_type,//支付方式
                'total_fee' => $order->total_fee,//支付金额
                'pay_status' => $order->pay_status,//支付状态
                'username' => $order->user_id,//用户名
                'pay_end' => $order->pay_time,//支付完成时间
            ];
            //过滤空的字符串
            $thirdData = array_filter($thirdData);
            //签名·
            $thirdData['sign'] = PaymentDetail::getSign($thirdData, $secret);
            Yii::info('Url:' . $url);

            Yii::info('thirdData:' . json_encode($thirdData));
            //发送数据到第三方平台
            $resultStatus = Tool::postData($url, $thirdData);

            Yii::info('resultStatus:' . $resultStatus);
            //解析json字符串
            $resultStatus = json_decode($resultStatus, true);

            if ($resultStatus['code'] == 1) {
                return '回调失败';
            }

            Yii::$app->db->createCommand()->update('tab_payment_detail', [
                'pay_status' => 2,  // 支付已完成
            ], ['trade_no' => $result['out_trade_no']])->execute();
            //通知支付的应用
            return $result['msg'];
        }
        echo 'error';
        exit();
    }

    /**
     * @info 所有的支付方式同步回调
     * @return string
     */
    public function actionSynchro($type)
    {
        if (!isset($type)) {
            echo '缺少支付参数';
            exit();
        }
        Yii::trace('msg', $type);
        //支付的类文件
        $payClass = Yii::$app->systemConfig->getValue('PAYMENT_PAY_CLASS');

        //支付类型
        $classType = array_keys($payClass);

        $data = $_GET;
        if (in_array($type, $classType)) {
            $result = $payClass[$type]::sync($data);

            if ($result['code'] == 0) {
                if (isset($result['fee'])) {
                    $url = $result['url'] . "?" . 'fee=' . $result['fee'];
                } else {
                    $url = $result['url'];
                }
                header('location:' . $url);
            }

            return $result['msg'];
        }

        echo '支付方式不存在';
        exit();
    }

    /**
     * @info 微信退款异步回调
     */
    public function actionRefund()
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

        $result = (array)$postObj;//类型转换

        print_r($result);

    }
}