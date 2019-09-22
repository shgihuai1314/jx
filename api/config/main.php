<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php')
);

return [
    'id' => 'app-api',
    'name' => '考试Api系统',
    'timeZone' => env('timeZone', 'Asia/Shanghai'),
    'basePath' => dirname(__DIR__),
    'sourceLanguage' => env('sourceLanguage', 'en-US'),
    'language' => env('language', 'zh-CN'),
    'defaultRoute' => 'site/index',
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'runtimePath' => '@system/runtime',  // 设置runtimePath
    'modules' => [
        'v1' => [
            'class' => 'api\modules\v1\Module'
        ],
    ],
    'components' => [
        // 系统配置组件
        'systemConfig' => [
            'class' => 'system\modules\main\components\Config',
        ],
        // 日志组件，包括登录日志
        'systemLog' => [
            'class' => 'system\modules\main\components\Log',
        ],
        // 操作日志组件
        'systemOperateLog' => [
            'class' => 'system\modules\main\components\LogOperate',
        ],
        // 保存附件组件
        'systemFileInfo' => [
            'class' => 'system\modules\main\components\SaveFile'
        ],
        //消息组件
        'systemMessage' => [
            'class' => 'system\modules\notify\components\Message',
        ],

        //订单日志组件
        'systemOrderLog' => [
            'class' => 'system\modules\wallet\components\OrderLog'
        ],

        //钱包流水组件
        'systemWalletDetails' => [
            'class' => 'system\modules\wallet\components\WalletDetails'
        ],

        // 用户组件
        'user' => [
            'identityClass' => 'system\modules\user\components\UserIdentity',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-api', 'httpOnly' => true],
        ],

        'request' => [
            'csrfParam' => '_csrf-api',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser'
            ],
        ],
        //日志组件配置
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'logTable' => '{{%tab_log_error}}',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => true,
            'rules' => [
                '<controller>/<action>' => API_VERSION . '/<controller>/<action>',
                //针对支付的异步回调和同步回调设定的规则
                'callback/notify/<type:\w+>' =>  API_VERSION . '/callback/notify',
                'callback/synchro/<type:\w+>' => API_VERSION . '/callback/synchro',
            ],
        ],

        'systemPayment' => [
            'class' => 'system\modules\payment\components\SelectPay'
        ],

        'systemIntegral' => [
            'class' => 'system\modules\integral\components\ScoreLog'
        ],
        // 处理视频文件
        'systemMediaFile' => [
            'class' => 'system\modules\main\components\Ffmpeg',
        ]
    ],
    'params' => $params,
];
