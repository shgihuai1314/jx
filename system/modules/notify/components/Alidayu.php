<?php
/*短信发送组件*/
namespace system\modules\notify\components;

use Yii;

/**
 * Class Alidayu 阿里大于的短信发送类
 * @package system\modules\notify\components
 */
class Alidayu
{
    // api接口
    private $_gatewayUrl = "http://gw.api.taobao.com/router/rest";
    // 请求的方法
    private $_method = "alibaba.aliqin.fc.sms.num.send";
    // 短信签名类型
    private $_signMethod = "md5";
    // api版本
    private $_apiVersion = "2.0";
    // 返回的数据格式
    private $_format = 'json';
    // 发送的类型
    private $_normal = 'normal';

    // 以下是需要配置的项目
    public $appKey = '';        // app_key
    public $secretKey = '';     // secret_key
    public $signName = '';      // 短信签名

    public function send($data)
    {
        if (!isset($data['template'], $data['params'], $data['send_to'])) {
            return [
                'code' => 1,
                'message' => '发送短信的参数不全'
            ];
        }
        // 注意：阿里大于要求接口里面参数的值必须是字符串，不能是数字，所以json_encode转换之前要全部转换成字符串
        $newParams = [];
        foreach ($data['params'] as $key => $item) {
            if (is_int($item)) {
                $item = strval($item);
            }
            $newParams[$key] = $item;
        }

        // 处理所有的参数
        $params = [
            // 公共参数
            'method' => $this->_method,
            'app_key' => $this->appKey,
            'sign_method' => $this->_signMethod,
            'timestamp' => date("Y-m-d H:i:s"),
            'format' => $this->_format,
            'v' => $this->_apiVersion,

            // 业务参数
            'sms_type' => $this->_normal,
            'sms_free_sign_name' => $this->signName,        // 签名
            'sms_template_code' => $data['template'],       // 模板id
            'sms_param' => json_encode($newParams),         // 模板参数
            'rec_num' => $data['send_to'],                  // 手机号
        ];

        // 排序
        ksort($params);

        // 生成签名
        $signString = $this->secretKey;
        foreach ($params as $key => $value) {
            $signString .= $key . $value;
        }
        $signString .= $this->secretKey;

        $params['sign'] = strtoupper(md5($signString));

        $url = $this->_gatewayUrl . '?' . http_build_query($params);

        // 发起请求，get或者post都可以
        $result = file_get_contents($url);

        if (!$result) {
            return [
                'code' => 1,
                'message' => '短信接口异常，无法发送消息，可能是网络问题'
            ];
        }

        $arr = json_decode($result, true);
        if (isset($arr['alibaba_aliqin_fc_sms_num_send_response'])) {
            return [
                'code' => 0,
                'message' => '发送成功',
            ];
        } else if (isset($arr['error_response'])) {
            return [
                'code' => 1,
                'data' => $arr,
                'message' => $arr['error_response'],
            ];
        }
    }

}