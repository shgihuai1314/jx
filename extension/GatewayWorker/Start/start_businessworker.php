<?php

use \Workerman\Worker;
use \GatewayWorker\BusinessWorker;

// 自动加载类
require_once __DIR__ . '/../autoload.php';

define('APP_NAME', 'console'); // 应用名称，必填
require(__DIR__ . '/../../../vendor/autoload.php');
require(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../../common/config/bootstrap.php');
require(__DIR__ . '/../../../console/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../common/config/main.php'),
    require(__DIR__ . '/../../../common/config/main-local.php'),
    require(__DIR__ . '/../../../console/config/main.php'),
    require(__DIR__ . '/../../../console/config/main-local.php')
);

$application = new yii\console\Application($config);
$app = new \system\modules\main\components\LoadModule();
$app->bootstrap($application);

// bussinessWorker 进程
$worker = new BusinessWorker();
// worker名称
$worker->name = 'BusinessWorker';
// bussinessWorker进程数量
$worker->count = 4;
// 服务注册地址
$worker->registerAddress = '127.0.0.1:8904';

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

