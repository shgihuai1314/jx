<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/4/1
 * Time: 下午2:24
 */

namespace system\modules\notify\controllers;


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