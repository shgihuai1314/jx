<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/9
 * Time: 下午1:45
 */

namespace system\modules\main\controllers;



class BaseController extends \system\controllers\BaseController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (\Yii::$app->user->isGuest) {
                \Yii::$app->user->loginRequired();
                return false;
            }

            return true;
        }

        return false;
    }
}