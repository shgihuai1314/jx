<?php

namespace system\modules\cron\migrations;

use console\models\Migration;

class M190213071800Add_system_config extends Migration
{
    // 所属模块
    public $module_id = 'cron';

    // 更新说明
    public $description = '增加生成直播地址定时任务';

    // 版本号
    public $version = '1.0';

    public $config = [
        'cron' => [
            [
                'name' => '生成直播地址',
                'command' => 'live/set-rtmp',
                'desc' => '生成直播推流、拉流地址'
            ]
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
