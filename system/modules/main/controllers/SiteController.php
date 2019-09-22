<?php
/**
 * service-hall.
 * User: ligang
 * Date: 2017/8/21 上午11:58
 */
namespace system\modules\main\controllers;

use yii\web\Controller;

/**
 * Class SiteController, 无权限判断，负责错误显示等
 * @package system\modules\main\controllers
 */
class SiteController extends Controller
{
    public $layout = false;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
}