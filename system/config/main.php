<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-system',
    'name' => '雨滴科技管理平台',
    'timeZone' => env('timeZone', 'Asia/Shanghai'),
    'basePath' => dirname(__DIR__),
    'sourceLanguage' => env('sourceLanguage', 'en-US'),
    'language' => env('language', 'zh-CN'),
    'defaultRoute' => 'main/default/index',
    'controllerNamespace' => 'system\controllers',
    'bootstrap' => [
        'log',
        'system\modules\main\components\LoadModule'
    ],
    'modules' => [

    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-system',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        // 用户组件
        'user' => [
            'class' => 'system\modules\user\components\WebUser',
            'loginUrl' => ['/user/default/login'],
            'identityClass' => 'system\modules\user\components\UserIdentity',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-system', 'httpOnly' => true],
            'acceptableRedirectTypes' => ['text/html', 'application/xhtml+xml', 'application/x-ms-application']
        ],
        'session' => [
            // this is the name of the session cookie used for login on the system
            'name' => 'session-system',
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
            'errorAction' => 'site/error', //'/main/site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => true,
            'rules' => [
            ],
        ],
        'assetManager' => [
            'appendTimestamp' => true, //YII_ENV_DEV, // 开发模式下，禁止缓存
            'bundles'=>[
                'yii\web\JqueryAsset' => [
                    'js' => []
                ]
            ],
        ],
    ],
    'params' => $params,
];
