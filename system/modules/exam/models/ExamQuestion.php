<?php

namespace system\modules\exam\models;

use system\modules\user\models\User;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_exam_question".
 *
 * @property integer $id
 * @property integer $cate_id
 * @property string $title
 * @property integer $question_type
 * @property string $result
 * @property string $update_time
 * @property string $option_1
 * @property string $option_2
 * @property string $option_3
 * @property string $option_4
 * @property string $option_5
 * @property string $option_6
 * @property string $option_7
 * @property string $option_8
 * @property integer $difficulty
 * @property string $explain
 * @property integer $is_delete
 */
class ExamQuestion extends \system\models\Model
{
    //是否记录日志
    public $log_flag = true;
    //日志信息配置
    public $log_options = [
        'target_name' => 'id',//日志目标对应的字段名，默认name
        'model_name' => 'tab_exam_question',//模型名称
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_exam_question';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['cate_id', 'question_type', 'update_time', 'is_delete'], 'integer'],
            [['is_delete', 'result'], 'safe'],
            [['explain', 'title', 'option_1', 'option_2', 'option_3', 'option_4', 'option_5', 'option_6', 'option_7', 'option_8', 'difficulty'], 'string'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '主键',
            'cate_id' => '题库id',
            'title' => '试题标题',
            'question_type' => '试题类别，如：1，单选题，2，多选题，3，判断题 4,填空 5，问答',
            'result' => '正确答案，多选题的答案为逗号隔开的字符串',
            'explain' => '答案说明',
            'is_delete' => '是否删除',
            'difficulty' => '试题难易度',
            'update_time' => '更新时间',
            'option_1' => '试题选项1',
            'option_2' => '试题选项1',
            'option_3' => '试题选项1',
            'option_4' => '试题选项1',
            'option_5' => '试题选项1',
            'option_6' => '试题选项1',
            'option_7' => '试题选项1',
            'option_8' => '试题选项1',
        ], parent::attributeLabels());
    }

    /**
     * 获取题库名称
     * @return \yii\db\ActiveQuery
     */
    public function getBank()
    {
        return $this->hasOne(ExamQuestionCategory::className(), ['id' => 'cate_id']);
    }


    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->update_time = time();
            return true;
        }

        return false;
    }


    /**
     * 根据题目类型和题库获取题目数量
     * @param $question_type
     * @param $question_banks
     */
    public static function getQuestionNum($question_type = '', $question_banks = '')
    {
        if (!isset($question_type, $question_banks)) {
            return false;
        }

        if (is_string($question_banks)) {
            $question_banks = explode(',', $question_banks);
        }

        $select_count = self::find()
            ->where(['question_type' => $question_type])
            ->andWhere(['in', 'cate_id', $question_banks])
            ->count();

        return $select_count;
    }

    /**
     * 查询所有的数据
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public static function getAll()
    {
        $data = self::getAllDataCache();
        return $data;
    }

    /**
     * 根据条件查询题目
     * @param array $condition
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public static function getConditionQuestion($condition = [])
    {
        $query = self::find();

        if ($condition['questionId']) {
            $query->where(['id' => $condition['questionId']]);
        }

        $data = $query->asArray()->all();

        if (!$data) {
            return false;
        }

        return $data;

    }

    /**
     * 查询谋试题的详情
     * @param $id
     */
    public static function getQuestionDetail($id)
    {
        $result = self::findOne(['id' => $id]);
        if (!$result) {
            return false;
        }

        return $result;
    }

   public function getAnswer(){
       return $this->hasOne(ExamAnswer::className(),['question_id'=>'id']);
   }
}
