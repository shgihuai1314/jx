<?php

namespace system\modules\user\migrations;

use console\models\Migration;

class M181225032155Add_extend_column extends Migration
{
    // 所属模块
    public $module_id = 'user';

    // 更新说明
    public $description = '增加扩展字段消息配置';

    // 版本号
    public $version = '1.0';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //拓展字段
        'extendsField'=>[
            [
                'table_name' => 'tab_user_extend',
                'field_name' => 'extend_message_notify',
                'field_title' => '消息配置',
                'field_type' => 'string',
                'show_type' => 'text',
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
