<?php

namespace system\modules\exam\models;

use Symfony\Component\Console\Question\Question;
use system\modules\user\models\User;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_exam_paper".
 *
 * @property integer $id
 * @property string $paper_name
 * @property integer $select_question_type
 * @property string $setting_info
 * @property integer $select_num
 * @property integer $select_fraction
 * @property integer $multiple_choice_num
 * @property integer $multiple_choice_fraction
 * @property integer $judgment_num
 * @property integer $judgment_fraction
 * @property integer $gap_filling_num'
 * @property integer $gap_filling_fraction
 * @property integer $essay_question_num
 * @property integer $essay_question_fraction
 * @property integer $regression
 * @property integer $papers_num
 * @property string $question_type_id
 * @property integer $score_sum
 * @property integer $update_time
 * @property integer $status
 * @property integer $update_user
 * @property integer $paper_difficulty
 */
class ExamPaper extends \system\models\Model
{

    public $customData = '';
    //是否记录日志
    public $log_flag = true;
    //日志信息配置
    public $log_options = [
        'target_name' => 'id',//日志目标对应的字段名，默认name
        'model_name' => 'tab_exam_paper',//模型名称
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_exam_paper';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['update_user', 'select_question_type', 'select_num', 'select_fraction', 'multiple_choice_num', 'multiple_choice_fraction', 'judgment_num', 'judgment_fraction', 'papers_num', 'score_sum'], 'integer'],
            [['paper_name', 'setting_info', 'paper_describle'], 'string'],
            [['question_type_id', 'gap_filling_num', 'gap_filling_fraction', 'essay_question_num', 'essay_question_fraction', 'regression', 'update_time', 'status', 'customData', 'paper_difficulty'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '主键',
            'paper_name' => '试卷名称',
            'paper_describle' => '试卷描述',
            'select_question_type' => '选题方式，0：完全随机，1：手动选题，2：随机一次',
            'setting_info' => '试卷题目的批量设置',
            'select_num' => '单选个数',
            'select_fraction' => '单选分数',
            'multiple_choice_num' => '多选个数',
            'multiple_choice_fraction' => '多选分数',
            'judgment_num' => '判断题个数',
            'judgment_fraction' => '判断题分数',
            'gap_filling_num' => '填空题个数',
            'gap_filling_fraction' => '填空题分数',
            'essay_question_num' => '问答题个数',
            'essay_question_fraction' => '问答题分数',
            'regression' => '漏选题分数',
            'papers_num' => '试卷数量',
            'question_type_id' => '题库类别id',
            'score_sum' => '总分',
            'status' => '发布状态',
            'update_time' => '更新时间',
            'update_user' => '更新人',
            'paper_difficulty' => '试卷难易度',
            'is_delete'=>'是否删除'
        ], parent::attributeLabels());
    }

    const CACHE_PAPER_KEY = 'cache:paper:id:';

    /**
     * 根据id获取试卷
     * @param $paper_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getOne($paper_id)
    {
        $cacheKey = self::CACHE_PAPER_KEY . $paper_id;
        if ($cache = Yii::$app->cache->get($cacheKey)) {
            return $cache;
        }

        $data = self::find()->where(['id' => $paper_id])->asArray()->one();
        Yii::$app->cache->set($cacheKey, $data, 30 * 24 * 3600);

        return $data;
    }


    /**
     * 试卷数据id=>试卷名称的数据
     * @return array|bool
     */
    public static function getAllPaperData()
    {
        $data = self::find()->where(['status' => 1])->all();
        if (!$data) {
            return false;
        }

        return ArrayHelper::map($data, 'id', 'paper_name');
    }

    /**
     * @return bool
     */
    /*public function afterFind()
    {
        parent::afterFind();
        if ($this->question_type_id != null || $this->question_type_id != '') {
            $this->question_type_id = explode(',', $this->question_type_id);
        }
        return true;
    }*/
    /**
     * 查询关联数据
     * @return object
     */
    public function getPaper()
    {
        return $this->hasMany(ExamSetScore::className(), ['paper_id' => 'id'])->with('detail');
    }

