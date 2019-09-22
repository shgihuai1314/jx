<?php

namespace system\modules\user\migrations;

use console\models\Migration;

class M181128100043Add_column extends Migration
{
    // 所属模块
    public $module_id = 'user';

    // 更新说明
    public $description = '增加用户隐私字段';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->addColumn('tab_user_extend', 'extend_course_is_privacy',
            $this->smallInteger()->notNull()->defaultValue(0)->comment('课程隐私'));

        $this->addColumn('tab_user_extend', 'extend_topic_is_privacy',
            $this->smallInteger()->notNull()->defaultValue(0)->comment('话题隐私'));

        $this->addColumn('tab_user_extend', 'extend_note_is_privacy',
            $this->smallInteger()->notNull()->defaultValue(0)->comment('笔记隐私'));

        $this->addColumn('tab_user_extend', 'extend_attention_is_privacy',
            $this->smallInteger()->notNull()->defaultValue(0)->comment('关注隐私'));
    }

    public function down()
    {

    }
}
