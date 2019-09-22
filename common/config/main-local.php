<?php
$hostname = env('hostname');// 数据库地址
$port = env('port');// 端口号
$dbname = env('dbname');// 数据库名称

return [
    'components' => [
        //主数据库
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host=$hostname;port=$port;dbname=$dbname",
            'username' => env('username'),
            'password' => env('password'),
            'charset' => env('db_encode'),
        ],
    ],
];
