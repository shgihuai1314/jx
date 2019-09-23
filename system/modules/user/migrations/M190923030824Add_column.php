<?php

namespace system\modules\user\migrations;

use console\models\Migration;

class M190923030824Add_column extends Migration
{
    // 所属模块
    public $module_id = 'user';

    // 更新说明
    public $description = '添加oprnid字段,以及用户的其他字段';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->dropColumn('tab_user_extend', 'extend_bg_img');
        $this->dropColumn('tab_user_extend', 'extend_bg_music');
        $this->dropColumn('tab_user_extend', 'extend_course_is_privacy');
        $this->dropColumn('tab_user_extend', 'extend_topic_is_privacy');
        $this->dropColumn('tab_user_extend', 'extend_note_is_privacy');
        $this->dropColumn('tab_user_extend', 'extend_attention_is_privacy');
        $this->dropColumn('tab_user_extend', 'extend_message_notify');
        $this->dropColumn('tab_user_extend', 'extend_direct_message');


        $this->addColumn('tab_user_extend','extend_openid',
            $this->char(128)->notNull()->defaultValue('')->comment('微信openid'));
        $this->addColumn('tab_user_extend','extend_province',
            $this->char(128)->notNull()->defaultValue('')->comment('身份'));
        $this->addColumn('tab_user_extend','extend_city',
            $this->char(128)->notNull()->defaultValue('')->comment('城市'));
        $this->addColumn('tab_user_extend','extend_country',
            $this->char(128)->notNull()->defaultValue('')->comment('国家'));
        $this->addColumn('tab_user_extend','extend_privilege',
            $this->text()->comment('用户特权信息'));
        $this->addColumn('tab_user_extend','unionid',
            $this->text()->comment('用户特权信息'));
    }

    public function down()
    {
        parent::down();
    }
}
