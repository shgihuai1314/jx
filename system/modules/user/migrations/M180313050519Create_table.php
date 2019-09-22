<?php

namespace system\modules\user\migrations;

use console\models\Migration;

class M180313050519Create_table extends Migration
{
    // 所属模块
    public $module_id = 'user';

    // 更新说明
    public $description = '创建用户模块相关表';

    public function up()
    {
        // 用户组织机构
        $this->createTable('tab_user_group', [
            'id' => $this->primaryKey(11)->notNull()->comment('组id'),
            'name' => $this->char(100)->notNull()->defaultValue('')->comment('组织结构名称'),
            'path' => $this->string()->notNull()->defaultValue('' )->comment('结构路径'),
            'pid' => $this->integer()->notNull()->defaultValue(1)->comment('父ID'),
            'manager' => $this->integer()->notNull()->defaultValue(0)->comment('部门负责人'),
            'assistant' => $this->integer()->notNull()->defaultValue(0)->comment('部门助理'),
            'leader' => $this->integer()->notNull()->defaultValue(0)->comment('上级主管领导'),
            'sub_leader' => $this->integer()->notNull()->defaultValue(0)->comment('上级分管主任'),
            'tel' => $this->char(64)->notNull()->defaultValue('')->comment('联系电话'),
            'fax' => $this->char(64)->notNull()->defaultValue('')->comment('传真'),
            'address' => $this->char(100)->notNull()->defaultValue('')->comment('地址'),
            'func' => $this->char(255)->notNull()->defaultValue('')->comment('部门职能'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('排序'),
            'code' => $this->char(32)->notNull()->defaultValue('')->comment('部门代码'),
        ]);

        // 职位信息表
        $this->createTable('tab_user_position', [
            'id' => $this->primaryKey(11)->notNull()->comment('职位id'),
            'name' => $this->char(20)->notNull()->comment('职位名称'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('排序序号'),
            'number' => $this->integer()->notNull()->defaultValue(0)->comment('在职人数'),
        ]);

        // 用户信息表
        $this->createTable('tab_user', [
            'user_id' => $this->primaryKey(11)->notNull()->comment('用户id'),
            'username' => $this->string()->notNull()->defaultValue('')->comment('用户名'),
            'realname' => $this->char(64)->notNull()->defaultValue('')->comment('姓名'),
            'gender' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('性别,0保密;1男;2女'),
            'avatar' => $this->string()->notNull()->defaultValue('')->comment('头像'),
            'auth_key' => $this->char(32)->notNull()->defaultValue('')->comment('auth key'),
            'password_hash' => $this->string()->notNull()->defaultValue('')->comment('密码'),
            'password_reset_token' => $this->string()->defaultValue('')->comment('密码重设token'),
            'access_token' => $this->string()->defaultValue('')->comment('access_token'),
            'phone' => $this->char(32)->notNull()->defaultValue('')->comment('手机号'),
            'qq' => $this->char(32)->notNull()->defaultValue('')->comment('QQ'),
            'email' => $this->string()->notNull()->defaultValue('')->comment('email'),
            'role_id' => $this->smallinteger(6)->notNull()->defaultValue(0)->comment('角色id'),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('状态;0正常，1禁用，2锁定，3删除'),
            'is_admin' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否管理：0非1是管理员'),
            'group_id' => $this->integer()->notNull()->defaultValue(0)->comment('组id'),
            'position_id' => $this->smallinteger(6)->notNull()->defaultValue(0)->comment('职位id'),
            'validation_email' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否验证email：0非1是'),
            'validation_phone' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否验证手机：0非1是'),
            'last_change_password' => $this->integer()->notNull()->defaultValue(0)->comment('最后更新密码的时间'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('排序'),
            'remark' => $this->text()->null()->comment('用户备注'),
        ]);
        $this->createIndex('username', 'tab_user', 'username', true);
        $this->createIndex('phone', 'tab_user', 'phone');
        $this->createIndex('position_id', 'tab_user', 'position_id');
        $this->createIndex('role_id', 'tab_user', 'role_id');
        $this->createIndex('access_token', 'tab_user', 'access_token');

        // 内容权限表
        $this->createTable('tab_content_permission', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('用户id'),
            'create_by' => $this->integer()->notNull()->defaultValue(0)->comment('创建人'),
            'update_by' => $this->integer()->notNull()->defaultValue(0)->comment('修改人'),
            'create_time' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
            'update_time' => $this->integer()->notNull()->defaultValue(0)->comment('修改时间'),
        ]);

        // 用户错误日志
        $this->createTable('tab_user_login_error', [
            'id' => $this->primaryKey(11)->notNull()->comment('流水id'),
            'type' => $this->char(64)->notNull()->comment('类型'),
            'target' => $this->string()->notNull()->comment('目标'),
            'times' => $this->integer()->notNull()->comment('次数'),
            'update_at' => $this->integer()->notNull()->comment('最后更新时间'),
            'total' => $this->integer()->notNull()->comment('错误总数'),
        ]);

        // 访问记录表
        $this->createTable('tab_user_read', [
            'id' => $this->primaryKey(11)->notNull()->comment('访问记录表id'),
            'target_type' => $this->string()->notNull()->defaultValue('')->comment('自定义节点，与后台添加对应即可'),
            'target_id' => $this->integer()->notNull()->defaultValue(0)->comment('节点的唯一标识'),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('访问人员ID'),
            'read_at' => $this->integer()->notNull()->defaultValue(0)->comment('访问时间'),
            'is_read' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('0未读，1已读'),
        ]);

        // 用户扩展信息表
        $this->createTable('tab_user_extend', [
            'user_id' => $this->primaryKey(11)->notNull()->defaultValue(0)->comment('用户id'),
        ]);

        $this->delete('tab_notify_node', ['node_name' => ['check_phone', 'check_email']]);
        $this->batchInsert('tab_notify_node', [
            'node_name',
            'node_info',
            'module',
            'content',
            'send_message',
            'send_email',
            'send_sms',
        ], [
            ['check_phone', '验证手机号', 'user', '验证码${code}，您正在进行身份验证，请勿告诉其他人！', 0, 0, 1],
            ['check_email', '验证Email', 'user', '验证码${code}，您正在验证此Email地址，请勿告诉其他人！', 0, 1, 0],
        ]);
    }

    public function down()
    {
        echo " 核心模块相关不能删除！\n";
        return false;
        // $this->dropTable('tab_user');
        // $this->dropTable('tab_content_permission');
        // $this->dropTable('tab_user_login_error');
        // $this->dropTable('tab_user_read');
        // $this->dropTable('tab_user_extend');
    }
}
