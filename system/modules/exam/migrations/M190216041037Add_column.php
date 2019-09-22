<?php

namespace system\modules\exam\migrations;

use console\models\Migration;

class M190216041037Add_column extends Migration
{
    // 所属模块
    public $module_id = 'exam';

    // 更新说明
    public $description = '添加是否设为公共题库的字段';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->addColumn('tab_exam_question_category', 'is_question_bank',
            $this->integer()->notNull()->defaultValue(0)->comment('是否设为公共题库'));
    }

    public function down()
    {

    }
}
