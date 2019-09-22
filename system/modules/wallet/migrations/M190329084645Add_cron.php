<?php

namespace system\modules\wallet\migrations;

use console\models\Migration;

class M190329084645Add_cron extends Migration
{
    // 所属模块
    public $module_id = 'wallet';

    // 更新说明
    public $description = '添加定时结算的计划任务';

    // 版本号
    public $version = '1.0';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //计划任务
        'cron' => [
            'name' => '自动结算',// 任务名称
            'command' => 'wallet/settle',// 执行命令
            'desc' => '根据订单进行结算',// 任务说明
        ],
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
