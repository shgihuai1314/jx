<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/6/19
 * Time: 下午2:21
 */

namespace system\modules\user\components;

use system\modules\role\models\AuthAssign;
use system\modules\user\models\User;
use yii;
use yii\base\Action;
use system\modules\user\models\UserLoginError;
use system\modules\user\models\LoginForm;
use yii\captcha\CaptchaValidator;
use yii\web\ForbiddenHttpException;

/**
 * Class LoginAction 登录的action
 * @package system\modules\user\components
 */
class LoginAction extends Action
{
    public $is_admin = true; // 是否是管理员登录
    public $view = 'login'; // 模版
    public $layout = false; // 是否使用布局
    public $captchaAction = '/user/default/captcha'; // 显示验证码action

    /**
     * 用户登录
     * @return string|\yii\web\Response
     * @throws ForbiddenHttpException
     */
    public function run()
    {
        // 如果已经登录，那么直接跳转到首页
        if (!Yii::$app->user->isGuest) {
            return $this->controller->goHome();
        }

        // 判断ip和用户名是否可以登录
        if (!$this->_canLogin()) {
            throw new ForbiddenHttpException('您的ip已经被锁定，不允许登录！请稍后再试！');
        }

        //记住登录前的页面；登录成功后跳回到此页面
        //var_dump(Yii::$app->request->referrer);exit;
        //Yii::$app->user->setReturnUrl(Yii::$app->request->referrer);

        // 在这里选择合适的登录方式，
        $login_cas_type = Yii::$app->systemConfig->getValue('USER_LOGIN_CAS_APP', []);

        if (in_array(APP_NAME, $login_cas_type)) {
            return $this->_loginByCas();
        } else {
            return $this->_loginByLocal();
        }
    }

