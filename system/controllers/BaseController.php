<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/7
 * Time: 上午10:12
 */

namespace system\controllers;

use system\models\Model;
use system\modules\main\models\Menu;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\Controller;
use yii\web\Response;
use Yii;

/**
 * Class BaseController 所有控制器的基础控制器，由具体的应用传入验证规则和布局文件
 * @package system\controllers
 */
class BaseController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        // 先判断菜单权限是否能够访问
        $path = Yii::$app->request->pathInfo ?: Yii::$app->defaultRoute;
        //$path = substr($path, 0, strpos($path, '&')); // 去除url中的&之后的内容；有些url不规范，比如：http://xxx/article/mobile/index&id=1
        $mobileApp = Yii::$app->systemConfig->getValue('MOBILE_APP_LIST', []);
        $isMobile = in_array(APP_NAME, $mobileApp);
        // 如果是手机端，那么如果当前的url是pc端，那么转换成对应的手机端Url，如果不是，那么放行
        if ($isMobile && $this->module->hasProperty('pcMobileMap')) {
            $pcMobileMap = $this->module->pcMobileMap;
            foreach ($pcMobileMap as $pc => $mobile) {
                if ($pc == trim($path, '/')) {
                    $params = Yii::$app->request->get();
                    array_unshift($params, $mobile);
                    $this->redirect($params);
                    Yii::$app->end();
                }
            }
        }

        // 判断IP是否可以访问
        if (!$this->_checkAccess()) {
            throw new ForbiddenHttpException('当前IP：'.Yii::$app->request->getUserIP().'不允许访问系统，请联系管理员！');
        }

        $app_name = defined('APP_NAME') ? APP_NAME : '';
        // 应用名称
        if ($app_name != 'admin') {
            throw new ForbiddenHttpException('没有权限访问功能');
        }

        // 定义layout
        if (!$this->layout && $layout = $this->_getLayout()) {
            $this->layout = $layout;
        }

        // 绑定行为 注意：行为的名称一定要定义成不同的名称，否则可能会被覆盖掉；
        if (isset(Yii::$app->params['baseBehavior'])) {
            foreach (Yii::$app->params['baseBehavior'] as $key => $value) {
                $this->attachBehavior($key, $value);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        // 关闭csrf验证
        if ($this->hasProperty('disableCsrfAction') && in_array($action->id, $this->disableCsrfAction)) {
            $this->enableCsrfValidation = false;
        }

        if (parent::beforeAction($action)) {

            return true;
        }

        return false;
    }

    /**
     * ajax返回
     * @param $data
     * @return string|Response
     */
    public function ajaxReturn($data)
    {
        return $this->asJson($data);
    }

    /**
     * 闪屏消息，在桌面停留3s钟后自动消失
     * @param $type
     * @param $message
     */
    public function flashMsg($type, $message)
    {
        Yii::$app->getSession()->setFlash($type, $message);
    }

    /**
     * AJAX处理
     * @param Model $model
     * @param string $action
     * @param array $params
     * @return bool|string|Response
     */
    protected function ajax($model, $action, $params = [])
    {
        if (!$params) {
            $params = Yii::$app->request->post();
        }

        switch ($action) {
            case 'edit' :
                $field = ArrayHelper::getValue($params, 'field', '');
                $val = ArrayHelper::getValue($params, 'val', 0);

                if (!empty($model)) {
                    $model->$field = $val;
                    $res = $model->save();
                    $error = '';
                    if (!$res) {
                        foreach ($model->errors as $key => $val) {
                            $error .= $val[0] . ' ';
                            break;
                        }
                    }
                    return $this->getAjaxReturn($res, ['', $error]);
                } else {
                    return $this->getAjaxReturn(false, '处理的对象不存在！');
                }

            case 'del' :
                $id = ArrayHelper::getValue($params, 'id', []);

                $action = ArrayHelper::getValue($params, 'action');
//                print_r($params);die;
                return $this->getAjaxReturn($model::BatchDel($id, $action), ['', '未知错误！']);
            default:
                return false;
        }
    }

    /**
     * 保存后操作
     * @param $model       object|bool    保存对象或者true|false
     * @param null $goto   成功后跳转, 值为null或者失败则不跳转
     * @param string $success
     * @param string $error
     * @throws \yii\base\ExitException
     */
    protected function getSaveRes($model, $goto = null, $success = '操作成功！', $error = '操作失败！')
    {
        $res = is_object($model) ? $model->save() : $model;
        if ($res) {//成功
            if ($success) $this->flashMsg('success', $success);

            if ($goto === null) {// 返回前一页
                echo "<script>window.history.go(-2)</script>";
                exit();
            } elseif ($goto !== false) {// 跳转到指定页面
                $this->redirect($goto);
                Yii::$app->end();
            }
            // $goto === false 不作处理
        }  else {//失败
	        if (YII_ENV_DEV && is_object($model)) {// 在开发环境下，如果数据对象保存失败，在浏览器打印出错误信息
	            $firstErrors = $model->firstErrors;
                $error .= reset($firstErrors);
            }
            $this->flashMsg('error', $error);
        }
    }

    /**
     * 获取ajax操作返回值
     * @param mixed $res 成功或失败
     * @param array|string $msg 提示信息：字符串表示成功或失败都提示的内容，数组则$msg[0]表示成功消息，$msg[1]表示失败消息
     * @param array $data 成功时返回的数据，只有当$res为true时返回
     * @return string|Response
     */
    protected function getAjaxReturn($res, $msg = null, $data = [])
    {
        // $res为true code返回0；为false则code返回1
        $code = $res ? 0 : 1;
        // 如果$msg为数组，则$msg[0]表示成功消息，$msg[1]表示失败消息；如果$msg为字符串，则不管$res为true或false都返回$msg
        $msg = is_array($msg) ? ArrayHelper::getValue($msg, $code, '') : $msg;

        return $this->ajaxReturn([
            'code' => $code,
            'msg' => $msg,
            'message' => $msg,
            'data' => $res ? $data : []
        ]);
    }

    /**
     * 应该采用的layout
     * @return string
     */
    private function _getLayout()
    {
        $layout = '';
        
        if (isset(Yii::$app->params['layout'])) {
            $layoutArray = Yii::$app->params['layout'];
            $path = Yii::$app->request->pathInfo ?: Yii::$app->defaultRoute;
            // 如果有设置到具体的path，那么直接采用
            if (isset($layoutArray[$path])) {
                $layout = $layoutArray[$path];
            } else {
                // 遍历找到对应的layout
                foreach (Yii::$app->params['layout'] as $key => $value) {
                    if ((($pos = strpos($key, '*')) !== false && !strncmp($path, $key, $pos))) {
                        $layout = $value;
                        break;
                    }
                }
                // 如果找不到匹配的，那么判断是否有默认的
                if ($layout == '') {
                    if (isset($layoutArray['default'])) {
                        $layout = $layoutArray['default'];
                    }
                }
            }
        }
        
        return $layout;
    }

    /**
     * 判断当前用户IP是否允许访问
     * @return bool if access is granted
     */
    private function _checkAccess()
    {
        $ip = Yii::$app->getRequest()->getUserIP(); // 用户ip
        $forbidden = Yii::$app->systemConfig->getValue('SYSTEM_FORBIDDEN_IP', []); // 禁止ip 优先级更改
        $allow = Yii::$app->systemConfig->getValue('SYSTEM_ALLOW_IP', []);  // 允许ip

        // 如果在禁止ip内，那么直接返回false
        foreach ($forbidden as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                return false;
            }
        }

        // 如果允许ip为空，那么返回true
        if (empty($allow)) {
            return true;
        }

        foreach ($allow as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                return true;
            }
        }

        return false;
    }
}