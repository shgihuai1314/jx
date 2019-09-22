<?php

namespace system\modules\cron\migrations;

use console\models\Migration;

class M180607073057Add_menu extends Migration
{
    // 所属模块
    public $module_id = 'cron';

    // 更新说明
    public $description = '添加计划任务模块菜单';

    // 版本号
    public $version = '1.0';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //菜单
        'menu' => [
            'menu_name' => '计划任务',
            'module' => 'cron',
            'path' => 'system/modules/cron',
            'pid' => 3,
            'icon' => 'iconfont icon-task-list',
            'children' => [
                [
                    'menu_name' => '任务管理',
                    'path' => 'cron/task/index',
                    'icon' => 'iconfont icon-tasks',
                ],
                [
                    'menu_name' => '定时器管理',
                    'path' => 'cron/timer/index',
                    'icon' => 'iconfont icon-timer',
                ]
            ]
        ]
    ];

    public function up()
    {
        parent::up();
    }

    public function down()
    {
        parent::down();
    }
}
