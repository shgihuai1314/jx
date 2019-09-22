<?php
// 定义模块id
$module_id = 'main';

return [
    // 基本属性
    'base' => [
        'module_id' => $module_id,  // 模块id，唯一标识
        'name' => '主模块',      // 模块名称
        'describe' => '系统主模块，提供了后台管理，菜单管理，配置管理，模块管理，数据备份等系统必须的功能',
        'version' => '1.0',     // 版本号，目前无用，后面可以用来升级模块用
        'core' => 1,            // 是否核心模块，1是，0否
        'author' => '雨滴科技',     // 模块作者，必须写公司的全名
    ],
    // 提供的模块
    'modules' => [
        $module_id => [
            'class' => 'system\modules\\' . $module_id . '\Module',
        ],
    ],
    // 提供的组件，可选项
    'components' => [
        // 系统配置组件
        'systemConfig' => [
            'class' => 'system\modules\main\components\Config',
        ],
        // 全局配置组件
        'systemOption' => [
            'class' => 'system\modules\main\components\Option',
        ],
	    // 日志组件，包括登录日志
	    'systemLog' => [
		    'class' => 'system\modules\main\components\Log',
	    ],
	    // 操作日志组件
	    'systemOperateLog' => [
		    'class' => 'system\modules\main\components\LogOperate',
	    ],
        // 保存附件组件
        'systemFileInfo' => [
            'class' => 'system\modules\main\components\SaveFile'
        ],
        //转码组件
        'systemTransFile' => [
            'class' => 'system\modules\main\components\Trans',
        ],
        // 处理视频文件
        'systemMediaFile' => [
            'class' => 'system\modules\main\components\Ffmpeg',
        ]
    ],
];