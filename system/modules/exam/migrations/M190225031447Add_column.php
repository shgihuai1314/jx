<?php

namespace system\modules\exam\migrations;

use console\models\Migration;

class M190225031447Add_column extends Migration
{
    // 所属模块
    public $module_id = 'exam';

    // 更新说明
    public $description = '添加考试记录的答题试时间';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->addColumn('tab_exam_record', 'answer_duration',
            $this->integer()->notNull()->defaultValue(0)->comment('答题时间'));
    }

    public function down()
    {

    }
}
