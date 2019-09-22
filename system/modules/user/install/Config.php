<?php
return [
    //基本属性
    'base'=>[
        'module_id' => 'user',
        'name' => '用户',
        'describe' => '管理系统中所有的用户',
        'version' => '1.0',
        'core' => 1,
        'author' => '雨滴科技',
    ],
    //模块
    'modules' => [
        'user' => [
            'class' => 'system\modules\user\Module',
        ],
    ],
    //组件
    'components' => [

    ],
];
