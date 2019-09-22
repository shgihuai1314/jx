<?php

namespace system\modules\main\migrations;

use yii;
use console\models\Migration;

class M190218031019Main extends Migration
{
    // 所属模块
    public $module_id = 'main';

    // 更新说明
    public $description = '将pdf转化为图片';

    // 版本号
    public $version = '1.0';

    public $config = [
        //配置
        'systemConfig' => [
             //全局
             [
                'name' => 'PDF_IMG_CMD',
                'title' => 'IMG命令',
                'value' => '/usr/bin/convert',
                'remark' => 'pdfToImg命令',
             ],
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
