<?php

$module_id = 'article';
return [
    //基本属性
    'base'=>[
        'module_id' => $module_id,
        'name' => '文章管理', //模块名称
        'describe' => '文章管理',//模块描述
        'version' => '1.0',
        'core' => 0,
        'author' => '雨滴科技',
    ],
    //模块
    'modules' => [
        $module_id => [
            'class' => 'system\modules\\' . $module_id . '\Module',
        ],
    ],
];
