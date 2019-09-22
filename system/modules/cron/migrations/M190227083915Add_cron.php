<?php

namespace system\modules\cron\migrations;

use console\models\Migration;

class M190227083915Add_cron extends Migration
{
    // 所属模块
    public $module_id = 'cron';

    // 更新说明
    public $description = '增加日程提醒定时任务';

    // 版本号
    public $version = '1.0';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //计划任务
        'cron' => [
            'name' => '日程提醒',// 任务名称
            'command' => 'schedule/send_message',// 执行命令
            'desc' => '向当前用户发送消息通知',// 任务说明
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
