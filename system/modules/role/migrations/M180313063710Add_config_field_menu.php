<?php

namespace system\modules\role\migrations;

use console\models\Migration;

class M180313063710Add_config_field_menu extends Migration
{
    // 所属模块
    public $module_id = 'role';

    // 更新说明
    public $description = '添加角色表相关配置';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //菜单
        'menu' => [
            'menu_name' => '管理员',
            'module' => 'role',
            'path' => 'role/default/index',
            'pid' => 2,
            'icon' => 'fa fa-user-circle',
            'children' => [
                [
                    'menu_name' => '增加',
                    'path' => 'role/default/add',
                    'type' => 1,
                ],
                [
                    'menu_name' => '编辑',
                    'path' => 'role/default/edit',
                    'type' => 1,
                ],
                [
                    'menu_name' => '删除',
                    'path' => 'role/default/delete',
                    'type' => 1,
                ],
            ]
        ]
    ];

    public function up()
    {
        parent::up();
    }

    public function down()
    {
        $this->printLog(" 核心模块重要配置不能删除！\n");
        return false;
        // parent::down();
    }
}
