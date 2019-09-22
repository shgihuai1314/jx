<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-console',
    'timeZone' => env('timeZone', 'Asia/Shanghai'),
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'console\controllers',
    'bootstrap' => [
        'log',
        'system\modules\main\components\LoadModule',
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => 'console\controllers\MigrateController',
        ],
    ],
    'components' => [
        //订单日志组件
        'systemOrderLog' => [
            'class' => 'system\modules\wallet\components\OrderLog'
        ],

        //钱包流水组件
        'systemWalletDetails' => [
            'class' => 'system\modules\wallet\components\WalletDetails'
        ],

        'systemMessage' => [
            'class' => 'system\modules\notify\components\Message',
        ],

        'log' => [
            /*'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],*/
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'logTable' => '{{%tab_log_error}}',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];
