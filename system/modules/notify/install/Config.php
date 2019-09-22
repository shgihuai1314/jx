<?php
return [
    //基本属性
    'base'=>[
        'module_id' => 'notify',
        'name' => '消息',
        'describe' => '负责系统中所有提醒的发送，管理等功能',
        'version' => '1.0',
        'core' => 1,
        'author' => '雨滴科技',
    ],
    //模块
    'modules' => [
        'notify' => [
            'class' => 'system\modules\notify\Module',
        ],
    ],
    //组件
    'components' => [
        //消息组件
        'systemMessage' => [
            'class' => 'system\modules\notify\components\Message',
        ],
    ],
];
