<?php

namespace Api;

use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\Response;
use Yii;

class BaseApiController extends Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        Yii::$app->user->enableSession = false;
        //Yii::info('web_server_host:'.json_encode(array_keys(Yii::$app->systemConfig->getValue('WEB_SERVER_HOST', []))));
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => array_keys(Yii::$app->systemConfig->getValue('WEB_SERVER_HOST', [])),
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [],
            ],
        ];

        // 如果请求方法是OPTIONS 或者当前action设置了不需要认证，则取消身份验证
        if (Yii::$app->getRequest()->getMethod() == 'OPTIONS' ||
            ($this->hasProperty('notAuthAction') &&
                (in_array('*', $this->notAuthAction) || in_array($this->action->id, $this->notAuthAction))
            )
        ) {
            //unset($behaviors['authenticator']);
            // 请求头信息
            $authHeader = Yii::$app->request->getHeaders()->get('Authorization');
            // 获取token
            if ($authHeader !== null && preg_match("/^Bearer\\s+(.*?)$/", $authHeader, $matches)) {
                $token = $matches[1];
                // 登陆当前用户
                $res = Yii::$app->user->loginByAccessToken($token);
                //var_dump($res);exit;
                if (!$res) {
                    Yii::$app->user->logout();
                }
            }
        } else {
            $behaviors['authenticator'] = [
                'class' => HttpBearerAuth::className(),
            ];
        }

//        $behaviors['contentNegotiator'] = [
//            'class' => ContentNegotiator::className(),
//            'formats' => [
//                'application/json' => Response::FORMAT_JSON
//            ]
//        ];

        return $behaviors;
    }

    /**
     * 返回api接口格式数组
     * @param boolean|mixed $res 成功或失败
     * @param array|string $message 提示信息
     * @param mixed $data 返回数据
     * @return array
     */
    public function apiReturn($res, $message, $data = null)
    {
        // $res为true code返回0；为false则code返回1
        $code = $res ? 0 : 1;

        // 如果$message为数组，则$message[0]表示成功消息，$message[1]表示失败消息；
        // 如果$message为字符串，则不管$res为true或false都返回$message
        $message = is_array($message) ? ArrayHelper::getValue($message, $code, '') : $message;

        return ArrayHelper::merge([
            'code' => $code,
            'message' => $message,
        ], $res && $data ? ['data' => $data] : []);
    }

}
