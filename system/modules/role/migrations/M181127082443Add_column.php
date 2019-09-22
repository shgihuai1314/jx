<?php

namespace system\modules\role\migrations;

use console\models\Migration;

class M181127082443Add_column extends Migration
{
    // 所属模块
    public $module_id = 'role';

    // 更新说明
    public $description = '新增角色编码，是否默认字段';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->addColumn('tab_auth_role', 'code',
            $this->char(64)->notNull()->defaultValue('')->comment('角色编码'));
        $this->addColumn('tab_auth_role', 'is_init',
            $this->boolean()->notNull()->defaultValue(0)->comment('初始化角色，0：否；1是'));
        $this->addColumn('tab_auth_role', 'is_default',
            $this->boolean()->notNull()->defaultValue(0)->comment('默认角色,0：否；1：是'));

        $this->update('tab_auth_role', ['code' => 'ROLE_ADMIN', 'is_init' => 1], ['role_id' => 1]);

        $this->batchInsert('tab_auth_role', ['name', 'description', 'permission', 'code', 'is_init', 'is_default'],
            [
                ['教学', '拥有发布资源权限的角色', '', 'ROLE_TEACHER', 1, 1],
                ['学员', '前台能够加入课程学习角色', '', 'ROLE_STUDENT', 1, 1],
                ['站点管理员', '前台站点管理者', '', 'ROLE_SITE_MANAGER', 1, 1],
            ]);
    }

    public function down()
    {
        parent::down();
    }
}
