<?php

namespace system\modules\notify\migrations;

use console\models\Migration;

class M180313061424Create_table extends Migration
{
    // 所属模块
    public $module_id = 'notify';

    // 更新说明
    public $description = '创建消息模块相关表';

    public function up()
    {
        // 验证码表
        $this->createTable('tab_notify_code', [
            'id' => $this->primaryKey(11)->notNull()->comment('自增id'),
            'node' => $this->char(64)->notNull()->defaultValue('')->comment('消息节点，可以自定义；比如register等'),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('用户id'),
            'send_type' => $this->char(64)->notNull()->defaultValue('')->comment('发送类型：sms，email'),
            'target' => $this->string()->notNull()->defaultValue('')->comment('发送目标，可以是手机号码，email等'),
            'code' => $this->char(64)->notNull()->defaultValue('')->comment('验证码'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
            'is_verify' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否已验证；0没有；1已用'),
        ]);

        // 提醒消息表
        $this->createTable('tab_notify_message', [
            'message_id' => $this->primaryKey(11)->notNull()->comment('消息id'),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('消息接收用户id'),
            'node_name' => $this->string()->notNull()->defaultValue('')->comment('节点名称'),
            'module' => $this->char(64)->notNull()->defaultValue('')->comment('模块名称'),
            'content' => $this->text()->defaultValue(NULL)->comment('消息内容'),
            'sender_id' => $this->integer()->notNull()->defaultValue(0)->comment('发送人id'),
            'created_at' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
            'is_read' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否已读；0未读，1已读'),
            'read_at' => $this->integer()->notNull()->defaultValue(0)->comment('读取时间'),
            'url' => $this->string()->notNull()->defaultValue('')->comment('消息的url'),
            'is_delete' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否删除；0未删除，1删除'),
        ]);

        // 提醒消息队列表
        $this->createTable('tab_notify_message_queue', [
            'queue_id' => $this->primaryKey(11)->notNull()->comment('消息队列id'),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('用户id'),
            'node_name' => $this->string()->notNull()->defaultValue('')->comment('节点名称'),
            'data' => $this->text()->notNull()->comment('消息参数'),
            'created_at' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
            'state' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('0未处理；1处理中；2已处理'),
        ]);

        // 提醒节点表
        $this->createTable('tab_notify_node', [
            'node_id' => $this->primaryKey(11)->notNull()->comment('节点id'),
            'node_name' => $this->string()->notNull()->defaultValue('')->comment('节点名称'),
            'node_info' => $this->string()->notNull()->defaultValue('')->comment('节点信息'),
            'module' => $this->char(64)->notNull()->defaultValue('')->comment('模块id'),
            'content' => $this->text()->comment('消息默认模板'),
            'send_message' => $this->smallInteger(1)->notNull()->defaultValue(1)->comment('站内信'),
            'send_email' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('发送邮件'),
            'send_sms' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('发送短信'),
            'send_app' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('app发送'),
            'send_qywx' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('企业微信'),
        ]);
    }

    public function down()
    {
        echo " 核心模块表不能删除！\n";
        return false;
        // 删除表
        // $this->dropTable('tab_notify_code');
        // $this->dropTable('tab_notify_message');
        // $this->dropTable('tab_notify_message_queue');
        // $this->dropTable('tab_notify_node');
    }
}
