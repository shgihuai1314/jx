<?php

namespace system\modules\cron\migrations;

use console\models\Migration;

class M180814031404Create_tab_flow_cron_table extends Migration
{
    // 所属模块
    public $module_id = 'cron';

    // 更新说明
    public $description = '添加系统监控相关表';

    // 版本号
    public $version = '2.0';

    public function up()
    {
        $this->createTable('tab_flow', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'up_side' => $this->text()->comment('上行流量'),
            'down_side' => $this->text()->comment('下行流量'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('添加时间'),
        ]);
    
        $this->createTable('tab_real_time_flow', [
            'id' => $this->primaryKey(11)->notNull()->comment('实时流量id'),
            'up_side' => $this->string()->notNull()->defaultValue(0)->comment('上行流量'),
            'down_side' => $this->string()->notNull()->defaultValue(0)->comment('下行流量'),
            'net_out_speed' => $this->string()->notNull()->defaultValue(0)->comment('总接收量'),
            'net_input_speed' => $this->string()->notNull()->defaultValue(0)->comment('总发送量'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('添加时间'),
        ]);
    
        $this->createTable('tab_real_time_cpu', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'cpu_usage' => $this->string()->comment('cpu负载率'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('添加时间'),
        ]);
        $this->createTable('tab_real_time_load', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'load_usage' => $this->string()->comment('系统负载率'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('添加时间'),
        ]);
        $this->createTable('tab_real_time_memory', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'memory_usage' => $this->string()->comment('运行内存'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('添加时间'),
        ]);
    
        $this->createTable('tab_cpu_record', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'cpu' => $this->text()->comment('cpu负载率'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('添加时间'),
        ]);
        $this->createTable('tab_load_record', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'load' => $this->text()->comment('系统负载率'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('添加时间'),
        ]);
        $this->createTable('tab_memory_record', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'memory' => $this->text()->comment('运行内存'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('添加时间'),
        ]);
    }

    public function down()
    {
        $this->dropTable('tab_flow');
        $this->dropTable('tab_real_time_flow');
        $this->dropTable('tab_real_time_cpu');
        $this->dropTable('tab_real_time_load');
        $this->dropTable('tab_real_time_memory');
    }
}
