<?php

namespace system\modules\exam\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_exam_answer".
 *
 * @property integer $id
 * @property integer $question_id
 * @property string $question_answer
 * @property integer $exam_record_id
 * @property string $remark,
 * @property integer $score,
 * @property integer $remark_time,
 * @property integer $remark_user
 * @property integer $is_correct   //是否正确
 */
class ExamAnswer extends \system\models\Model
{
    //是否记录日志
    public $log_flag = true;
    //日志信息配置
    public $log_options = [
        'target_name' => 'id',//日志目标对应的字段名，默认name
        'model_name' => 'tab_exam_answer',//模型名称
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_exam_answer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['question_id', 'exam_record_id','remark_time','remark_user'], 'integer'],
            [['question_answer','remark','score','is_correct'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '主键',
            'question_id' => '试题id',
            'question_answer' => '答案',
            'exam_record_id' => '考试id',
            'remark'=>'评语',
            'score'=>'分数',
            'remark_time'=>"评论时间",
            'remark_user'=>"点评人",
            'is_correct'=>"答案是否正确"
        ], parent::attributeLabels());
    }

    /**
     *
     * 废弃
     * 添加答题记录
     */
    public static function addData1($question, $answer, $record_id)
    {
        if ($answer) {
            foreach ($answer as $k => $v) {
                //是否存在这个记录，存在修改，不存在添加
                if($v){
                    $onAnswer = self::findOne(['exam_record_id' => $record_id, 'question_id' => $question[$k]]);
                    if ($onAnswer) {
                        is_array($v)?sort($v):null;
                        $onAnswer->question_answer = is_array($v)?implode(',',$v):$v;
                        $onAnswer->save();
                    } else {
                        $model = new ExamAnswer();
                        $model->question_id = $question[$k];
                        is_array($v)?sort($v):null;
                        $model->question_answer = is_array($v)?implode(',',$v):$v;
                        $model->exam_record_id = $record_id;
                        $model->save();
                    }
                }
            }
        }
//        return true;
    }


    /**
     * 添加答题记录
     * @param $answer
     * @param $record_id
     */
    public static function addData($answer, $record_id)
    {
        if ($answer) {
            foreach ($answer as $k => $v) {
                //是否存在这个记录，存在修改，不存在添加
                if($v){
                    $onAnswer = self::findOne(['exam_record_id' => $record_id, 'question_id' => $k]);
                    $oneQuestion=ExamQuestion::findOne($k);
                    if ($onAnswer) {
                        is_array($v)?sort($v):null;

                        $answer= is_array($v)?implode(',',$v):$v;
                        if($oneQuestion->result==$answer){
                            $onAnswer->is_correct=1;
                        }
                        $onAnswer->question_answer = $answer;//正确答案
                        $onAnswer->save();
                    } else {
                        $model = new ExamAnswer();
                        $model->question_id = $k;//题目id
                        is_array($v)?sort($v):null;//答案排序
                        $answer= is_array($v)?implode(',',$v):$v;

                        if($oneQuestion->result==$answer){
                            $model->is_correct=1;
                        }

                        $model->question_answer =$answer;//正确答案
                        $model->exam_record_id = $record_id;//答题记录
                        $model->save();
                    }
                }
            }
        }
//        return true;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @return bool
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if(!$insert){
            $model=ExamRecord::find()->where(['id'=>$this->exam_record_id])->one();
            if(isset($changedAttributes['score'])){
                $model->score=$model->score+$this->score;
                $model->save();
                return true;
            }

            return false;
        }

        return false;

    }

    public function getQuestion()
    {
        return $this->hasOne(ExamQuestion::className(), ['id' => 'question_id']);
    }


}