    /**
     * @param bool $insert
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            //计算总分
            if($insert || $this->select_question_type==0){
                $this->score_sum =
                    intval($this->select_num) * intval($this->select_fraction) +
                    intval($this->multiple_choice_num) * intval($this->multiple_choice_fraction) +
                    intval($this->judgment_num) * intval($this->judgment_fraction) +
                    intval($this->gap_filling_num) * intval($this->gap_filling_fraction) +
                    intval($this->essay_question_num) * intval($this->essay_question_fraction);
            }


            /* //手动选题的题目串
             if ($this->setting_info != null && is_array($this->setting_info)) {
                 $this->setting_info = implode(',', $this->setting_info);
             }*/
            //手动选题，添加试题
            if ($this->customData) {
//                print_r($this->customData);die;
                $data = [];
                $data['select'] = [];
                foreach ($this->customData as $k => $v) {
                    //各种题型分类
                    if ($v['type'] == 1) {
                        $data['select'][] = $v['id'];
                    } elseif ($v['type'] == 2) {
                        $data['multiple_choice'][] = $v['id'];
                    } elseif ($v['type'] == 3) {
                        $data['judgment'][] = $v['id'];
                    } elseif ($v['type'] == 4) {
                        $data['gap_filling'][] = $v['id'];
                    } else {
                        $data['essay_question'][] = $v['id'];
                    }
                    //所有的题目
                    $data['setting_info'][] = $v['id'];
                }
                //计算分数
                $this->score_sum = array_sum(ArrayHelper::getColumn($this->customData, 'Score'));

                //单选题个数
                $this->select_num = isset($data['select']) ? count($data['select']) : '';
                $this->select_fraction = '';
                //多选题个数
                $this->multiple_choice_num = isset($data['multiple_choice']) ? count($data['multiple_choice']) : '';
                $this->multiple_choice_fraction = '';
                //判断个数
                $this->judgment_num = isset($data['judgment']) ? count(($data['judgment'])) : '';
                $this->judgment_fraction = '';
                //填空题个数
                $this->gap_filling_num = isset($data['gap_filling']) ? count($data['gap_filling']) : '';
                $this->gap_filling_fraction = '';
                //问答题个数
                $this->essay_question_num = isset($data['essay_question']) ? count($data['essay_question']) : '';
                $this->essay_question_fraction = '';
                //题目
                $this->setting_info = implode(",", array_unique($data['setting_info']));
            }

            //如果选题方式是随机一次
            if ($this->select_question_type == 2 && !$this->customData) {
//               print(Exam::toString(Exam::randQuestion($this)));die;
               // echo 454534;die;
                if($insert){
                    $this->setting_info = implode(',', Exam::toString(Exam::randQuestion($this)));
                }
            }
            /* //如果是字符串判断去除重复的值
             if (is_string($this->setting_info)) {
                 $this->setting_info = implode(',', array_unique(explode(',', $this->setting_info)));
             }*/

            //多个题库
            if ($this->question_type_id != null && is_array($this->question_type_id)) {
                $this->question_type_id = implode(',', $this->question_type_id);
            }

            $this->update_time = time();
            $this->update_user = Yii::$app->User->id;

