<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        //邮件
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
        ],
    ],
];
