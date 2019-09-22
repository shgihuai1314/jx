<?php

$module_id = 'cron';
return [
    //基本属性
    'base'=>[
        'module_id' => $module_id,
        'name' => '计划任务', //模块名称
        'describe' => '通过workman的定时器功能执行计划任务',//模块描述
        'version' => '1.0',
        'core' => 1,
        'author' => '雨滴科技',
    ],
    //模块
    'modules' => [
        $module_id => [
            'class' => 'system\modules\\' . $module_id . '\Module',
        ],
    ],
];
