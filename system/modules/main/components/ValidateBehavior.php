<?php
/**
 * service-hall.
 * User: ligang
 * Date: 2017/9/6 下午4:51
 */
namespace system\modules\main\components;

use system\modules\role\models\AuthAssign;
use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

class ValidateBehavior extends ActionFilter
{
    //关闭csrf验证的方法，这里不需要定义，全部定义在各自的控制中，以下三个参数都是如此，如果有其他业务相关参数，可以全部在这里定义
    //public $disableCsrfAction = [];

    // 基础忽略列表，列表中不做权限验证
    //public $ignoreList = [];

    // 依赖检查，如果有值的权限，那么可以放行，key是要判断的权限，值是依赖的权限，只要值中一个有权限即可放行
    //public $dependIgnoreList = [];

    // 依赖检查的另一种写法，如果有key的权限，那么value数组中的权限放行
    //public $dependIgnoreValueList = [];

    /**
     * 在程序执行之前，对访问的方法进行权限验证.
     * @param \yii\base\Action $action
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        //如果未登录，则直接返回
        if (Yii::$app->user->isGuest) {
            Yii::$app->user->loginRequired();
            return false;
            //Yii::$app->end();
        }

        // 关闭csrf验证
        /*if ($this->owner->hasProperty('disableCsrfAction') && in_array($action->id, $this->owner->disableCsrfAction)) {
            $this->owner->enableCsrfValidation = false;
        }*/

        if (parent::beforeAction($action)) {
            //print_r(Yii::$app->user);exit;
            // 如果当前用户非管理员，那么禁止访问
            //if (Yii::$app->user->identity && Yii::$app->user->identity->is_admin != 1) {
            if (Yii::$app->user->getId() && !AuthAssign::isSuper(\Yii::$app->user->identity->getId())){
                Yii::$app->user->logout();
                // 写登录日志
                Yii::$app->systemLog->write([
                    'type' => 'login',
                    'target_id' => Yii::$app->user->getId(),
                    'user_id' => Yii::$app->user->getId(),
                    'content' => '用户：'.Yii::$app->user->identity->realname.' 试图访问后台，已拒绝',
                ]);
                throw new ForbiddenHttpException('当前用户非管理员，禁止访问；已经记录了此次登录日志，请勿重复登录；');
            }

            //获取路径
            $path = Yii::$app->request->pathInfo ?: Yii::$app->defaultRoute;
            //忽略列表
            if ($this->owner->hasProperty('ignoreList') && in_array($path, $this->owner->ignoreList)) {
                return true;
            }

            //忽略依赖列表; key是忽略的权限，value是允许的权限，只有value中的权限存在，那么就允许key使用
            if($this->owner->hasProperty('dependIgnoreList') && array_key_exists($path, $this->owner->dependIgnoreList)){
                $dependArr = $this->owner->dependIgnoreList[$path];
                foreach ($dependArr as $onePath) {
                    if($this->can($onePath)){
                        return true;
                    }
                }
            }

            // key作为依赖的权限，value数组作为忽略的权限
            if($this->owner->hasProperty('dependIgnoreValueList')){
                foreach ($this->owner->dependIgnoreValueList as $permission => $ignoreList) {
                    // 如果dependIgnoreValueList中有'*'，表示当前控制器所有action都忽略权限
                    if (in_array('*', $ignoreList) || in_array($path, $ignoreList)) {
                        if($this->can($permission)){
                            return true;
                        }
                    }
                }
            }

            if ($this->can($path)) {
                return true;
            } else {
                throw new ForbiddenHttpException('没有权限访问');
            }
        }

        return false;
    }

    // 简单调用系统自带的can方法进行校验
    private function can($path)
    {
        if (Yii::$app->user->can($path)) {
            return true;
        }
        return false;
    }
}