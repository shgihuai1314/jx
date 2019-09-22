<?php

namespace system\modules\main\migrations;

use console\models\Migration;

class M190311102941Add_pdf_config extends Migration
{
    // 所属模块
    public $module_id = 'main';

    // 更新说明
    public $description = '是否播放pdf';

    // 版本号
    public $version = '1.0';

    public $config = [
        'systemConfig' => [
            [
                'name' => 'IS_PLAY_PDF',
                'title' => '是否播放pdf',
                'type' => 'number',
                'value' => '0',
                'remark' => '0：否，1：是',
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
