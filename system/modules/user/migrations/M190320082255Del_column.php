<?php

namespace system\modules\user\migrations;

use console\models\Migration;

class M190320082255Del_column extends Migration
{
    // 所属模块
    public $module_id = 'user';

    // 更新说明
    public $description = '删除收藏表无用的字段';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->dropColumn('tab_collection', 'tag');
        $this->dropColumn('tab_collection', 'url');
    }

    public function down()
    {
        parent::down();
    }
}
