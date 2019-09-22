<?php

use Workerman\Worker;
use Workerman\Handle\Task;

require_once __DIR__ . '/../Autoloader.php';

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

$handle = new Task();

$worker = new Worker();
$worker->count = 1;
$worker->name = 'cron';

$worker->onWorkerStart = [$handle, 'workerStart'];

Worker::runAll();