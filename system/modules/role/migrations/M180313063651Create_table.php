<?php

namespace system\modules\role\migrations;

use console\models\Migration;

class M180313063651Create_table extends Migration
{
    // 所属模块
    public $module_id = 'role';

    // 更新说明
    public $description = '创建角色模块相关表';

    public function up()
    {
        // 角色赋予表
        $this->createTable('tab_auth_assign', [
            'role_id' => $this->integer()->notNull()->defaultValue(0)->comment('角色id'),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('用户id'),
        ]);
        $this->addPrimaryKey('role_user_id', 'tab_auth_assign', ['role_id', 'user_id']);

        $this->delete('tab_auth_assign', [
            'role_id' => 1,
            'user_id' => 1,
        ]);
        $this->insert('tab_auth_assign', [
            'role_id' => 1,
            'user_id' => 1,
        ]);

        $this->createTable('tab_auth_role', [
            'role_id' => $this->primaryKey()->notNull()->comment('角色id'),
            'name' => $this->char(32)->notNull()->defaultValue('')->comment('角色名称'),
            'description' => $this->string()->notNull()->defaultValue('')->comment('描述'),
            'permission' => $this->text()->comment('权限'),
        ]);

        $this->createIndex('name', 'tab_auth_role', 'name');

        $this->delete('tab_auth_role', ['role_id' => 1]);
        $this->insert('tab_auth_role', [
            'role_id' => '1',
            'name' => '超级管理角色',
            'description' => '在系统中不限制权限，所以在分配此角色时请斟酌分配；',
        ]);
    }

    public function down()
    {
        echo " 核心模块相关表不能删除！\n";
        return false;
        // $this->dropTable('tab_auth_assign');
        // $this->dropTable('tab_auth_role');
    }
}
