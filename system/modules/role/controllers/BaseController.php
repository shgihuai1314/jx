<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/16
 * Time: 上午10:57
 */

namespace system\modules\role\controllers;


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