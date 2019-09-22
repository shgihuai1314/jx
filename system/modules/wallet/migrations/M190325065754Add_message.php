<?php

namespace system\modules\wallet\migrations;

use console\models\Migration;

class M190325065754Add_message extends Migration
{
    // 所属模块
    public $module_id = 'wallet';

    // 更新说明
    public $description = '添加钱包账户和消息提醒';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->insert('tab_user_wallet',['user_id'=>0,'realname'=>'平台','user_name'=>'pingtai','bind_type'=>1,'bind_acount'=>1121917625]);
        $this->insert('tab_user_wallet',['user_id'=>-1,'realname'=>'运营商','user_name'=>'运营','bind_type'=>1,'bind_acount'=>15271630257]);
        $this->insert('tab_notify_node',['node_name'=>'verify_code','node_info'=>'绑定账户','module'=>'wallet','content'=>'SMS_89590054','send_sms'=>1]);
    }

    public function down()
    {

    }
}
