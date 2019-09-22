<?php

namespace system\modules\user\migrations;

use console\models\Migration;

class M181128013929Add_column extends Migration
{
    // 所属模块
    public $module_id = 'user';

    // 更新说明
    public $description = '添加背景图，背景音乐拓展字段';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->addColumn('tab_user_extend', 'extend_bg_img',
            $this->string()->notNull()->defaultValue('')->comment('背景图片'));

        $this->addColumn('tab_user_extend', 'extend_bg_music',
            $this->string()->notNull()->defaultValue('')->comment('背景音乐'));

        $this->addColumn('tab_user_extend', 'extend_update_time',
            $this->integer()->notNull()->defaultValue(0)->comment('更新时间'));
    }

    public function down()
    {

    }
}
