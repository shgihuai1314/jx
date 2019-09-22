<?php

namespace system\modules\cron\migrations;

use console\models\Migration;

class M180612134549Add_cron extends Migration
{
    // 所属模块
    public $module_id = 'cron';

    // 更新说明
    public $description = '添加备份数据库的计划任务';

    // 版本号
    public $version = '1.3';

    public $config = [
        'cron' => [
            'name' => '备份数据库',
            'command' => 'backup/db',
            'desc' => '定时备份数据库'
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
