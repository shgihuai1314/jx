<?php

namespace system\modules\wallet\migrations;

use console\models\Migration;

class M190318025103Create_table extends Migration
{
    // 所属模块
    public $module_id = 'wallet';

    // 更新说明
    public $description = '钱包模块数据表添加';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        //用户钱包
        $this->createTable('tab_user_wallet', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('用户id,0代表系统-1代表运营商'),
            'realname' => $this->string()->notNull()->defaultValue('')->comment('真实姓名去用户表的realname'),
            'user_name' => $this->string()->notNull()->defaultValue('')->comment('账户名'),
            'account_balance' => $this->integer()->notNull()->defaultValue(0)->comment('账户余额'),
            'account_pass' => $this->string()->notNull()->defaultValue('')->comment('账户密码'),
            'bind_type' => $this->smallInteger(1)->defaultValue(0)->comment('绑定类型'),
            'bind_acount' => $this->string()->notNull()->defaultValue('')->comment('绑定账号'),
            'bind_bank' => $this->string()->notNull()->defaultValue('')->comment('绑定银行'),
            'bind_remark' => $this->string()->notNull()->defaultValue('')->comment('绑定备注'),
            'last_update_at' => $this->integer()->notNull()->defaultValue(0)->comment('最后更新时间')
        ]);

        //添加唯一索引
       // $this->createIndex('user','tab_user_wallet','user_id',true);
        $this->insert('tab_user_wallet',['user_id'=>0,'realname'=>'平台','user_name'=>'pingtai','bind_type'=>1,'bind_acount'=>1121917625]);
        $this->insert('tab_user_wallet',['user_id'=>-1,'realname'=>'运营','user_name'=>'pingtai','bind_type'=>1,'bind_acount'=>8888]);

        //钱包流水
        $this->createTable('tab_user_wallet_details', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('用户id'),
            'amount' => $this->integer()->notNull()->comment('金额，以分为单位,带符号'),
            'after_account_balance' => $this->integer()->notNull()->defaultValue(0)->comment('操作后账户余额'),
            'type' => $this->string()->notNull()->defaultValue(0)->comment('收到新订单:newOrder,订单结算:orderCheck,订单退款:orderRefund,提现:withdrawals'),
            'description' => $this->text()->comment('流水详细描述'),
            'create_by' => $this->integer()->notNull()->defaultValue(0)->comment('创建人'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
            'target_user_id'=>$this->integer()->notNull()->defaultValue(0)->comment('关联的用户id'),
            'target_id'=>$this->integer()->notNull()->defaultValue(0)->comment('关联的编号'),
            'plan_id'=>$this->integer()->notNull()->defaultValue(11)->comment('计划id'),
        ]);

        //创建索引
        $this->createIndex('user','tab_user_wallet_details','user_id');

        //提现记录详情
        $this->createTable('tab_user_withdrawals_details', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('用户id'),
            'realname' => $this->string()->notNull()->defaultValue('')->comment('姓名'),
            'account' => $this->string()->notNull()->defaultValue('')->comment('提现到的账户'),
            'remark' => $this->string()->notNull()->defaultValue('')->comment('提现备注'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
            'create_by' => $this->integer()->notNull()->defaultValue(0)->comment('创建人'),
            'state' => $this->smallInteger(1)->defaultValue(0)->comment('提现状态'),
            'auditor' => $this->integer()->notNull()->defaultValue(0)->comment('审核人id'),
            'auditor_at' => $this->integer()->notNull()->defaultValue(0)->comment('审核时间'),
            'auditor_remark' => $this->string()->notNull()->defaultValue('')->comment('审核备注'),
            'amount' => $this->integer()->notNull()->defaultValue(11)->comment('提现金额')
        ]);

        //订单状态变动记录
        $this->createTable('tab_order_logs', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull()->defaultValue(0)->comment('订单id'),
            'content' => $this->text()->comment('日志内容'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
            'create_by' => $this->integer()->notNull()->defaultValue(0)->comment('创建人'),
            'create_ip' => $this->char(128)->notNull()->defaultValue('')->comment('创建ip'),
        ]);
    }


    public function down()
    {
        $this->dropTable('tab_user_wallet');
        $this->dropTable('tab_order_logs');
        $this->dropTable('tab_user_withdrawals_details');
        $this->dropTable('tab_user_wallet_details');
    }
}
