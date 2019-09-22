<?php

namespace system\modules\exam\models;

use Symfony\Component\Console\Question\Question;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_exam_question_category".
 * @property integer $id
 * @property string $name
 * @property integer $user_id
 * @property integer $update_time
 * @property integer $is_delete
 * @property integer $is_question_bank
 */
class ExamQuestionCategory extends \system\models\Model
{
    //是否记录日志
    public $log_flag = true;
    //日志信息配置
    public $log_options = [
        'model_name' => 'tab_exam_question_category',//模型名称
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_exam_question_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['user_id'], 'integer'],
            [['name'], 'string'],
            [['update_time','is_delete','is_question_bank'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '主键',
            'name' => '题库名称',
            'user_id' => '用户id',
            'update_time' => '更新时间',
            'is_delete'=>'是否删除',
            'is_question_bank'=>'公共题库'
        ], parent::attributeLabels());
    }

    /**
     * 获取属性
     * @param string $field
     * @param string $key
     * @param bool $default
     * @return array|bool|string
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
        $list = [
            'is_question_bank' => ['1' => '是', '0' => '否'],
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->user_id = Yii::$app->user->id;
                $this->update_time = time();
            } else {
                if (!$this->user_id) {
                    $this->user_id = '';
                } else {
                    $this->user_id = Yii::$app->user->id;
                }
                $this->update_time = time();
            }
        }
        return true;
    }

    /**
     * 获取题目
     * @return \yii\db\ActiveQuery
     */
    public function getQuestion(){
        return $this->hasMany(ExamQuestion::className(),['cate_id'=>'id']);
    }
    /**
     * 删除关联数据,删除题库的时候删除关联数据
     * @return bool
     */
    public function beforeDelete()
    {
      /*  if (parent::beforeDelete()) {
//            ExamQuestion::BatchDel(['cate_id' => $this->id]);
            ExamQuestion::updateAll(['is_delete' => 1], ['cate_id'=>$this->id]);
            return true;
        }
        return false;*/
    }


    /**
     * 获取题库
     * @params $type 为真的时候获取自己的题库，否则获取公共题库
     * @return array|bool
     */
    public static function getMapQuestionBank($type = '')
    {
        $query = ExamQuestionCategory::find();

        if ($type) {
            $query->where(['user_id' => Yii::$app->user->id,'is_delete'=>0,'is_question_bank'=>0]);
        } else {
            $query->where(['is_delete'=>0,'is_question_bank'=>1]);
        }

        $result = $query->asArray()->all();

        return ArrayHelper::map($result, 'id', 'name');
    }

    /**
     * 获取题库的数据
     * @param string $type
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public static function getAllData($type = '')
    {
        $query = ExamQuestionCategory::find();

        if (!$type) {
            $query->where(['is_delete'=>0]);
        }

        $data = $query->asArray()
            ->all();

        if (!$data) {
            return false;
        }

        return $data;
    }


}
