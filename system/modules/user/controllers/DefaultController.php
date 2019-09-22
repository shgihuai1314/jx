<?php
namespace system\modules\user\controllers;

use Yii;
use system\modules\user\models\UserLoginError;
use system\modules\user\models\LoginForm;
use yii\captcha\CaptchaValidator;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * 默认控制器,登录等操作，不受权限系统控制
 */
class DefaultController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    /**
     * 处理验证码
     * @return array
     */
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fontFile' => '@webroot/static/fonts/adele-light-webfont.ttf',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'width' => 109,
                'height' => 38,
                'transparent' => false,  // 背景是否透明
                'backColor' => 0x009688, // 黑色背景，默认白色
                'foreColor' => 0xFFFFFF, // 字体颜色，白色
                'maxLength' => 6,
                'minLength' => 4,
                'offset' => 3,
            ],
            'login' => [
                'class' => 'system\modules\user\components\LoginAction',
            ]
        ];
    }

    /**
     * 登出系统
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        if (Yii::$app->user->logout()) {
            Yii::$app->getSession()->setFlash('ok', '已成功退出');
        }

        return $this->goHome();
    }


}
