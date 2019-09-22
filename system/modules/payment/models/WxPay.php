<?php
/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2018/9/5
 * Time: 18:36
 */

namespace system\modules\payment\models;

use system\core\utils\Tool;
use system\models\Model;

use system\modules\user\models\UserExtend;
use Yii;

class WxPay extends Model
{
    /**
     * 开始支付
     * @param $data
     * @return array|bool
     */
    public static function doPay($data)
    {
        if (!$data) {
            return false;
        }
        if (Alipay::isMobile()) {
            return self::wechat($data);
        }
        //扫码支付
        return self::createJsBizPackage($data);

    }

    /**
     * 发起微信扫码订单
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $notifyUrl 支付结果通知url
     * @param string $timestamp 订单发起时间
     * @return array
     */
    public static function createJsBizPackage($data)
    {
        //获取微信支付的配置
        //$pay_config = Yii::$app->systemConfig->getValue('SYSTEM_WEPAY_CONFIG');
        $pay_config = paymentApp::getConfig($data->app_code, 'wechat');

        if (!$pay_config) {
            exit('支付信息错误');
        }

        $config = [
            'appid' => $pay_config['appid'],
            'mch_id' => $pay_config['mchid'],
            'sub_appid' => $pay_config['sub_appid'],
            'sub_mch_id' => $pay_config['sub_mch_id'],
            'key' => $pay_config['key'],
        ];

        $config = array_filter($config);
        //$orderName = iconv('GBK','UTF-8',$orderName);
        $unified = array(
            'appid' => $config['appid'],
            'attach' => $data->app_code,//商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body' => paymentApp::getOneData($data->app_code)->name,
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'notify_url' => Yii::$app->request->hostInfo . Yii::$app->request->scriptUrl . '/callback/notify/wechat',
            'out_trade_no' => $data->trade_no,
            'spbill_create_ip' => Yii::$app->request->userIP,
            'total_fee' => intval($data->total_fee),       //单位 转为分
            'trade_type' => 'NATIVE',
        );

        $unified['sign'] = self::getSign($unified, $config['key']);

        $responseXml = Tool::postData('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));

        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($unifiedOrder === false) {
            die('parse xml error');
        }

        if ($unifiedOrder->return_code != 'SUCCESS') {
            die($unifiedOrder->return_msg);
        }

        if ($unifiedOrder->result_code != 'SUCCESS') {
            die($unifiedOrder->err_code);
        }

        $codeUrl = (array)($unifiedOrder->code_url);
        if (!$codeUrl[0]) {
            exit('get code_url error');
        }

        $arr = array(
            "appId" => $config['appid'],
            "timeStamp" => (string)time(),
            "nonceStr" => self::createNonceStr(),
            "package" => "prepay_id=" . $unifiedOrder->prepay_id,
            "signType" => 'MD5',
            "code_url" => $codeUrl[0],
        );

        $arr['paySign'] = self::getSign($arr, $config['key']);
        $arr['TradeNo'] = $data->trade_no;

        return $arr;
    }

    /**
     *  获取随机字符串
     * @param int $length
     * @return string
     */
    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * @param $arr
     * @return string
     * 数组转换xml
     */
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }

        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 获取签名
     * @param $params
     * @param $key
     * @return string
     */
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }

    /**
     * 拼接签名字符串
     * @param $paraMap
     * @param bool $urlEncode
     * @return string
     */
    public static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }

                $buff .= $k . "=" . $v . "&";
            }
        }

        $reqPar = '';

        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }

        return $reqPar;
    }

    /**
     * 微信公众号支付以及企业微信支付
     * @param $data
     * @return array
     */
    public static function wechat($data)
    {
        //获取微信支付的配置
        //$pay_config = Yii::$app->systemConfig->getValue('SYSTEM_WEPAY_CONFIG');
        $pay_config = paymentApp::getConfig($data->app_code, 'wechat');

        if (!$pay_config) {
            exit('支付信息错误');
        }

        if (APP_NAME == 'wechat') {
            //获取openid
            $model = UserExtend::findOne(['user_id' => Yii::$app->user->id]);
            $openid = $model->extend_openid;
        } else if (APP_NAME == 'qywx') {
            $openInfo = Yii::$app->systemQyweixin->convertToOpenid(Yii::$app->user->identity->username);
            $openid = $openInfo['openid'];
        }

        //Yii::trace('info', 'openid to user:' . Json::encode($config));
        //$orderName = iconv('GBK','UTF-8',$orderName);
        $unified = [
            'appid' => $pay_config['appid'],
            'mch_id' => $pay_config['mch_id'],
            'sub_appid' => $pay_config['sub_appid'],
            'sub_mch_id' => $pay_config['sub_mch_id'],
            'attach' => $data->app_code,//自定义参数
            'body' => $data->order_name, //商品名称
            'nonce_str' => self::createNonceStr(),
            'notify_url' => Yii::$app->request->hostInfo . Yii::$app->request->scriptUrl . '/callback/notify/wechat',
            'out_trade_no' => $data->trade_no, //本地订单号
            'spbill_create_ip' => '127.0.0.1',
            'total_fee' => intval($data->total_fee), // 金额  单位 转为分
            'trade_type' => 'JSAPI',
        ];

        if (APP_NAME == 'wechat') {
            //获取openid
            if ($unified['sub_appid'] == null) {
                $unified['openid'] = $openid;
            } else {
                $unified['sub_openid'] = $openid;
            }
        } else if (APP_NAME == 'qywx') {
            if ($unified['sub_appid'] == null) {
                $unified['openid'] = $openid;
            } else {
                $unified['sub_openid'] = $openid;
            }
        }

        $unified['sign'] = self::getSign(array_filter($unified), $pay_config['key']);
        $unified = array_filter($unified);//去除空的数据

        $responseXml = Tool::postData('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($unifiedOrder === false) {
            die('parse xml error');
        }

        if ($unifiedOrder->return_code != 'SUCCESS') {
            die($unifiedOrder->return_msg);
        }

        if ($unifiedOrder->result_code != 'SUCCESS') {
            die($unifiedOrder->err_code);
        }

        $res = array(
            "appId" => $pay_config['appid'],
            "timeStamp" => (string)time(), //这里是字符串的时间戳，不是int，
            "nonceStr" => self::createNonceStr(),
            "package" => "prepay_id=" . $unifiedOrder->prepay_id,
            "signType" => 'MD5',
        );
        $res['paySign'] = self::getSign($res, $pay_config['key']);
        $res['fee'] = $data->total_fee / 100;
        return $res;
    }

    /**
     * 微信退款
     * @param float $totalFee 支付配置的唯一标识
     * @param float $totalFee 订单金额 单位元
     * @param float $refundFee 退款金额 单位元 todo 按照一定的比例退费
     * @param string $refundNo 退款单号
     * @param string $wxOrderNo 本地订单号
     * @param string $orderNo 平台订单号
     * @return string
     */
    public static function doRefund($app_code, $totalFee, $refundFee, $orderNo = '', $wxOrderNo = '')
    {
        if (!$wxOrderNo && !$orderNo) {
            return ['code' => 1, 'msg' => '微信订单号和商户订单号必须存在一个'];
        }

        //微信支付的配置信息
        $pay_config = paymentApp::getConfig($app_code, 'wechat');

        $config = array(
            'mch_id' => $pay_config['mchid'],
            'appid' => $pay_config['appid'],
            'key' => $pay_config['key'],
        );

        //过滤掉空的字符串
        $config = array_filter($config);
        //支付参数
        $unified = array(
            'appid' => $config['appid'],//appid
            'mch_id' => $config['mch_id'],//商户号
            'nonce_str' => self::createNonceStr(),//随机字符串
            'total_fee' => intval($totalFee * 100),//订单金额	 单位 转为分
            'refund_fee' => intval($refundFee * 100),//退款金额 单位 转为分
            'sign_type' => 'MD5',//签名类型 支持HMAC-SHA256和MD5，默认为MD5
            //'notify_url' => Yii::$app->request->hostInfo . Yii::$app->request->scriptUrl . '/callback/refund',
            'out_refund_no' => 'refund_' . uniqid(),//商户退款单号
            'refund_desc' => '商品已售完',//todo 退款原因（选填）
        );
        //商户订单号和微信订单号任选一个都可以
        if(Yii::$app->systemConfig->getValue('COURSE_PAY_ENVIRONMENT')==1){
            $wxOrderNo ? $unified['transaction_id'] = $wxOrderNo : $unified['out_trade_no'] = $orderNo;
        }else{
            $onePayData=PaymentDetail::find()->where(['third_order_number'=>$orderNo])->orWhere(['trade_no'=>$wxOrderNo])->one();
            $unified['trade_no']=$onePayData['transaction_id'];
        }

        //添加签名
        $unified['sign'] = self::getSign($unified, $config['key']);

//        print_r($unified);die;
        //发送退款请求,这里必须有安装证书，否则请求无效
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/secapi/pay/refund', self::arrayToXml($unified));

        //xml格式的字符串转换成对象
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
//        print_r((array)$unifiedOrder);
        if ($unifiedOrder === false) {
            //die('parse xml error');
            return ['code'=>1,'msg'=>'退款失败'];
        }

        if ($unifiedOrder->return_code != 'SUCCESS') {
            return ['code'=>1,'msg'=>$unifiedOrder->return_msg];
        }

        if ($unifiedOrder->result_code != 'SUCCESS') {
           // return $unifiedOrder->err_code_des;
            return ['code'=>1,'msg'=>$unifiedOrder->err_code_des];
        }

        return ['code'=>0,'msg'=>'退款成功'];
    }

    /**
     * 查询微信订单支付状态
     * @param $app_code 配置标识
     * @param string $orderNo
     * @param string $wxOrderNo
     * @return array
     */
    public static function query($app_code,$orderNo = '', $wxOrderNo = ''){
        if (!$wxOrderNo && !$orderNo) {
            return ['code' => 1, 'msg' => '微信订单号和商户订单号必须存在一个'];
        }

        //微信支付的配置信息
        $pay_config = paymentApp::getConfig($app_code, 'wechat');


        //微信支付配置
        $config = [
            'appid' => $pay_config['appid'],
            'mch_id' => $pay_config['mchid'],
            'key' => $pay_config['key'],
        ];

        $unified=[
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
        ];

        $wxOrderNo ? $unified['transaction_id'] = $wxOrderNo : $unified['out_trade_no'] = $orderNo;

        $unified['sign'] = self::getSign($unified, $pay_config['key']);


        $responseXml = Tool::postData('https://api.mch.weixin.qq.com/pay/orderquery', self::arrayToXml($unified));
        $queryResult = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($queryResult === false) {
            die('parse xml error');
        }
        if ($queryResult->return_code != 'SUCCESS') {
            die($queryResult->return_msg);
        }

        $result=(array)$queryResult;

        if($result['trade_state']=='SUCCESS'){
                $data = [
                    'pay_time' => strtotime($result['time_end']),
                    'trade_no' => $result['transaction_id'],
                    'type' => 'wechat'
                ];
            return ['code' => 0, 'message' => '查询成功', 'data' => $data];
        }else{
            return ['code' => 1, 'message' => '查询失败'];
        }
    }

    /**
     * 处理微信的异步回调
     * @param $postStr
     * @return array
     */
    public static  function callback($postStr){
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

        //获取openid
        isset($arr['sub_openid']) ? $openid = $arr['sub_openid'] : $openid = $arr['openid'];

        //获取支付配置
        $config = paymentApp::getConfig($arr['attach'], 'wechat');

        //删除签名
        unset($arr['sign']);

        //验证签名
        if (WxPay::getSign($arr, $config['key']) != $postObj->sign) {
            return ['code'=>1,'msg'=>'签名错误'];
        }

        //查询订单
        $pay_msg = PaymentDetail::findOne(['transaction_id' => $arr['transaction_id']]);

        if (!$pay_msg) {
            //isset($arr['sub_openid'])?$openid=$arr['sub_openid']:$openid=$arr['openid'];
            $updateData = [
                'code'=>0,
                'pay_status' => 1,
                'pay_time' => strtotime($arr['time_end']),
                'data' => WxPay::arrayToXml($arr),
                'pay_user_id' => $openid,
                'transaction_id' => $arr['transaction_id'],
                'out_trade_no'=>$arr['out_trade_no'],
                'msg'=>'<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>'
            ];
            return $updateData;
        }

        return ['code'=>1,'msg'=>'订单已支付'];
    }
    /**
     * @param string $url
     * @param string $postData
     * @param array $options
     * @return mixed
     */
    public static function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //第一种方法，cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, getcwd() . '/cert/apiclient_cert.pem');
        //默认格式为PEM，可以注释
//        print_r(getcwd());die;获取当前工作目录
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, getcwd() . '/cert/apiclient_key.pem');
        //第二种方式，两个文件合成一个.pem文件
//        curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 错误的标识
     * @param $str
     * @return string
     */
    public static function getTradeSTate($str)
    {
        switch ($str){
            case 'SUCCESS';
                return '支付成功';
            case 'REFUND';
                return '转入退款';
            case 'NOTPAY';
                return '未支付';
            case 'CLOSED';
                return '已关闭';
            case 'REVOKED';
                return '已撤销（刷卡支付）';
            case 'USERPAYING';
                return '用户支付中';
            case 'PAYERROR';
                return '支付失败';
        }
    }
}