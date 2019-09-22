<?php
/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2018/5/22
 * Time: 17:38
 */

namespace console\controllers;

use system\modules\payment\models\paymentApp;
use system\modules\payment\models\PayTrade;
use Yii;
use system\modules\user\models\User;
use yii\console\Controller;
use system\core\utils\Tool;
use system\modules\srun\models\Srun4k;

class MsgController extends Controller
{
    // 支付参数
    public $pay_params = [
        'appid' => 'wx2168ef4b1d5f7c32',  // 服务商的appid
        'mch_id' => '1280082001',         // 服务商的商户号

        'sub_appid' => 'wxe56f45400adf0902', // 湖北大学企业号的corpid
        'sub_mch_id' => '1486477892',        // 湖北大学微信支付商户号
    ];
    // 微信商户的安全支付key
    private $pay_key = 'changshajundaxinxijishuyouxiangs';

    // 微信支付url
    private $pay_url = 'https://api.mch.weixin.qq.com/pay/orderquery';

    /**
     * 测试消息
     */
    public function actionTest()
    {

        $arr = Srun4k::viewProduct('test03');
        \Yii::$app->systemMessage->send('pay_messge_notify', '2', [
            'name' => 'test03',
            'balance' => '0.01',
            'url' => 'srun/mobile/profile',
        ]);
    }

    /**
     * 发送欠费提醒消息
     */
    public function actionSend()
    {
        $result = User::find()->asArray()->where(['position_id' => 1])->all();
        foreach ($result as $key => $val) {
            $allProduct = Srun4k::viewProduct($val['username']);
            if ($allProduct['data'][0]['products_id'] == 3) {
                if ($allProduct['data'][0]['user_balance'] == 0.00 || $allProduct['data'][0]['user_balance'] < 10) {
                    \Yii::$app->systemMessage->send('pay_messge_notify', $val['user_id'], [
                        'name' => $val['username'],
                        'balance' => $allProduct['data'][0]['user_balance'],
                        'url' => '/srun/mobile/profile',
                    ]);
                }
            }
        }
    }


    //微信对账
    public function actionWechatRecon()
    {
        //----------日志---------------
        $log_dir = Yii::getAlias('@webroot') . "/log/wechat/" . date('Y', time()) . "/" . date('m', time()) . "/";
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        $dir = $log_dir . date('Y-m-d', time()) . '.log';
        $file = fopen($dir, 'a') or die("Unable to open file!");
        $txt = '';

        //获取每月1号之后的信息
        $time = strtotime(date('Y-m-01', time()));
//        var_dump($time);die;
        $list = PayTrade::find()
            ->where(['and', 'state = 1', 'create_at >=' . $time])
            ->asArray()
            ->all();
        // print_r($list);die;
        if (count($list) == 0) {
            echo "没有需要对账的订单\r\n";
            $txt .= "没有需要对账的订单\r\n";
        }
//        $out_trade_no = '150465707949985';
//        $wechat_data = $this->queryOrder($out_trade_no);
//        var_dump($wechat_data);die;

        foreach ($list as $v) {
            //查询微信支付的订单信息
            $wechat_data = $this->queryOrder($v['trade_no']);
//            print_r($wechat_data);die;
//            var_dump($wechat_data);die;
            if (isset($wechat_data['trade_state']) && $wechat_data['trade_state'] == 'SUCCESS') {
                //深澜4K保存订单信息
                $payMessge = [
                    'username' => $v['user_name'],
                    'money' => $v['total_fee'] / 100,
                    'orderId' => $wechat_data['transaction_id'],
                ];
                $res = Yii::$app->systemSrunApi->payment($payMessge);
                //$res = Srun::doSaving($v['user_name'], $v['total_fee']/100, $wechat_data['transaction_id']);
                //$res = json_decode($res, true);
                if (isset($res['code']) && $res['code'] == 0) {
                    // 更新数据记录
                    $r = Yii::$app->db->createCommand()->update('tab_pay_trade', [
                        'state' => 2,  // 支付已完成
                        'pay_at' => strtotime($wechat_data['time_end']),  // 支付完成时间
                        'transaction_id' => $wechat_data['transaction_id'],  // 微信订单号
                    ], ['trade_no' => $v['trade_no']])->execute();
                    if ($r) {
                        echo "用户：" . $v['user_name'] . " 本地订单号：" . $v['trade_no'] . " 对账" . ($v['total_fee'] / 100) . "成功\r\n";
                        $txt .= "用户：" . $v['user_name'] . " 本地订单号：" . $v['trade_no'] . " 对账" . ($v['total_fee'] / 100) . "成功\r\n";
                    } else {
                        echo $v['user_name'] . "修改本地支付状态失败\r\n";
                        $txt .= $v['user_name'] . "修改本地支付状态失败\r\n";
                    }
                } else {
                    echo "用户：" . $v['user_name'] . " 本地订单号：" . $v['trade_no'] . " 对账" . ($v['total_fee'] / 100) . "失败\r\n";
                    $txt .= "用户：" . $v['user_name'] . " 本地订单号：" . $v['trade_no'] . " 对账" . ($v['total_fee'] / 100) . "失败\r\n";
                }
            } else {
                echo $v['user_name'] . "无需对账\r\n";
            }
        }
        fwrite($file, $txt);
        fclose($file);
    }

    //根据本地生成的订单号查询微信订单信息
    public function queryOrder($out_trade_no)
    {
        $params = [
            'appid' => $this->pay_params['appid'], // 服务商的appid
            'mch_id' => $this->pay_params['mch_id'], // 服务商的商户号

            'sub_appid' => $this->pay_params['sub_appid'], // 湖北大学企业号的corpid
            'sub_mch_id' => $this->pay_params['sub_mch_id'], // 湖北大学微信支付商户号

            'out_trade_no' => $out_trade_no, //本地订单号
            'nonce_str' => Yii::$app->security->generateRandomString(), //随机字符串
        ];
        // 排序
        ksort($params);
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
        $res = Tool::postData($this->pay_url, $post);

        $data = (array)simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
//        var_dump($data);
        return $data;
    }

}