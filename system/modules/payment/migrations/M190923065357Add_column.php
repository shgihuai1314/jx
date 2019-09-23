<?php

namespace system\modules\payment\migrations;

use console\models\Migration;

class M190923065357Add_column extends Migration
{
    // 所属模块
    public $module_id = 'payment';

    // 更新说明
    public $description = '新增真实姓名字段';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->addColumn('tab_payment_detail','username',
            $this->char(128)->notNull()->defaultValue('')->comment('用户名'));
    }

    public function down()
    {
        parent::down();
    }
}
