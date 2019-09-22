<?php

namespace system\modules\cron\migrations;

use console\models\Migration;

/**
 * Handles the creation of table `tab_cron`.
 */
class M180607070551Create_tab_cron_table extends Migration
{
    // 所属模块
    public $module_id = 'cron';

    // 更新说明
    public $description = '创建计划任务模块相关表';

    // 版本号
    public $version = '1.0';

    /**
     * @return bool|void
     * @throws \yii\db\Exception
     */
    public function up()
    {
        $this->createTable('tab_cron_tasks', [
            'id' => $this->primaryKey(),
            'name' => $this->char(64)->notNull()->defaultValue('')->comment('任务名称'),
            'type' => $this->smallInteger(3)->notNull()->defaultValue(0)->comment('执行方式'),
            'module_id' => $this->string()->notNull()->defaultValue('')->comment('所属模块'),
            'command' => $this->string()->notNull()->defaultValue('')->comment('执行指令'),
            'desc' => $this->string()->notNull()->defaultValue('')->comment('任务说明'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('排序'),
            'create_by' => $this->integer()->notNull()->defaultValue(0)->comment('创建人'),
            'create_time' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
        ]);

        $this->createTable('tab_cron', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull()->defaultValue(0)->comment('任务id'),
            'start_time' => $this->integer()->notNull()->defaultValue(0)->comment('开始时间'),
            'interval_time' => $this->integer()->notNull()->defaultValue(0)->comment('间隔时间'),
            'status' => $this->smallInteger(3)->notNull()->defaultValue(1)->comment('是否启用'),
            'create_by' => $this->integer()->notNull()->defaultValue(0)->comment('创建人'),
            'create_time' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
            'update_time' => $this->integer()->notNull()->defaultValue(0)->comment('更新时间'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('tab_cron');
        $this->dropTable('tab_cron_tasks');
    }
}
