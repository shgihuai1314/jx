<?php

namespace system\modules\payment\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_payment_detail".
 *
 * @property string $id
 * @property string $third_order_number
 * @property string $trade_no
 * @property string $transaction_id
 * @property integer $total_fee
 * @property integer $pay_type
 * @property integer $pay_status
 * @property integer $pay_time
 * @property integer $pay_user_id
 * @property string $app_code
 * @property string $data
 * @property string $crate_time
 */
class PaymentDetail extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_payment_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['total_fee','pay_status', 'pay_time', 'pay_user_id','crate_time'], 'integer'],
            [['third_order_number', 'trade_no', 'transaction_id', 'app_code', 'data','pay_type'], 'string'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '主键',
            'third_order_number' => '第三方订单号',
            'trade_no' => '平台订单号',
            'transaction_id' => '支付宝或者微信返回的订单号',
            'total_fee' => '支付金额',
            'pay_type' => '支付方式',
            'pay_status' => '支付状态',
            'crate_time'=>'创建时间',
            'pay_time' => '支付时间',
            'pay_user_id' => '发起支付的用户id',
            'app_code' => '支付标识',
            'data' => '支付完成的xml数据',
        ], parent::attributeLabels());
    }


    /**
     * 平台下单
     * @param $app_code string  应用的code
     * @param $data array 暂时只包含课程的id
     * @return bool|object
     */
    public static function newTrade($data)
    {
        if (!$data) {
            return false;
        }

        $tradeModel = new self();

        $tradeModel->third_order_number =$data['third_order_number']; //第三方订单号
        $tradeModel->trade_no=date('Y',time()).date('m',time()).date('d',time()).date('H',time()).date('i',time()).rand(10000, 99999);//平台订单号
        $tradeModel->total_fee = floor($data['fee']*100); //金额
        $tradeModel->pay_type=$data['pay_type'];//支付方式
        $tradeModel->crate_time = time();//下单时间
        $tradeModel->app_code=$data['app_code'];

        if (!$tradeModel->save()) {
            print_r($tradeModel->getErrors());
        }

        return $tradeModel;
    }

    /**
     * @param string $trade_no
     * @return bool|string|object||static
     */
    public static function getOneData($trade_no = '')
    {
        if (!$trade_no) {
            return false;
        }

        $data = self::findOne(['trade_no' => $trade_no]);

        return $data;

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
}
