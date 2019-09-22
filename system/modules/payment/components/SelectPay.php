<?php
namespace system\modules\payment\components;

/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2018/9/7
 * Time: 16:54
 */
use Yii;
use yii\base\Component;
use system\modules\payment\models\Alipay;
use system\modules\payment\models\WxPay;

class SelectPay extends Component
{
    /**
     * @param $data
     * @param string $pay_type 支付类型
     * @return array|bool|string
     */
    public function Select($data, $pay_type)
    {
        if (!$data && !$pay_type) {
            return json_encode(['code' => 1, 'messsge' => '参数缺失']);
        }

        $payClass = Yii::$app->systemConfig->getValue('PAYMENT_PAY_CLASS');

        $classType = array_keys($payClass);

        if (in_array($pay_type, $classType)) {
            $datas = $payClass[$pay_type]::doPay($data);
            return ['code' => 0, 'messsge' => $datas];
        }

        return ['code' => 1, 'messsge' => '缺少支付类'];
    }

    /**
     * $info 选择退款的类型
     * @param $data
     */
    public function selectRefund($data)
    {
        if (!$data) {
            return json_encode(['code' => 1, 'messsge' => '参数错误']);
        }

        $payClass = Yii::$app->systemConfig->getValue('PAYMENT_PAY_CLASS');

        $classType = array_keys($payClass);

        if (in_array($data['pay_type'], $classType)) {
            $datas = $payClass[$data['pay_type']]::doRefund($data['app_code'],$data['total_fee']/100,$data['total_fee']/100,$data['local_order'],$data['trade_no']);
            if($datas['code']==0){
                return ['code'=>0,'messsge'=>$datas['msg']];
            }
            return ['code' => 1, 'messsge' =>$datas['msg']];
        }

        return ['code' => 1, 'messsge' => '缺少支付类'];

    }


    /**
     * 支付宝微信订单查询
     * @param $data
     * @return mixed|string
     */
    public function selectOrderQuery($data)
    {
        if (!$data) {
            return json_encode(['code' => 1, 'messsge' => '参数错误']);
        }

        $payClass = Yii::$app->systemConfig->getValue('PAYMENT_PAY_CLASS');

        $classType = array_keys($payClass);

        if (in_array($data['pay_type'], $classType)) {
            $datas = $payClass[$data['pay_type']]::query($data['app_code'],$data['out_trade_no'],$data['trade_no']);
//            print_r(json_decode($datas,true));die;
            return $datas;
            //return ['code' => 0, 'messsge' => $datas];
        }

        return ['code' => 1, 'messsge' => '缺少查询的类'];

    }
}