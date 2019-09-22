<?php
defined('YII_DEBUG') or define('YII_DEBUG', false);  // IE8调试时必须关闭debug，否则可能会有些js执行错误
defined('YII_ENV') or define('YII_ENV', 'dev');
define('APP_NAME', 'admin'); // 应用名称，必填

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../common/config/bootstrap.php');
require(__DIR__ . '/../system/config/bootstrap.php');

$config = \yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../common/config/main.php'),
    require(__DIR__ . '/../common/config/main-local.php'),
    require(__DIR__ . '/../system/config/main.php'),
    require(__DIR__ . '/../system/config/main-local.php')
);

// 绑定baseController中的行为，可以设置多个
$config['params']['baseBehavior'] = [
    'customValidate' => [
        'class' => \system\modules\main\components\ValidateBehavior::className(),
    ]
];
$app = new yii\web\Application($config);
$app->run();