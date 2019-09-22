<?php

namespace system\modules\user\migrations;

use console\models\Migration;

class M181122063452Add_column extends Migration
{
    // 所属模块
    public $module_id = 'user';

    // 更新说明
    public $description = '添加个人简介字段和wx字段';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->addColumn('tab_user', 'wx',
            $this->string()->notNull()->defaultValue('')->comment('微信'));

        $this->addColumn('tab_user', 'personal_profile',
            $this->string()->notNull()->defaultValue('')->comment('个人简介'));
    }

    public function down()
    {

    }
}
