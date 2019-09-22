<?php
/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace system\modules\payment\models;


use system\models\Model;

class TianCaiController extends Model
{

    /**
     * 开始支付
     * @param $data
     * @return array|bool
     */
    public static function pay($data, $is_mobile)
    {
        if (!$data) {
            return false;
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
            'key' => $pay_config['key'],
        ];

        $config = array_filter($config);
        //$orderName = iconv('GBK','UTF-8',$orderName);
        $unified =[
            'orderDate'=>20140319152513,
            'orderNo'=>1403190004,
            'amount'=>10.00,
            'xmpch'=>036- 2014010005,
            'return_url'=>'http://www.test.com/returnPage.htm',
            'notify_url'=>'http://www.test.com/ notifyPage.htm',
        ];

        $unified['sing']=self::getSign($data,$config['key']);

        return self::buildRequestForm($unified);
    }

    /**
     * 电脑建立请求，以表单HTML形式构造（默认）
     * @param $para_temp |请求参数数组
     * @return string
     */
    public static function buildRequestForm($para_temp)
    {
        $sHtml="<form  id='form1' method='post' action='http:/gx.szhtkj.com.cn/zhifu/payAccept.aspx'>";
        while (list ($key, $val) = each($para_temp)) {
            if (false === self::checkEmpty($val)) {
                $val = str_replace("'", "&apos;", $val);
                $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
            }
        }
        $sHtml = $sHtml . "<input type='submit' value='ok' style='display:none;''></form>";
        $sHtml= $sHtml."<script type=’javascript’> form1.submit();</script>";
        return $sHtml;
    }

    /**
     *
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


}