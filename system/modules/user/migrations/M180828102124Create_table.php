<?php

namespace system\modules\user\migrations;

use console\models\Migration;

class M180828102124Create_table extends Migration
{
    // 所属模块
    public $module_id = 'user';

    // 更新说明
    public $description = '创建用户模块班级、收藏、关注相关表';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->createTable('tab_class_member', [
            'id' => $this->primaryKey(),
            'class_id' => $this->integer()->notNull()->defaultValue(0)->comment('班级ID'),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('学员ID'),
            'join_time' => $this->integer()->notNull()->defaultValue(0)->comment('进班时间'),
            'position' => $this->string()->notNull()->defaultValue('')->comment('班级职位'),
        ]);

        $this->createIndex('class_id', 'tab_user_class_member', ['class_id', 'user_id']);

        $this->createTable('tab_class_detail', [
            'id' => $this->primaryKey(),
            'name' => $this->char(64)->notNull()->defaultValue('')->comment('班级名称'),
            'headmaster' => $this->char(64)->notNull()->defaultValue('')->comment('班主任'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('开班时间'),
        ]);

        $this->createTable('tab_collection', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('收藏人ID'),
            'target_type' => $this->string()->notNull()->defaultValue('')->comment('收藏目标的类型'),
            'target_id' => $this->integer()->notNull()->defaultValue(0)->comment('收藏目标的ID'),
            'tag' => $this->string()->notNull()->defaultValue('')->comment('标签字符串，用,隔开'),
            'url' => $this->string()->notNull()->defaultValue('')->comment('内容的链接地址'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
        ]);

        $this->createIndex('target_id', 'tab_user_collection', ['target_id', 'target_type']);

        $this->createTable('tab_attention', [
            'id' => $this->primaryKey(),
            'follow_id' => $this->integer()->notNull()->defaultValue(0)->comment('被关注人'),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('关注人'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('关注时间'),
        ]);

        $this->createIndex('follow_id', 'tab_user_attention', ['follow_id', 'user_id']);
    }

    public function down()
    {
        $this->dropTable('tab_class_member');
        $this->dropTable('tab_class_detail');
        $this->dropTable('tab_collection');
        $this->dropTable('tab_attention');
    }
}
