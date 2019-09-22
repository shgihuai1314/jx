<?php

namespace system\modules\article\migrations;

use console\models\Migration;

class M180910081050Add_menu extends Migration
{
    // 所属模块
    public $module_id = 'article';

    // 更新说明
    public $description = '卸载文章模块后台菜单';

    // 版本号
    public $version = '1.0';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //菜单
        'menu' => [
            'menu_name' => '文章模块',
            'module' => 'article',
            'path' => 'system/modules/article',
            'pid' => 1,
            'icon' => 'fa fa-list',
            'children' => [
                [
                    'menu_name' => '文章管理',
                    'path' => 'article/content/index',
                    'icon' => 'fa fa-file-text',
                ],
                [
                    'menu_name' => '文章分类',
                    'path' => 'article/category/index',
                    'icon' => 'fa fa-navicon',
                ],
            ]
        ]
    ];

    public function up()
    {

    }

    public function down()
    {
        parent::down();
    }
}
