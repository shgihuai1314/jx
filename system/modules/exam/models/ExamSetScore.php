<?php

namespace system\modules\exam\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_exam_set_score".
 *
 * @property string $id
 * @property integer $paper_id
 * @property integer $qustion_id
 * @property string $question_score
 */
class ExamSetScore extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_exam_set_score';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['paper_id', 'qustion_id'], 'integer'],
            [['question_score'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '主键',
            'paper_id' => '试卷id',
            'qustion_id' => '题目id',
            'question_score' => '题目分数',
        ], parent::attributeLabels());
    }

    public function getDetail()
    {
        return $this->hasOne(ExamQuestion::className(), ['id' => 'qustion_id']);
    }

    /**
     * 获取题目
     * @return \yii\db\ActiveQuery
     */
    public function getQuestion()
    {
        return $this->hasOne(ExamQuestion::className(), ['id' => 'qustion_id']);
    }

    /**
     * 获取单条数据
     * @param string $id
     * @param string $paper_id
     * @param string $question_id
     * @param string $score
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getPaperQuestionDetail($id = '', $paper_id = '', $question_id = '', $score = '')
    {
        $query = self::find();

        if ($paper_id) {
            $query->where(['paper_id' => $paper_id]);
        }

        if ($id) {
            $query->andWhere(['id' => $id]);
        }

        if ($question_id) {
            $query->andWhere(['qustion_id' => $question_id]);
        }

        if ($score) {
            $query->andWhere(['question_score' => $score]);
        }

        $data = $query->one();

        return $data;
    }

    /**
     * 获取所有的数据
     * @param string $paper_id
     */
    public static function getAllData($paper_id = '')
    {
        $data = self::find()->where(['paper_id' => $paper_id])->all();
        if (!$data) {
            return false;
        }

        return $data;
    }


    /**
     * 返回一条记录的分数
     * @param $paper_id
     * @param array $questionId
     * @return int
     */
    public static function computeScore($paper_id, $questionId ='')
    {
        $data = self::find()
            ->where(['paper_id' => $paper_id, 'qustion_id' => $questionId])
            ->asArray()
            ->one();

        return $data['question_score'];
    }
}
