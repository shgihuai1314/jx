<?php
return [
    //基本属性
    'base'=>[
        'module_id' => 'role',
        'name' => '角色',
        'describe' => '提供了系统中的角色管理，为后台管理提供了方便的管理',
        'version' => '1.0',
        'core' => 1,
        'author' => '雨滴科技',
    ],
    //模块
    'modules' => [
        'role' => [
            'class' => 'system\modules\role\Module',
        ],
    ],
];