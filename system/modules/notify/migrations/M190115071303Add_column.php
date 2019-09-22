<?php

namespace system\modules\notify\migrations;

use console\models\Migration;

class M190115071303Add_column extends Migration
{
    // 所属模块
    public $module_id = 'notify';

    // 更新说明
    public $description = '增加消息表路由标识和参数字段';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->dropColumn('tab_notify_message', 'url');

        $this->addColumn('tab_notify_message', 'code',
            $this->string()->notNull()->defaultValue('')->comment('路由标识'));
        $this->addColumn('tab_notify_message', 'params',
            $this->string()->notNull()->defaultValue('')->comment('路由参数'));
    }

    public function down()
    {
        parent::down();
    }
}
