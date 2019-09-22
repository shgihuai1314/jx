<?php
namespace system\controllers;

use phpseclib\Crypt\AES;
use system\modules\main\components\WebOfficeAction;
use system\modules\user\components\UserSelectAction;
use system\modules\website\core\utils\AesCtr;
use yii\web\Controller;

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
	        'user-group-select' => [
	        	'class' => UserSelectAction::className()
	        ],
            'web-office' => [
                'class' => WebOfficeAction::className()
            ],
        ];
    }

    /**
     * 浏览器提示升级
     * @return string
     */
    public function actionUnsupportedBrowser()
    {
        return $this->render('browserUpgrade');
    }

    /**
     * 升级提醒
     */
    public function actionUpgrade()
    {
        echo <<<EOF
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>升级中</title>
<body>
<div style="text-align:center;padding-top:100px;">
    系统正在升级，请稍后访问！<br />给您带来不便敬请谅解
</div>
</body>
</html>
EOF;
    }

}