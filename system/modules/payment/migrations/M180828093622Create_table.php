<?php

namespace system\modules\payment\migrations;

use console\models\Migration;

class M180828093622Create_table extends Migration
{
    // 所属模块
    public $module_id = 'payment';

    // 更新说明
    public $description = '支付';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        //支付配置
        $this->createTable('tab_payment_app', [
            'id' => $this->primaryKey(11)->notNull()->comment('主键'),
            'name' => $this->char(128)->notNull()->defaultValue('')->comment('业务名称'),
            'code' => $this->char(128)->notNull()->defaultValue('')->comment('业务编码'),
            'pay_type' => $this->char(128)->notNull()->defaultValue('')->comment('支付方式'),
            'status' => $this->boolean()->notNull()->defaultValue(1)->comment('是否开启；0禁用1开启'),
            'describle' => $this->string()->notNull()->defaultValue('')->comment('描述'),
            'notify_class' => $this->string()->notNull()->defaultValue('')->comment('异步回调地址'),
            'notify_url' => $this->string()->notNull()->defaultValue('')->comment('同步跳转地址'),
            'alipay_config' => $this->text()->comment('支付宝配置'),
            'wechat_config' => $this->text()->comment('微信配置'),
            'pay_nums' => $this->text()->comment('支付金额设置:25,50,75,100,125,150,200'),
            'secret' => $this->char(128)->notNull()->defaultValue('')->comment('密钥'),
            'app_rand' => $this->string()->defaultValue('')->comment('应用范围'),
        ]);

        //平台订单交易记录
        $this->createTable('tab_payment_detail', [
            'id' => $this->primaryKey(11)->notNull()->comment('主键'),
            'third_order_number' => $this->char(64)->defaultValue('')->comment('第三方订单号'),
            'trade_no' => $this->char(64)->defaultValue('')->comment('平台订单号'),
            'transaction_id' => $this->char(128)->defaultValue('')->comment('支付宝或者微信返回的订单号'),
            'total_fee' => $this->integer(11)->notNull()->defaultValue(0)->comment('支付金额'),
            'pay_type' => $this->char(128)->notNull()->defaultValue('')->comment('支付方式'),
            'pay_status' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('支付状态'),
            'pay_time' => $this->integer(11)->notNull()->defaultValue(0)->comment('支付时间'),
            'pay_user_id' => $this->integer(11)->notNull()->defaultValue(0)->comment('发起支付的用户id'),
            'app_code' => $this->char(128)->notNull()->defaultValue('')->comment('支付标识'),
            'user_id'=>$this->integer()->notNull()->defaultValue(0)->comment('用户id'),
            'order_name'=>$this->char(128)->notNull()->defaultValue('')->comment('业务名称'),
            'data' => $this->text()->comment('支付完成的xml数据'),
            'crate_time' => $this->integer(11)->notNull()->defaultValue(0)->comment('创建时间'),
        ]);
    }

    public function down()
    {
        $this->dropTable('tab_pay_app');
        $this->dropTable('tab_payment_detail');
    }
}
