<?php

namespace system\modules\payment\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_payment_refund".
 *
 * @property integer $id
 * @property string $reason
 * @property string $app_code
 * @property integer $refund_money
 * @property integer $start_time
 * @property integer $end_time
 * @property string $refund_no
 * @property string $trade_no
 * @property string $out_trade_no
 * @property integer $buyer_user_id
 */
class PaymentRefund extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_payment_refund';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['refund_money', 'start_time', 'end_time', 'buyer_user_id'], 'integer'],
            [['reason', 'app_code', 'refund_no', 'trade_no', 'out_trade_no'], 'string'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '主键',
            'reason' => '退款原因',
            'app_code'=>'唯一标识',
            'refund_money' => '退款金额',
            'start_time' => '退款开始时间',
            'end_time' => '退款完成时间',
            'refund_no' => '第三方的订单号',
            'trade_no' => '退款成功交易订单号',
            'out_trade_no' => '退款本地订单号',
            'buyer_user_id' => '购买者id',
        ], parent::attributeLabels());
    }

    /**
     * 添加平台的退款数据
     * @param $data
     * @return bool|string
     */
    public static function addRefund($data)
    {
        $model=new self();
        $model->reason=$data['reason'];//退款原因
        $model->app_code=$data['app_code'];//退款描述
        $model->refund_no=$data['refund_no'];//第三方的订单号
        $model->refund_money=$data['refund_money'];//第三方的订单号
        // $model->order_number=CourseOrder::findOne(['id'=>$data['order_id']])->trade_no;//订单表的的订单号
        if($model->save()){
            return $model->refund_no;
        }

        return false;
    }

}
