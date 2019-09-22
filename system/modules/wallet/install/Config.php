<?php

$module_id = 'wallet';
return [
    //基本属性
    'base' => [
        'module_id' => $module_id,
        'name' => '钱包账户', //模块名称
        'describe' => '账户余额变动提现',//模块描述
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

    //组件
    'components' => [
        //订单日志组件
        'systemOrderLog' => [
            'class' => 'system\modules\wallet\components\OrderLog'
        ],

        //钱包流水组件
        'systemWalletDetails' => [
            'class' => 'system\modules\wallet\components\WalletDetails'
        ]
    ],
];
