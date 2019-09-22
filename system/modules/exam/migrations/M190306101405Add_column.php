<?php

namespace system\modules\exam\migrations;

use console\models\Migration;

class M190306101405Add_column extends Migration
{
    // 所属模块
    public $module_id = 'exam';

    // 更新说明
    public $description = '添加考试合格分数和答题记录的答案是否正确字段,以及当前模块需要的索引';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->addColumn('tab_exam', 'exam_pass_mark', $this->integer()->notNull()->defaultValue(0)->comment('考试及格分數'));
        $this->addColumn('tab_exam_answer', 'is_correct', $this->integer()->notNull()->defaultValue(0)->comment('答案是否正确'));
        $this->createIndex('examAswer', 'tab_exam_answer', ['question_id', 'question_answer', 'exam_record_id']);
        $this->createIndex('examSetScore', 'exam_set_score', ['paper_id', 'qustion_id']);
        $this->createIndex('cate', 'tab_exam_question', ['cate_id']);
    }

    public function down()
    {

    }
}
