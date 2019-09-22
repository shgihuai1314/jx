<?php

namespace system\modules\notify\migrations;

use console\models\Migration;

class M190122063728Create_table_tab_direct_message extends Migration
{
    // 所属模块
    public $module_id = 'notify';

    // 更新说明
    public $description = '添加私信表tab_direct_message';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->createTable('tab_direct_message',[
            'id' => $this->primaryKey(),
            'from_user' => $this->integer()->notNull()->defaultValue(0)->comment('发送人'),
            'to_user' => $this->integer()->notNull()->defaultValue(0)->comment('接收人'),
            'content' => $this->text()->comment('内容'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('发送时间'),
            'is_read' => $this->boolean()->notNull()->defaultValue(0)->comment('是否已读:0否;1是'),
            'from_delete' => $this->boolean()->notNull()->defaultValue(0)->comment('发送人删除状态'),
            'to_delete' => $this->boolean()->notNull()->defaultValue(0)->comment('接收人删除状态')
        ]);

        $this->createIndex('from_user', 'tab_direct_message', 'from_user');
        $this->createIndex('to_user', 'tab_direct_message', 'to_user');

        $this->createTable('tab_direct_relation', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('用户ID'),
            'chat_user' => $this->integer()->notNull()->defaultValue(0)->comment('聊天对象'),
            'latest_message' => $this->integer()->notNull()->defaultValue(0)->comment('最新id'),
            'update_at' => $this->integer()->notNull()->defaultValue(0)->comment('更新时间')
        ]);

        $this->createIndex('user_id', 'tab_direct_relation', 'user_id');
        $this->createIndex('chat_user', 'tab_direct_relation', 'chat_user');
        $this->createIndex('latest_message', 'tab_direct_relation', 'latest_message');
    }

    public function down()
    {
        $this->dropTable('tab_direct_message');
        $this->dropTable('tab_direct_relation');
    }
}
