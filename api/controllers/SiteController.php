<?php

namespace api\controllers;

use yii\web\Controller;
use Yii;

/**
 * Site controller
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

    /**
     * 首页
     */
    public function actionIndex()
    {
        echo '这是site/index，系统正在升级，请稍后访问';
        exit;
    }

}