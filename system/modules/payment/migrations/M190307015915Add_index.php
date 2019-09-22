<?php

namespace system\modules\payment\migrations;

use console\models\Migration;

class M190307015915Add_index extends Migration
{
    // 所属模块
    public $module_id = 'payment';

    // 更新说明
    public $description = '添加订单表的索引';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->createIndex('third_order_number','tab_payment_detail',['third_order_number']);
        $this->createIndex('trade_no','tab_payment_detail',['trade_no']);
        $this->createIndex('transaction_id','tab_payment_detail',['transaction_id']);
        $this->createIndex('userCode','tab_payment_detail',['user_id','app_code']);
    }

    public function down()
    {

    }
}
