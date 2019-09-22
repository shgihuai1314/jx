<?php

namespace system\modules\payment\migrations;

use console\models\Migration;

class M190315081734Create_table extends Migration
{
    // 所属模块
    public $module_id = 'payment';

    // 更新说明
    public $description = '增加一个平台的退款记录表';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        //支付平台的退款的数据表
        $this->createTable('tab_payment_refund', [
            'id' => $this->primaryKey(11)->notNull()->comment('主键'),
            'reason' => $this->string(255)->notNull()->defaultValue('')->comment('退款原因'),
            'describle'=>$this->text()->comment('退款描述'),
            'refund_money' => $this->integer(11)->notNull()->defaultValue(0)->comment('退款金额'),
            'start_time' => $this->integer(11)->notNull()->defaultValue(0)->comment('退款开始时间'),
            'end_time' => $this->integer(11)->notNull()->defaultValue(0)->comment('退款完成时间'),
            'refund_no'=>$this->char(128)->notNull()->defaultValue('')->comment('第三方的订单号'),
            'trade_no'=>$this->char(128)->notNull()->defaultValue('')->comment('退款成功交易订单号'),
            'out_trade_no'=>$this->char(128)->notNull()->defaultValue('')->comment('退款本地订单号'),
            'buyer_user_id'=>$this->integer(11)->notNull()->defaultValue(0)->comment('购买者id'),
        ]);
    }

    public function down()
    {
        $this->dropTable('tab_payment_refund');
    }
}
