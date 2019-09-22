<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'zmZKqgqjImX23Tjlg1asdfjklSDF2123jkin292376m0hJZ_dEnNiyN7asdfasdfasdf',
        ],
        'assetManager' => [
            //'linkAssets' => YII_ENV_DEV, // assets文件用快捷链接的形式
        ],
    ],
];

if (YII_DEBUG) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'],
        'traceLine' => '<a href="phpstorm://open?file={file}&line={line}">{file}:{line}</a>',
    ];
}

return $config;
