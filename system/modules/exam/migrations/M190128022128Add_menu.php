<?php

namespace system\modules\exam\migrations;

use console\models\Migration;

class M190128022128Add_menu extends Migration
{
    // 所属模块
    public $module_id = 'exam';

    // 更新说明
    public $description = '添加题库菜单';

    // 版本号
    public $version = '1.0';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //菜单
        'menu' => [
            'menu_name' => '答题系统',
            'module' => 'exam',
            'path' => 'system/modules/exam',
            'pid' => 4,
            'icon' => 'fa fa-check-square-o',
            'children' => [
                [
                    'menu_name' => '题库管理',
                    'path' => 'exam/question-bank/index',
                    'icon' => 'fa fa-database',
                    'children' => [
                        [
                            'menu_name' => '添加',
                            'path' => 'exam/question-bank/add',
                            'type' => 1,
                        ],
                        [
                            'menu_name' => '修改',
                            'path' => 'exam/question-bank/edit',
                            'type' => 1,
                        ],
                        [
                            'menu_name' => '删除',
                            'path' => 'exam/question-bank/delete',
                            'type' => 1,
                        ],
                    ]
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
        parent::down();
    }
}
