<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/15
 * Time: 下午2:14
 */

namespace system\modules\user\controllers;


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