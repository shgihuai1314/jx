<?php
/**
 * Created by PhpStorm.
 * User: 01
 * Date: 2019/9/23
 * Time: 9:53
 */

namespace Api;
use Yii;
use system\modules\payment\models\PaymentDetail;
use system\modules\payment\models\ThirdPartyLogin;
use system\modules\user\models\User;
use system\modules\user\models\UserExtend;

class AuthLoginController extends BaseApiController
{
    public $notAuthAction = ['*'];

    /**
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {

            if (!Yii::$app->user->isGuest) {
                $openid = \Yii::$app->session->remove('wechat_openid');
                if (!empty($openid)) {
                    $model = UserExtend::findOne(\Yii::$app->user->id);
                    if (empty($model)) {
                        $model = new UserExtend();
                        $model->user_id = \Yii::$app->user->id;
                    }
                    $model->extend_openid = $openid;
                    $model->save();
                }
                return true;
            }else{
                //进行授权登录,并同步用户信息
                $code = \Yii::$app->request->get('code','');
                if (ThirdPartyLogin::GetOpenid($code)) {
                    return true;
                };

                return $this->apiReturn(false, '获取用户信息失败');
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function actionInfo()
    {
        //todo  跳回的页面的url 需要加encodeURIComponent（）方法来处理 否知不好使
        /* getCode() {
         let url = window.location.search;
         let start = window.location.search.indexOf("=");
         let end = window.location.search.indexOf("&");
         let code = url.substring(start + 1, end);
         return code;
     }*/

        $user_id = \Yii::$app->user->id;
        $model = User::find()->where(['user_id' => $user_id])->with('extend')->asArray()->one();
        if ($model) {
            $data = [
                'user_id' => $model['user_id'],
                "openid" => $model['extend']['extend_openid'],
                "nickname" => $model['realname'],
                "sex" => $model['gender'],
                "province" => $model['extend']['extend_province'],
                "city" => $model['extend']['extend_city'],
                "country" => $model['extend']['extend_country'],
                "headimgurl" => $model['avatar'],
                "privilege" => $model['extend']['extend_privilege'],
                "unionid" => $model['extend']['unionid'],
            ];
            return $this->apiReturn(true, '获取用户信息成功', $data);
        }
        return $this->apiReturn(false, '获取用户信息失败');
    }

    /**
     * 发起支付
     * @return array
     */
    public function actionPay()
    {
        $post = \Yii::$app->request->post();
        if (!isset($post['project_id'], $post['fee'])) {
            return $this->apiReturn(false, '参数错误');
        }

        $orderData = PaymentDetail::newTrade($post);

        if (!$orderData) {
            return $this->apiReturn(false, '下单失败');
        }

        //发起支付
        $data = PaymentDetail::wechat($orderData);

        if ($data) {
            return $this->apiReturn(true, '支付成功');
        }

        return $this->apiReturn(false, '支付失败');
    }

}