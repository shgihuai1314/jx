<?php

namespace system\modules\exam\migrations;

use console\models\Migration;

class M180829071343Create_table extends Migration
{
    // 所属模块
    public $module_id = 'exam';

    // 更新说明
    public $description = '考试模块';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        //题库数据表
        $this->createTable('tab_exam_question_category', [
            'id' => $this->primaryKey(11)->notNull()->notNull()->comment('主键'),
            'name' => $this->string()->notNull()->defaultValue('')->comment('题库名称'),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('用户id'),
            'update_time' => $this->integer()->notNull()->defaultValue(0)->comment('最后更新时间'),
            'is_delete'=>$this->integer()->notNull()->defaultValue(0)->comment('是否删除'),
            'is_question_bank'=>$this->integer()->notNull()->defaultValue(0)->comment('是否设为公共题库')
        ]);
        $this->insert('tab_exam_question_category',['name'=>'默认题库']);

        //考试题目表
        $this->createTable('tab_exam_question', [
            'id' => $this->primaryKey(11)->notNull()->comment('主键'),
            'cate_id' => $this->integer()->notNull()->defaultValue(0)->comment('题库id'),
            'title' => $this->text()->comment('试题标题'),
            'question_type' => $this->boolean()->notNull()->defaultValue(0)->comment('试题类别，如：1，单选题，2，多选题，3，判断题 4,填空题 5，问答题'),
            'difficulty' => $this->integer()->notNull()->defaultValue(0)->comment('试题难易度 1，简单 2，一般，3，困难'),
            'result' => $this->string()->notNull()->defaultValue('')->comment('正确答案，多选题的答案为逗号隔开的字符串'),
            'update_time' => $this->integer()->notNull()->defaultValue(0)->comment('更新时间'),
            'is_delete'=>$this->boolean()->notNull()->defaultValue(0)->comment('是否删除 0,未删除；1已删除'),
            'explain' => $this->text()->comment('答案说明'),
            'option_1' => $this->text()->comment('试题选项1'),
            'option_2' => $this->text()->comment('试题选项1'),
            'option_3' => $this->text()->comment('试题选项1'),
            'option_4' => $this->text()->comment('试题选项1'),
            'option_5' => $this->text()->comment('试题选项1'),
            'option_6' => $this->text()->comment('试题选项1'),
            'option_7' => $this->text()->comment('试题选项1'),
            'option_8' => $this->text()->comment('试题选项1'),
        ]);

//        $this->insert('tab_exam_question_category', ['name' => '默认题库']);
        //考试试卷表
        $this->createTable('tab_exam_paper', [
            'id' => $this->primaryKey(11)->notNull()->comment('主键'),
            'paper_name' => $this->string()->notNull()->defaultValue('')->comment('试卷名称'),
            'paper_describle' => $this->text()->comment('试卷说明'),
            'select_question_type' => $this->integer()->notNull()->defaultValue(0)->comment('选题方式，0：完全随机，1：手动选题，2：随机一次'),
            'setting_info' => $this->text()->comment('试卷题目的批量设置'),
            'select_num' => $this->integer()->defaultValue(0)->comment('单选个数'),
            'select_fraction' => $this->integer()->defaultValue(0)->comment('单选分数'),
            'multiple_choice_num' => $this->integer()->defaultValue(0)->comment('多选个数'),
            'multiple_choice_fraction' => $this->integer()->defaultValue(0)->comment('多选分数'),
            'judgment_num' => $this->integer()->defaultValue(0)->comment('判断题个数'),
            'judgment_fraction' => $this->integer()->defaultValue(0)->comment('判断题分数'),
            'papers_num' => $this->integer()->defaultValue(1)->comment('试卷数量'),
            'gap_filling_num' => $this->integer()->defaultValue(0)->comment('填空题个数'),
            'gap_filling_fraction' => $this->integer()->defaultValue(0)->comment('填空题分数数'),
            'essay_question_num' => $this->integer()->defaultValue(0)->comment('问答题个数'),
            'essay_question_fraction' => $this->integer()->defaultValue(0)->comment('问答题分数'),
            'regression' => $this->integer()->defaultValue(0)->comment('漏选分数'),
            'question_type_id' => $this->string()->defaultValue('')->comment('题库类别id'),
            'time_limit' => $this->integer()->defaultValue(0)->comment('考试时间限制，单位分'),
            'score_sum' => $this->integer()->notNull()->defaultValue(0)->comment('试卷总分'),
            'paper_difficulty' => $this->integer()->defaultValue(0)->comment('试卷难易度'),
            'update_time' => $this->integer()->defaultValue(0)->comment('最后更新时间'),
            'update_user' => $this->integer()->defaultValue(0)->comment('更新人'),
            'status' => $this->boolean()->defaultValue(0)->comment('发布状态'),
        ]);

