<?php

namespace system\modules\payment\models;

use system\core\utils\Tool;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_pay_app".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $pay_type
 * @property string $pay_messge
 * @property integer $status
 * @property string $describle
 * @property integer $app_type
 * @property string $notify_class
 * @property string $notify_url
 * @property string $secret
 * @property string $alipay_config
 * @property string $wechat_config
 * @property string $pay_nums
 * @property string $app_rand
 */
class paymentApp extends \system\models\Model
{
    //是否记录日志
    public $log_flag = true;
    //日志信息配置
    public $log_options = [
        'model_name' => 'tab_payment_app',//模型名称
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_payment_app';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['name', 'code'], 'required'],
            [['status'], 'integer'],
            [['name', 'code', 'describle', 'notify_class', 'notify_url', 'pay_nums', 'app_rand', 'alipay_config','secret'], 'string'],
            [['pay_type','wechat_config'], 'safe']
        ]);
    }

    /**
     * 获取属性
     * @param string $field
     * @param string $key
     * @param bool $default
     * @return array|bool|string
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
        $list = [
            'status' => ['1' => '开启', '0' => '禁用'],
            'pay_type' => Yii::$app->systemConfig->getValue('PAYMENT_PAY_TYPE'),
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '主键',
            'name' => '业务名称',
            'code' => '业务编码',
            'pay_type' => '支付方式',
            'alipay_config' => '支付宝支付配置',
            'wechat_config' => '微信支付配置',
            'status' => '是否开启',
            'describle' => '描述',
            'notify_class' => '回调类',
            'notify_url' => '跳转地址',
            'pay_nums' => '支付金额设置',
            'secret'=>'密钥',
            'app_rand' => '应用范围',
        ], parent::attributeLabels());
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
//            print_r($this->id);die;
            if ($insert) {
                $this->pay_type = implode(',', $this->pay_type);
                $this->pay_nums = str_replace('\r\n', "\r\n", $this->pay_nums);
                $this->alipay_config = str_replace('\r\n', "\r\n", $this->alipay_config);
                $this->wechat_config = str_replace('\r\n', "\r\n", $this->wechat_config);
            } else {

                if (is_array($this->pay_type)) {
                    $this->pay_type = implode(',', $this->pay_type);
                }

            }
            // 将\r\n转义
            $this->alipay_config = str_replace('\r\n', "\r\n", $this->alipay_config);
            $this->wechat_config = str_replace('\r\n', "\r\n", $this->wechat_config);
//            echo 11111;die;
            return true;
        }

        return false;
    }

    /**
     * 获取支付的配置
     * @param $app_code
     * @param $type
     * @return array|string
     */
    public static function getConfig($app_code, $type)
    {
        $data = self::findOne(['code' => $app_code]);

        if (!$data) {
            return false;
        }

        if ($type == 'alipay') {
            if (!$data->alipay_config) {
                return Yii::$app->systemConfig->getValue('SYSTEM_ALIPAY_CONFIG');
            }
            return Tool::paramsToArray($data->alipay_config);

        } elseif ($type == 'wechat') {
            if (!$data->wechat_config) {
                return Yii::$app->systemConfig->getValue('SYSTEM_WEPAY_CONFIG');
            }
            return Tool::paramsToArray($data->wechat_config);
        } else {
            //获取数据表的是所有字段
            $r = ArrayHelper::toArray(Yii::$app->db->getSchema()->getTableSchema('tab_pay_app'));//获取表字段名
            //判断是否存在这个字段
            if (in_array('extend_' . $type, array_keys($r['columns']))) {
                $filed = 'extend_' . $type;
                //是否存在配置
                if (!$data->$filed) {
                    return Yii::$app->systemConfig->getValue('SYSTEM_' . strtoupper($type) . '_CONFIG');
                }
                return Tool::paramsToArray($data->$filed);
            }
        }

        return false;
    }

    /**
     * @param string $app_code
     * @return bool|string|null|static
     */
    public static function getOneData($app_code = '')
    {
        if (!$app_code) {
            return false;
        }
        return self::findOne(['code' => $app_code]);
    }
}
