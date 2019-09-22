<?php
/**
 * service-hall.
 * User: ligang
 * Date: 2017/9/9 上午11:47
 */
namespace system\components;

use yii;
use yii\base\ActionFilter;

class AccessControl extends ActionFilter
{
    public $allowPath = [];         // 允许的path
    public $allowModules = [];      // 允许的模块
    //public $allowController = [];   // 允许的控制器，暂时没用
    //public $allowAction = [];       // 允许的action
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        //获取路径
        $path = Yii::$app->request->pathInfo ?: Yii::$app->defaultRoute;

        if (parent::beforeAction($action)) {

            // 判断模块权限
            if ($this->allowModules != '*' && !in_array($action->controller->module->id, $this->allowModules)) {
                throw new yii\web\NotFoundHttpException('没有权限访问');
            }

            // 判断path
            if ($this->allowPath != '*') {
                $valid = false;
                foreach ($this->allowPath as $allowPath) {
                    if ($allowPath == $path || (($pos = strpos($allowPath, '*')) !== false && !strncmp($path, $allowPath, $pos))) {
                        $valid = true;
                        break;
                    }
                }

                if (!$valid) {
                    throw new yii\web\NotFoundHttpException('没有权限访问');
                }
            }

            return true;
        }

        return false;
    }

}