    /**
     * 使用本地认证方式进行验证
     * @return string|yii\web\Response
     */
    private function _loginByLocal()
    {
        // 是否需要验证码
        $showCaptcha = $this->_showCaptcha();

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post(), '')) {
            // 验证用户名
            if (!UserLoginError::canLogin('username', $model->username)) {
                Yii::$app->getSession()->setFlash('error', '此用户已被锁定，不允许登录！请稍后再试！');
                return $this->controller->refresh();
            }

            // 验证码
            if ($showCaptcha) {
                $validator = new CaptchaValidator([
                    'captchaAction' => $this->captchaAction
                ]);
                if (!$validator->validate($model->verifyCode, $error)) {
                    Yii::$app->getSession()->setFlash('error', '验证码不正确！');
                    return $this->controller->refresh();
                }
            }

            /** @var UserIdentity $user */
            $user = UserIdentity::getUserByUsername($model->username);
            if (empty($user)) {
                Yii::$app->getSession()->setFlash('error', '账号不存在！');
                return $this->controller->refresh();
            }

            // 获取用户认证接口列表
            $authList = Yii::$app->systemConfig->getValue('USER_AUTH_API_LIST', []);
            // 需要到第三方认证的入口
            $gateways = Yii::$app->systemConfig->getValue('API_AUTH_GATEWAYS', []);

            // 管理员认证、认证接口为空、入口类型不在API_AUTH_GATEWAYS配置中 只用系统验证
            if ($this->is_admin || empty($authList) || !in_array(APP_NAME, $gateways)) {
                $loginRes = $model->login();
            } else {
                $loginRes = false;
                // 循环遍历验证接口列表
                foreach ($authList as $auth) {
                    if ($auth == 'local') {// 系统验证
                        $loginRes = $model->login();
                    } elseif ($auth == 'no-local') {// 不做验证
                        continue;
                    } else { // 第三方接口，如common\api\Srun4k
                        $auth = str_replace('/', '\\', $auth);
                        if (method_exists($auth, 'login')) {
                            $data = [
                                'username' => $model->username,
                                'password' => $model->password,
                            ];
                            $loginRes = call_user_func([$auth, 'login'], $data);
                        } else {
                            $loginRes = [
                                'code' => 1,
                                'message' => '认证接口无法调用'
                            ];
                        }
                    }

                    // 如果验证成功，不再继续验证
                    if ($loginRes['code'] == 0) {
                        break;
                    }
                }
            }

            if ($loginRes['code'] == 0) {
                // 登录前的再次验证
                $loginRes = $this->loginValid($user);
                if ($loginRes['code'] == 0) {
                    // 登录用户
                    Yii::$app->user->login($user, $model->rememberMe ? 3600 * 24 * 30 : 0);
                    Yii::$app->getSession()->setFlash('ok', '登录成功');
                    Yii::info("登录后的跳转Url：".Yii::$app->request->referrer);
                    $loginApps = Yii::$app->systemConfig->getValue('USER_LOGIN_REFERRER_APP', []);
                    if (in_array(APP_NAME, $loginApps)) {
                        return $this->controller->goBack();
                    } else {
                        return $this->controller->goHome();
                    }
                }
            }

            // 登录失败
            Yii::$app->getSession()->setFlash('error', '登录失败！'.$loginRes['message']);
            return $this->controller->refresh();
        }

        //记住登录前的页面；登录成功后跳回到此页面
        Yii::info('getReturnUrl:' . Yii::$app->user->getReturnUrl());
        Yii::info('getHomeUrl:' . Yii::$app->getHomeUrl());
        Yii::info('referrer:' . Yii::$app->request->referrer);
        // 允许自动跳转到登录前的应用
        $loginApps = Yii::$app->systemConfig->getValue('USER_LOGIN_REFERRER_APP', []);
        if (in_array(APP_NAME, $loginApps) && Yii::$app->user->getReturnUrl() == Yii::$app->getHomeUrl()) {
            Yii::info("登录后的跳转Url：".Yii::$app->request->referrer);
            Yii::$app->user->setReturnUrl(Yii::$app->request->referrer);
        }

        if ($this->layout) {
            if ($this->layout === true) {
                return $this->controller->render($this->view, [
                    'showCaptcha' => $showCaptcha, // 是否显示验证码
                ]);
            } else {
                $this->controller->layout = $this->layout;
                return $this->controller->render($this->view, [
                    'showCaptcha' => $showCaptcha, // 是否显示验证码
                ]);
            }

        } else {
            return $this->controller->renderPartial($this->view, [
                'showCaptcha' => $showCaptcha, // 是否显示验证码
            ]);
        }
    }

    /**
     * Cas 认证方式
     * @return yii\web\Response
     * @throws yii\base\InvalidConfigException
     * @throws yii\web\NotFoundHttpException
     */
    private function _loginByCas()
    {
        $casData = Yii::$app->systemConfig->getValue('USER_LOGIN_CAS', []);
        if (!isset($casData['loginUrl'], $casData['validUrl'])) {
            throw new yii\base\InvalidConfigException('无效的Cas配置');
        }

        // cas登录地址和验证地址
        $casLoginUrl = $casData['loginUrl']; //比如：https://cas.whcp.edu.cn/lyuapServer/login;
        $casServerUrl = $casData['validUrl']; //比如：https://cas.whcp.edu.cn/lyuapServer/proxyValidate;
        //$loginUrl = Yii::$app->request->absoluteUrl; //比如：http://imedia.whcp.edu.cn/index.php/media/user/login;
        $loginUrl = \Yii::$app->urlManager->createAbsoluteUrl(Yii::$app->user->loginUrl); // 登录的绝对url
        // 如果获取到ticket，那么根据ticket获取用户信息
        if (Yii::$app->request->get('ticket')) {
            // 验证ticket的方式：https://cas.whcp.edu.cn/lyuapServer/proxyValidate?service=http://imedia.whcp.edu.cn/index.php/media/user/login&ticket=ST-308-nwSyeHFuXeHVa73KdTef
            $urlParams = [
                'ticket' => Yii::$app->request->get('ticket'),
                'service' => $loginUrl,
            ];
            $url = $casServerUrl . '?' . http_build_query($urlParams);
            //echo $url;exit;
            $data = file_get_contents($url);
            $data = str_replace('cas:', '', $data);  // 把cas：去掉，否则simplexml_load_string没办法解析
            $data = simplexml_load_string($data); // 解析成对象
            $data = json_decode(json_encode($data), true);  // 把对象通过两次转换变为数组
            //print_r($data);exit;
            if (isset($data['authenticationSuccess']['user'])) {
                $username = $data['authenticationSuccess']['user'];
                $userModel = UserIdentity::getUserByUsername($username);
                if (!$userModel) {
                    // TODO 如果用户不存在，那么新建用户,因为系统返回的信息可能不相同，而且不全，所以先不建用户
                    // TODO 向管理员发送警告消息
                    throw new yii\web\NotFoundHttpException('用户在本系统中不存在，请向管理员反馈！');
                }

                // 登录前的再次验证
                $loginRes = $this->loginValid($userModel);
                if ($loginRes['code'] == 0) {
                    // 登录用户
                    Yii::$app->user->login($userModel);
                    Yii::$app->getSession()->setFlash('ok', '登录成功');
                    return $this->controller->goBack();
                } else {
                    throw new ForbiddenHttpException($loginRes['message']);
                }
            } else {
                // 跳转到cas认证页面
                return $this->controller->redirect($casLoginUrl . '?service=' . $loginUrl);
            }
        } else {
            // 跳转到cas认证页面
            return $this->controller->redirect($casLoginUrl . '?service=' . $loginUrl);
        }
    }

    /**
     * 用户基本验证后，再次验证是否允许登录
     * @param $user UserIdentity 用户模型
     * @return array
     */
    private function loginValid($user)
    {
        // 非管理员禁止登录
        //if ($this->is_admin && $user->is_admin != 1) {
        if (!AuthAssign::isSuper($user->user_id)) {
            return [
                'code' => 1,
                'message' => '此账号不是管理员，没有权限登录！',
            ];
        }

        // 用户状态异常； @TODO 用户连续登录错误xx次，账号立即被锁定，不能再登录，加一个配置参数，是否锁定用户
        if ($user->status != User::STATUS_ACTIVE) {
            $user_status_list = Yii::$app->systemConfig->getValue('USER_STATUS_LIST', []);
            if (!$user_status_list[$user->status]) {
                return [
                    'code' => 1,
                    'message' => '用户状态异常，不能登录，请联系管理员！',
                ];
            } else {
                return [
                    'code' => 1,
                    'message' => '用户状态:'.$user_status_list[$user->status].'，不能登录，请联系管理员！',
                ];
            }
        }

        // 写登录日志
        Yii::$app->systemLog->write([
            'type' => 'login',
            'target_id' => $user->user_id,
            'user_id' => $user->user_id,
            'content' => "用户：{$user->realname} 登录成功",
        ]);

        return [
            'code' => 0,
            'message' => '验证成功'
        ];
    }

    /**
     * 是否允许登录
     * @return bool
     */
    private function _canLogin()
    {
        return UserLoginError::canLogin('ip', Yii::$app->request->getUserIP());
    }

    /**
     * 是否显示验证码
     * @return bool
     */
    private function _showCaptcha()
    {
        // 先判断是否要显示验证码
        $show_captcha = Yii::$app->systemConfig->getValue('USER_LOGIN_SHOW_CAPTCHA', 0);
        if ($show_captcha == 1) {
            return true;
        }

        return UserLoginError::showCaptcha();
    }
}