        //考试数据表
        $this->createTable('tab_exam',
            [
                'id' => $this->primaryKey(11),
                'name' => $this->string()->notNull()->defaultValue('')->comment('考试名称'),
                'describe' => $this->text()->comment('说明'),
                'start_time' => $this->integer()->notNull()->defaultValue(0)->comment('开始时间'),
                'end_time' => $this->integer()->notNull()->defaultValue(0)->comment('结束时间'),
                'exam_info' => $this->text()->comment('试题id'),
                'sort' => $this->integer()->notNull()->defaultValue(0)->comment('考试排序'),
                'paper_id' => $this->integer()->notNull()->defaultValue(0)->comment('试卷id'),
                'lesson_id'=>$this->integer()->notNull()->defaultValue(0)->comment('课程id'),
                'exam_number' => $this->integer()->notNull()->defaultValue(0)->comment('考试次数 0 不限制 1 单次'),
                'duration'=>$this->integer()->notNull()->defaultValue(0)->comment('考试时长'),
                'update_time'=>$this->integer()->notNull()->defaultValue(0)->comment('发布时间'),
                'retake_interval' => $this->integer()->notNull()->defaultValue(0)->comment('重复间隔'),
                'exam_score'=>$this->integer()->notNull()->defaultValue(0)->comment('考试分数'),
            ]
        );

        //考试答题表
        $this->createTable('tab_exam_answer', [
            'id' => $this->primaryKey(11)->notNull()->comment('主键'),
            'question_id' => $this->integer()->notNull()->defaultValue(0)->comment('试题id'),
            'question_answer' => $this->string()->notNull()->defaultValue('')->comment('答案'),
            'exam_record_id' => $this->integer()->notNull()->defaultValue(0)->comment('考试id'),
            'remark'=>$this->text()->comment('题目评语'),
            'score'=> $this->integer()->notNull()->defaultValue(0)->comment('题目分数'),
            'remark_time'=>$this->integer()->notNull()->defaultValue(0)->comment('评论时间'),
            'remark_user'=>$this->integer()->notNull()->defaultValue(0)->comment('批阅人')
        ]);
        $this->createIndex('exam_record_id', 'tab_exam_answer', 'exam_record_id');

        //考试记录表
        $this->createTable('tab_exam_record', [
            'id' => $this->primaryKey(11)->notNull()->comment('主键'),
            'exam_id' => $this->integer()->notNull()->defaultValue(0)->comment('考试id'),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('答题用户id'),
            'start_time' => $this->integer()->notNull()->defaultValue(0)->comment('开始时间'),
            'end_time' => $this->integer()->notNull()->defaultValue(0)->comment('结束时间，0：用户没有结束答题'),
            'used_time' => $this->integer()->notNull()->defaultValue(0)->comment('答题耗时'),
            'score' => $this->integer()->notNull()->defaultValue(0)->comment('得分数'),
            'question_id' => $this->text()->comment('试题id串'),
            'status' => $this->boolean()->notNull()->defaultValue(0)->comment('成绩是否有效，有效为0，无效为1'),
            'is_receive' => $this->boolean()->notNull()->defaultValue(0)->comment('是否领取，0为未领取，1为已领取'),
            'is_true' => $this->boolean()->notNull()->defaultValue(0)->comment('是否是正确的学号姓名，0为是，1为不是，默认为0'),
            'remark'=>$this->string()->notNull()->defaultValue('')->comment('评语'),
            'remark_status'=>$this->boolean()->defaultValue(0)->comment('批阅状态'),
            'read_user'=>$this->integer()->defaultValue(0)->comment('批阅人'),
            'answer_duration'=>$this->integer()->notNull()->defaultValue(0)->comment('答题时间'),
        ]);
        $this->createIndex('exam_user', 'tab_exam_record', ['exam_id', 'user_id']);

        //试卷试题分数
        $this->createTable('tab_exam_set_score',[
            'id' => $this->primaryKey(11)->notNull()->comment('主键'),
            'paper_id'=>$this->integer()->notNull()->defaultValue(0)->comment('试卷id'),
            'qustion_id'=>$this->integer()->notNull()->defaultValue(0)->comment('题目id'),
            'question_score'=>$this->integer()->notNull()->defaultValue(0)->comment('题目分数')
        ]);

        $this->createIndex('exam_score', 'tab_exam_set_score', ['paper_id', 'qustion_id']);
    }

    public function down()
    {
        $this->dropTable('tab_exam');
        $this->dropTable('tab_exam_answer');
        $this->dropTable('tab_exam_paper');
        $this->dropTable('tab_exam_question');
        $this->dropTable('tab_exam_question_category');
        $this->dropTable('tab_exam_record');
        $this->dropTable('tab_exam_set_score');
    }
}
