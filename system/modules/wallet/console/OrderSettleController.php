<?php
namespace system\modules\wallet\console;

use Yii;
use system\modules\course\models\CourseOrder;
use system\modules\wallet\models\UserWallet;

/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2019/3/18
 * Time: 15:32
 */
class OrderSettleController extends \yii\console\Controller
{
    private $wallet = [0, -1, 1];//钱包类型:平台钱包，运营商，老师

    //根据条件结算订单，结算条件1.付款时间必须大于一个月，2.开课时间时间大于多久
    public function actionSettle()
    {
        //测试发送验证码
        //$data=Yii::$app->systemMessage->sendCode('phone','check_phone','15271630257');
//        print_r($data);die;

      /*  print_r(\Yii::$app->systemOrderLog->saveOrderLog(['order_id'=>2,'content'=>'订单已结算']));
        die;*/
        //查询订单数据
        $data = CourseOrder::find()
            ->select(['user_id', 'id', 'pay_time', 'plan_id', 'total_fee', 'local_order'])
            ->where(['order_status' => 2, 'close_status' => 0])
            ->andWhere(['NOT', ['pay_time' => 0]])
            ->with('plan')
            ->asArray()
            ->all();

        if(!$data){
            exit('no data');
        }

        //进行结算
        $res=CourseOrder::settle($data);
        if($res['code']==0){
            echo $res['message']."\n";
        }else{
            echo $res['message']."\n";
        }

    }
}