<?php
/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2018/8/31
 * Time: 17:22
 */

namespace system\modules\payment\models;

use system\core\utils\Tool;
use system\modules\course\models\CourseOrder;
use system\modules\course\models\CourseRefund;
use Yii;
use yii\helpers\ArrayHelper;


class Alipay extends \system\models\Model
{
    const format = 'JSON';    //仅支持JSON	JSON

    const charset = 'utf-8';    //请求使用的编码格式

    const sign_type = 'RSA2'; //签名类型

    const version = '1.0'; //调用的接口版本，固定为：1.0

    public static $appCode;

    /**
     * 开始支付
     * @param $data
     * @param $is_mobile
     * @return bool|string
     */
    public static function doPay($data)
    {
        if (!$data) {
            return false;
        }

        self::$appCode = $data->app_code;

        if (self::isMobile()) {
            return self::WapPay($data);
        }

        return self::pcPay($data);
    }

    /**
     * 电脑网站支付
     * @param $data
     * @return string
     */
    public static function pcPay($data)
    {
        //请求参数
        $fee = $data->total_fee / 100;
        $requestConfigs = array(
            'out_trade_no' => $data->trade_no,//订单号
            'product_code' => 'FAST_INSTANT_TRADE_PAY',
            'total_amount' => $fee, //单位 元
            'subject' => paymentApp::getOneData($data->app_code)->name,  //TODO 订单标题
        );

        $config = paymentApp::getConfig(self::$appCode, 'alipay');
//        print_r($config);die;

        if (!$config) {
            exit('支付信息错误');
        }

        $commonConfigs = [
            //公共参数
            'app_id' => $config['appid'],
            'method' => 'alipay.trade.page.pay',             //接口名称
            'format' => self::format,
            'return_url' => Yii::$app->request->hostInfo . Yii::$app->request->scriptUrl . '/callback/synchro/alipay',
            'charset' => self::charset,
            'sign_type' => self::sign_type,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => self::version,
            'notify_url' => Yii::$app->request->hostInfo . Yii::$app->request->scriptUrl . '/callback/notify/alipay',
            'biz_content' => json_encode($requestConfigs),
        ];

        $commonConfigs["sign"] = self::generateSign($commonConfigs, $commonConfigs['sign_type']);

        return self::buildRequestForm($commonConfigs);
    }

    /**
     * 电脑建立请求，以表单HTML形式构造（默认）
     * @param $para_temp |请求参数数组
     * @return string
     */
    public static function buildRequestForm($para_temp)
    {
        $sHtml = "<form target='_blank' id='alipaysubmit' name='alipaysubmit' action='https://openapi.alipay.com/gateway.do?charset=" . self::charset . "' method='POST'>";
        while (list ($key, $val) = each($para_temp)) {
            if (false === self::checkEmpty($val)) {
                $val = str_replace("'", "&apos;", $val);
                $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
            }
        }
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='ok' style='display:none;''></form>";
        $sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";
        return $sHtml;
    }

    /**
     * 手机网站支付
     * @param $data
     * @return string
     */
    public static function WapPay($data)
    {
        //请求参数
        $requestConfigs = array(
            'out_trade_no' => $data->trade_no,
            'product_code' => 'QUICK_WAP_WAY',
            'total_amount' =>$data->total_fee / 100, //单位 元
            'subject' => paymentApp::getOneData($data->app_code)->name,  //TODO 订单标题
        );

        $config = paymentApp::getConfig(self::$appCode, 'alipay');

        if (!$config) {
            exit('支付信息错误');
        }

        $commonConfigs = [
            'app_id' => $config['appid'],
            'method' => 'alipay.trade.wap.pay',
            'format' => self::format,
            'return_url' => Yii::$app->request->hostInfo . Yii::$app->request->scriptUrl . '/callback/synchro/alipay',
            'charset' => self::charset,
            'sign_type' => self::sign_type,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => self::version,
            'notify_url' => Yii::$app->request->hostInfo . Yii::$app->request->scriptUrl . '/callback/notify/alipay',
            'biz_content' => json_encode($requestConfigs),
        ];
        //发起支付请求
        $commonConfigs["sign"] = self::generateSign($commonConfigs, $commonConfigs['sign_type']);

        return self::buildRequestForm($commonConfigs);
    }