            return true;
        }
        return false;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        //如果是新增
        if ($insert) {
            //手动选题
            if ($this->select_question_type == 1) {
                foreach ($this->customData as $k => $v) {
                    //添加数据到分数数据表
                    $model = new ExamSetScore();
                    $model->paper_id = $this->id;
                    $model->qustion_id = $v['id'];
                    $model->question_score = isset($v['Score']) ? $v['Score'] : '';
                    $model->save();
                }
            }

            //如果选题方式是随机一次
            if ($this->select_question_type == 2) {
                foreach (explode(',', $this->setting_info) as $k => $v) {
                    $qiestionType = ExamQuestion::getQuestionDetail($v)->question_type;
                    if ($qiestionType == 1) {
                        $score = $this->select_fraction;
                    } elseif ($qiestionType == 2) {
                        $score = $this->multiple_choice_fraction;
                    } elseif ($qiestionType == 3) {
                        $score = $this->judgment_fraction;
                    } elseif ($qiestionType == 4) {
                        $score = $this->gap_filling_fraction;
                    } else {
                        $score = $this->essay_question_fraction;
                    }

                    $model = new ExamSetScore();
                    $model->paper_id = $this->id;
                    $model->qustion_id = $v;
                    $model->question_score = $score;
                    $model->save();
                }
            }
        } else {
            //去除脏数据，保持试卷表的题目串和题目分数表的数据一致
            $diff = '';
            if ($this->customData) {
                $data = ArrayHelper::getColumn(ExamSetScore::getAllData($this->id), 'qustion_id');
                $data1 = ArrayHelper::getColumn($this->customData, 'id');
                $diff = array_diff($data, $data1);
                if ($diff) {
                    ExamSetScore::deleteAll(['qustion_id' => $diff, 'paper_id' => $this->id]);
                }
                //新增修改数据
                foreach ($this->customData as $key => $val) {
//                    print_r($this->customData );die;
                    $result = ExamSetScore::getPaperQuestionDetail(false, $this->id, $val['id']);
//                    print_r($result);die;
                    //如果不存在新增
                    if (!$result && !isset($result->question_score)) {
//                        echo $val['id']."\n";
                        $model = new ExamSetScore();
                        $model->paper_id = $this->id;
                        $model->qustion_id = $val['id'];
                        $model->question_score = isset($val['Score']) ? $val['Score'] : '';
                        $model->save();

                    } else {
                        //如果存在，分数不相等就修改
                        if (isset($val['Score'])) {
                            if ($result->question_score !== $val['Score']) {
                                $result->question_score = isset($val['Score']) ? $val['Score'] : '';
                                $result->save();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 删除关联数据
     * @return bool
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            ExamSetScore::BatchDel(['paper_id' => $this->id]);
            return true;
        }
        return false;
    }


    /**
     * 获取预览试卷和考试题目，分数信息
     * @param $parper_id
     */
    public static function getPaperMsg($parper_id, $question_id)
    {
        $data = ExamPaper::find()
            ->where(['id' => $parper_id])
            ->with('paper')
            ->asArray()
            ->one();

        if (!$data) {
            return false;
        }

        $questionData = [];//试卷信息
        $questionData['paper_name'] = $data['paper_name'];//试卷名称
        $questionData['paper_describle'] = $data['paper_describle'];//试卷描述
        $questionData['update_time'] = date('Y-m-d H:i:s', $data['update_time']);//更新时间
        $questionData['update_user'] = User::getNameById($data['update_user']);//更新新人

        //完全随机
        if ($data['select_question_type'] == 0 && $question_id) {
            $ids=is_array($question_id)?$question_id:explode(',',$question_id);
            $result = ExamQuestion::find()->where(['id' => $ids])->asArray()->all();
            if ($result) {
                foreach ($result as $k => $v) {
                    if ($v['question_type'] == 1) {
                        $v['score']=$data['select_fraction'];
                        $questionData['select'][] = $v;
                    } elseif ($v['question_type'] == 2) {
                        $v['score']=$data['multiple_choice_fraction'];
                        $questionData['multiple'][] = $v;
                    } elseif ($v['question_type'] == 3) {
                        $v['score']=$data['judgment_fraction'];
                        $questionData['judgment'][] = $v;
                    } elseif ($v['question_type'] == 4) {
                        $v['score']=$data['gap_filling_fraction'];
                        $questionData['gap'][] = $v;
                    } else {
                        $v['score']=$data['essay_question_fraction'];
                        $questionData['essay'][] = $v;
                    }
                }
            }
            //单选题总分数
            $questionData['selectScore'] = $data['select_num'] * $data['select_fraction'];
            //判断题分数
            $questionData['judgmentScore'] = $data['multiple_choice_num'] * $data['multiple_choice_fraction'];
            //多选题总分数
            $questionData['multipleScore'] = $data['judgment_num'] * $data['judgment_fraction'];
            //填空题总分数
            $questionData['gapScore'] = $data['gap_filling_num'] * $data['gap_filling_fraction'];
            //问答题总分数
            $questionData['essayScore'] = $data['essay_question_num'] * $data['essay_question_fraction'];
        } else {
            //题型分类
            foreach ($data['paper'] as $k => $v) {
                //题目加入分数
                $v['detail']['score'] = $v['question_score'];

                if ($v['detail']['question_type'] == 1) {
                    //单选题
                    $questionData['select'][] = $v['detail'];

                } elseif ($v['detail']['question_type'] == 2) {
                    //多选题
                    $questionData['multiple'][] = $v['detail'];

                }elseif ($v['detail']['question_type'] == 3){
                    $questionData['judgment'][] = $v['detail'];
                } elseif ($v['detail']['question_type'] == 4) {
                    //填空题
                    $questionData['gap'][] = $v['detail'];
                } else {
                    //问答题
                    $questionData['essay'][] = $v['detail'];
                }
            }
            //单选题总分数
            $questionData['selectScore'] = isset($questionData['select']) ? array_sum(ArrayHelper::getColumn($questionData['select'], 'score')) : '';
            //判断题总分数
            $questionData['judgmentScore'] = isset($questionData['judgment']) ? array_sum(ArrayHelper::getColumn($questionData['judgment'], 'score')) : '';
            //多选题总分数
            $questionData['multipleScore'] = isset($questionData['multiple']) ? array_sum(ArrayHelper::getColumn($questionData['multiple'], 'score')) : '';
            //填空题总分数
            $questionData['gapScore'] = isset($questionData['gap']) ? array_sum(ArrayHelper::getColumn($questionData['gap'], 'score')) : '';
            //问答题总分数
            $questionData['essayScore'] = isset($questionData['essay']) ? array_sum(ArrayHelper::getColumn($questionData['essay'], 'score')) : '';
        }

        return $questionData;

    }
}
