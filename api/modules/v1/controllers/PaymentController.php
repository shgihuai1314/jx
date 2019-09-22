<?php
/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2018/8/31
 * Time: 14:19
 */

namespace Api;

use system\modules\course\models\CourseOrder;
use system\modules\course\models\CoursePlan;
use system\modules\course\models\CourseStudent;
use system\modules\payment\models\paymentApp;
use system\modules\payment\models\PaymentDetail;
use system\modules\payment\models\PaymentRefund;
use system\modules\payment\models\PayTrade;
use system\modules\payment\models\PayTradeDetail;
use system\modules\payment\models\Refund;
use Yii;
use yii\helpers\ArrayHelper;

class PaymentController extends BaseApiController
{
    public $notAuthAction = ['*'];
    /**
     * @info 开始支付，调用支付接口
     * @method POST
     * @param string $third_order_number 订单号 required
     * @param string $app_code 支付唯一标识 required
     * @param string $fee 金额 required
     * @param integer $pay_type 支付类型 required
     * @return array ['code' => 0, 'message' => '提交订单成功',data=>'']
     */
    public function actionPay()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['third_order_number'],$post['app_code'],$post['fee'],$post['pay_type'])) {
            return $this->apiReturn(false, '参数错误');
        }

        if(!$post['pay_type']){
            return $this->apiReturn(false, '请选择支付方式');
        }

        //是否存在此支付方式
        $payConfig = paymentApp::getOneData($post['app_code']);

        //是否开启支付了
       /* if (!$payConfig->status) {
            return $this->apiReturn(false, '暂时无法支付');
        }*/

        if (!in_array($post['pay_type'], explode(',', $payConfig->pay_type))) {
            return $this->apiReturn(false, '支付方式不存在');
        }

        //查询平台订单
        $tradeModel = PaymentDetail::findOne(['third_order_number'=>$post['third_order_number']]);

        //平台订单不存在的时候，直接下单发起支付
        if (!$tradeModel) {
            //下单
            $orderData=PaymentDetail::newTrade($post);

            if(!$orderData){
                return $this->apiReturn(false, '下单失败');
            }

            //调用支付接口
            $data = Yii::$app->systemPayment->select($orderData, $post['pay_type']);

            return $this->apiReturn(true, '操作成功', $data['messsge']);
        }

        //平台订单存在的时候，先判断支付状态是否正确，在发起支付
        if($tradeModel->pay_status!==0){
            return $this->apiReturn(false, '已完成支付,无法在次支付');
        }

        //如果支付方式跟以前的支付方式不一样，就更新支付方式
        if($post['pay_type']!=$tradeModel['pay_type']){
            $tradeModel->pay_type=$post['pay_type'];
            if(!$tradeModel->save()){
                return $this->apiReturn(false, '支付失败');
            }
        }

//        print_r($tradeModel);die;
        //调用支付接口
        $data = Yii::$app->systemPayment->select($tradeModel, $tradeModel['pay_type']);

        if($data['code']==0){
            return $this->apiReturn(true, '操作成功', $data['messsge']);
        }

        return $this->apiReturn(false, $data['messsge']);
    }

    /**
     * @info 平台交易订单
     * @method GET
     * @param string $search 关键字查询
     * @param string $state 支付状态
     * @param inter my $state 是否获取自己的账单
     * @param string $pay_at 支付时间
     * @param string $pay_type 支付方式
     * @return array
     * [
     *      'code'=>'0'，
     *      'messge'=>'操作成功',
     *      'data'=>[
     *             "id"=>'主键',
     *             "user_id"=>用户id;
     *             "user_name"=>"用户名",
     *             "trade_no"=>"本地订单号",
     *             "total_fee"=>支付费用,
     *             "order_name"=>"订单标题",
     *             "pay_user_id"=>"支付者用户id",
     *             "create_at"=>"下单时间",
     *             "app_code"=>"支付标识",
     *             "pay_type"=>"支付类型",
     *             "transaction_id"=>"交易订单号",
     *             "state"=>"支付状态",
     *             "data"=>"支付数据",
     *             "coupon_id"=>"优惠卷id",
     *             "discount_amount"=>"优惠金额",
     *             "course_ids"=>"课程id串"
     *          ],
     * ];
     */
    public function actionGetAll()
    {
        //组装条件
        $condition = [
            'state',
            'pay_type',
            'search' => [
                'or',
                ['like', 'order_name', ':val'],
                ['like', 'transaction_id', ':val'],
                ['like', 'user_name', ':val'],
                ['like', 'trade_no', ':val'],
            ],
            'pay_at' => function ($val) {
                list($start, $end) = explode('~', $val);
                return ['between', 'pay_at', strtotime($start . ' 00:00:00'), strtotime($end . ' 23:59:59')];
            },
        ];

        $query = PayTrade::find();

        if (Yii::$app->request->get('my')) {
            $query = $query->where(['user_id' => Yii::$app->user->id]);
        }

        //查询所有的订单
        $data = $query
            ->search($condition)
            ->with('plan')
            ->orderBy(['id' => SORT_DESC])
            ->asArray()
            ->paginate()
            ->all();

        $pagination = ArrayHelper::remove($data, 'pagination');

        foreach ($data as $k => $v) {
            $data[$k]['file'] = \Yii::$app->systemFileInfo->get($v['plan']['thumb_img'], 'src');
        }

//        print_r($pagination);die;
//        print_r($data->createCommand()->getRawSql());die;
        return $this->apiReturn(true, '操作成功', ['order' => $data, 'pagination' => $pagination]);
    }

    /**
     * @info 查询平台订单
     */
    public function actionQuery(){
        $post=Yii::$app->request->post();
        if(!isset($post['local_order'])){
            return $this->apiReturn(false, '参数错误');
        }

        $payMentOrder=PaymentDetail::find()->where(['third_order_number'=>$post['local_order']])->asArray()->one();

        if(!$payMentOrder){
            return $this->apiReturn(false, '订单不存在');
        }

        //调用支付接口
        $data = Yii::$app->systemPayment->selectOrderQuery($payMentOrder['app_code'], $payMentOrder['trade_no']);

        if($data['code']==0){
            return $this->apiReturn(true, '操作成功', $data);
        }

        return $this->apiReturn(false,"查询失败");

//        print_r($post);die;
    }

    /**
     * @info 第三方数据发送到这里订单退款，添加退款订单的数据
     */
    public function actionRefund(){
        //退款原因，平台订单的支付成功的本地订单号，金额，app_code
        $post=Yii::$app->request->post();

        if(!isset($post['reason'],$post['return_no'],$post['refund_money'],$post['app_code'])){
            return $this->apiReturn(false, '参数缺失');
        }

        $model=PaymentRefund::addRefund($post);

        //调用退款接口
        if($model){
            $data = Yii::$app->systemPayment->selectRefund($post);
            return $data;
        }
        return $this->apiReturn(false, '退款失败');
    }

}