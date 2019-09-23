<?php
/**
 * Created by PhpStorm.
 * User: 01
 * Date: 2019/9/23
 * Time: 9:53
 */

namespace Api;


use system\modules\payment\models\PaymentDetail;
use system\modules\payment\models\ThirdPartyLogin;
use system\modules\user\models\User;
use system\modules\user\models\UserExtend;

class AuthLogin extends BaseApiController
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
            if (!\Yii::$app->request->getIsGet()) {
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
            }

            //进行授权登录,并同步用户信息
            $code = \Yii::$app->request->get('code');
            if (ThirdPartyLogin::GetOpenid($code)) {
                return true;
            };
            return false;
        }
        return false;
    }

    /**
     * @return array
     */
    public function actionGetUserInfo()
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
                "openid" => $model['extend_openid'],
                "nickname" => $model['user']['realname'],
                "sex" => $model['user']['gender'],
                "province" => $model['extend_province'],
                "city" => $model['extend_city'],
                "country" => $model['extend_country'],
                "headimgurl" => $model['user']['avatar'],
                "privilege" => $model['extend_privilege'],
                "unionid" => $model['extend_unionid'],
            ];
            return $this->apiReturn(1, '获取用户信息成功', $data);
        }
        return $this->apiReturn(0, '获取用户信息失败');
    }

    public function actionPay()
    {
        $post=\Yii::$app->request->post();
        if (!isset($post['project_id'],$post['fee'])) {
            return $this->apiReturn(false, '参数错误');
        }

        $orderData=PaymentDetail::newTrade($post);

        if(!$orderData){
            return $this->apiReturn(false, '下单失败');
        }

        //发起支付
        PaymentDetail::wechat($orderData);

    }

}