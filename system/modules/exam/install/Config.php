<?php

$module_id = 'exam';
return [
    //基本属性
    'base'=>[
        'module_id' => $module_id,
        'name' => '考试模块', //模块名称
        'describe' => '提供考试',//模块描述
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