    /**
     * 手机网站建立请求，以表单HTML形式构造（默认）
     * @param  请求参数数组
     * @return string
     */
    public static function buildMobileRequestForm($para_temp)
    {
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='https://openapi.alipay.com/gateway.do?charset=" . self::charset . "' method='POST'>";
        while (list ($key, $val) = each($para_temp)) {
            if (false === self::checkEmpty($val)) {
                $val = str_replace("'", "&apos;", $val);
                $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
            }
        }
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='ok' style='display:none;''></form>";
        $sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";

        return $sHtml;
    }

    /**
     * 获取签名
     * @param $params
     * @param string $signType
     * @return string
     */
    public static function generateSign($params, $signType = "RSA")
    {
        return self::sign(self::getSignContent($params), $signType);
    }

    /**
     * 应用私钥加密
     * @param $data
     * @param string $signType
     * @return string
     */
    public static function sign($data, $signType = "RSA")
    {
        //获取
        $config = paymentApp::getConfig(self::$appCode, 'alipay');

        $priKey = $config['private_key'];

        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($priKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, version_compare(PHP_VERSION, '5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256); //OPENSSL_ALGO_SHA256是php5.4.8以上版本才支持
        } else {
            openssl_sign($data, $sign, $res);
        }

        $sign = base64_encode($sign);

        return $sign;
    }

    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *   if is null , return true;
     **/
    public static function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
    }

    /**
     * 组装签名格式
     * @param $params
     * @return string
     */
    public static function getSignContent($params)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === self::checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = self::characet($v, self::charset);
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    public static function characet($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = self::charset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }
        return $data;
    }

    /**
     * @param $params
     * @return bool
     */
    public static function rsaCheck($params)
    {
        $sign = $params['sign'];
        $signType = $params['sign_type'];
        unset($params['sign_type']);
        unset($params['sign']);
        return self::verify($params['out_trade_no'], self::getSignContent($params), $sign, $signType);
    }

    /**
     * 支付宝公钥解密
     * @param $data
     * @param $sign
     * @param string $signType
     * @return bool
     */
    public static function verify($tradeNo, $data, $sign, $signType = 'RSA')
    {
        $config = paymentApp::getConfig(PaymentDetail::getOneData($tradeNo)->app_code, 'alipay');

        $pubKey = $config['public_key'];

        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        ($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');
        //调用openssl内置方法验签，返回bool值
        if ("RSA2" == $signType) {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res, version_compare(PHP_VERSION, '5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256);
        } else {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        }
//        if(!$this->checkEmpty($this->alipayPublicKey)) {
//            //释放资源
//            openssl_free_key($res);
//        }
        return $result;
    }

    /**
     * 区分手机端和pc端
     * @return bool
     */
    public static function isMobile()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $mobile_agents = Array("240x320", "acer", "acoon", "acs-", "abacho", "ahong", "airness", "alcatel", "amoi", "android", "anywhereyougo.com", "applewebkit/525", "applewebkit/532", "asus", "audio", "au-mic", "avantogo", "becker", "benq", "bilbo", "bird", "blackberry", "blazer", "bleu", "cdm-", "compal", "coolpad", "danger", "dbtel", "dopod", "elaine", "eric", "etouch", "fly ", "fly_", "fly-", "go.web", "goodaccess", "gradiente", "grundig", "haier", "hedy", "hitachi", "htc", "huawei", "hutchison", "inno", "ipad", "ipaq", "ipod", "jbrowser", "kddi", "kgt", "kwc", "lenovo", "lg ", "lg2", "lg3", "lg4", "lg5", "lg7", "lg8", "lg9", "lg-", "lge-", "lge9", "longcos", "maemo", "mercator", "meridian", "micromax", "midp", "mini", "mitsu", "mmm", "mmp", "mobi", "mot-", "moto", "nec-", "netfront", "newgen", "nexian", "nf-browser", "nintendo", "nitro", "nokia", "nook", "novarra", "obigo", "palm", "panasonic", "pantech", "philips", "phone", "pg-", "playstation", "pocket", "pt-", "qc-", "qtek", "rover", "sagem", "sama", "samu", "sanyo", "samsung", "sch-", "scooter", "sec-", "sendo", "sgh-", "sharp", "siemens", "sie-", "softbank", "sony", "spice", "sprint", "spv", "symbian", "tablet", "talkabout", "tcl-", "teleca", "telit", "tianyu", "tim-", "toshiba", "tsm", "up.browser", "utec", "utstar", "verykool", "virgin", "vk-", "voda", "voxtel", "vx", "wap", "wellco", "wig browser", "wii", "windows ce", "wireless", "xda", "xde", "zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }
        return $is_mobile;
    }

    /**
     * 数组转xml字符
     * @param  string $xml xml字符串
     **/
    public static function arrayToXml($data)
    {
        if (!is_array($data) || count($data) <= 0) {
            return false;
        }
        $xml = "<xml>";
        foreach ($data as $key => $val) {
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
     * @param $app_code  支付的唯一标识
     * @param $totalFee 总金额
     * @param $refundFee 退款金额
     * @param string $localOrderNo 本地订单号
     * @param string $alipayOrderNo //平台订单号
     * @return array|mixed
     */
    public static function doRefund($app_code, $totalFee,$refundFee, $localOrderNo = '', $alipayOrderNo = '')
    {
        self::$appCode = $app_code;

        //查询支付配置的信息
        $pay_config = paymentApp::getConfig($app_code, 'alipay');

        //请求参数
        $requestConfigs = array(
//            'trade_no'=>$orderNo,
//            'out_trade_no'=>$this->outTradeNo,
            'refund_amount' => $refundFee,
        );

        //支付订单号和本地订单号任选一个
        if(Yii::$app->systemConfig->getValue('COURSE_PAY_ENVIRONMENT')==1){
            $alipayOrderNo ?$requestConfigs['trade_no'] = $alipayOrderNo:$requestConfigs['out_trade_no'] = $localOrderNo;
        }else{
            $onePayData=PaymentDetail::find()->where(['third_order_number'=>$localOrderNo])->orWhere(['trade_no'=>$alipayOrderNo])->one();
            $requestConfigs['trade_no']=$onePayData['transaction_id'];
        }

        $commonConfigs = array(
            //公共参数
            'app_id' => $pay_config['appid'],
            'method' => 'alipay.trade.refund',             //接口名称
            'format' => self::format,
            'charset' => self::charset,
            'sign_type' => self::sign_type,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => self::version,
            'biz_content' => json_encode($requestConfigs),
        );

        $commonConfigs["sign"] = self::generateSign($commonConfigs, $commonConfigs['sign_type']);

        $result = Tool::postData('https://openapi.alipay.com/gateway.do', $commonConfigs);

        $resultArr = json_decode($result, true);

        if (empty($resultArr)) {
            $result = iconv('GBK', 'UTF-8//IGNORE', $result);
            return json_decode($result, true);
        }

        //查询订单,是否存在交易订单号，不存在则更新
        $result_code = $resultArr['alipay_trade_refund_response'];

        if ($result_code['code'] == 10000) {
            if(Yii::$app->systemConfig->getValue('COURSE_PAY_ENVIRONMENT')==1){
                $model = CourseRefund::findOne(['out_trade_no' => $result_code['trade_no']]);
            }else{
                $model = CourseRefund::findOne(['out_trade_no' => $result_code['out_trade_no']]);
            }

            if ($model->end_time && $model->out_trade_no && $model->buyer_user_id) {
                return ['code' => 1, 'msg' => '已退款'];
            } else {
                $model->end_time = strtotime($result_code['gmt_refund_pay']);
                $model->buyer_user_id = $result_code['buyer_user_id'];
               // $model->out_trade_no = $result_code['out_trade_no'];
                $model->trade_no = $result_code['trade_no'];
                $model->refund_money = $result_code['refund_fee'] * 100;
               if($model->save()){
                   return ['code' => 0, 'msg' => '退款成功'];
               }else{
                   return ['code' => 1, 'msg' => '退款失败'];
               }
                /*if ($model->save()) {
                    //退款成功,修改订单状态
                    $orderModel = CourseOrder::findOne(['trade_no' => $result_code['trade_no']]);
                    $orderModel->order_status = 5;//退款完成
                    $orderModel->save();
                    return ['code' => 0, 'msg' => '退款成功'];
                } else {
                    return ['code' => 1, 'msg' => '退款失败'];
                }*/
            }
        }

        return ['code' => 1, 'msg' => '退款失败'];
    }

    /**
     *  查询支付宝订单
     * @param $app_code
     * @param string $localOrderNo
     * @param string $alipayOrderNo
     * @return mixed
     */
    public static function query($app_code, $localOrderNo = '', $alipayOrderNo = '')
    {
        self::$appCode = $app_code;
        //查询支付配置的信息
        $pay_config = paymentApp::getConfig($app_code, 'alipay');
        //请求参数
        $requestConfigs = [
            'out_trade_no' => $localOrderNo,
            'trade_no' => $alipayOrderNo,
        ];

        $commonConfigs = [
            //公共参数
            'app_id' => $pay_config['appid'],
            'method' => 'alipay.trade.query',             //接口名称
            'format' => self::format,
            'charset' => self::charset,
            'sign_type' => self::sign_type,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => self::version,
            'biz_content' => json_encode($requestConfigs),
        ];

        $commonConfigs["sign"] = self::generateSign($commonConfigs, $commonConfigs['sign_type']);

        $result = json_decode(Tool::postData('https://openapi.alipay.com/gateway.do', $commonConfigs), true);

        if (isset($result['alipay_trade_query_response']['code']) && $result['alipay_trade_query_response']['code'] == 10000) {
            $data = [
                'pay_time' => strtotime($result['alipay_trade_query_response']['send_pay_date']),
                'trade_no' => $result['alipay_trade_query_response']['trade_no'],
                'type' => 'alipay'
            ];
            return ['code' => 0, 'message' => '查询成功', 'data' => $data];
        } else {
            return ['code' => 1, 'message' => '查询失败'];
        }

    }

    /**
     * 处理支付宝异步回调
     * @param $data
     */
    public static function callback($datas)
    {
        //验证签名
        $result = Alipay::rsaCheck($datas);

        if ($result === true) {
            //查询订单
            $pay_msg = PaymentDetail::findOne(['transaction_id' => $datas['trade_no']]);

            if (!$pay_msg) {
                $data = [
                    'code' => 0,
                    'pay_status' => 1,
                    'pay_time' => strtotime($datas['gmt_payment']),
                    'data' => Alipay::arrayToXml($datas),
                    'pay_user_id' => $datas['buyer_id'],
                    'transaction_id' => $datas['trade_no'],
                    'out_trade_no' => $datas['out_trade_no'],
                    'msg' => 'succeed'
                ];

                return $data;
            }

            return ['code' => 1, 'msg' => '订单已支付'];

        }

        return ['code' => 1, 'msg' => '签名错误'];

    }

    /**
     * 处理支付宝同步回调
     * @param $data
     */
    public static function sync($datas)
    {

        unset($datas['type']);
        //验证签名
        $result = Alipay::rsaCheck($datas);
        Yii::trace('msg', json_encode($result));

        if ($result === true) {
            $data = [
                'code' => 0,
                'out_trade_no' => $datas['out_trade_no'],
                'msg' => 'succeed'
            ];

            $order = PaymentDetail::getOneData($datas['out_trade_no']);

            $paymentApp = paymentApp::getOneData($order->app_code);

            $data['url'] = $paymentApp->notify_url;

            return $data;
        }

        return ['code' => 1, 'msg' => '签名错误'];

    }
}