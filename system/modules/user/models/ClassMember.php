<?php

namespace system\modules\user\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_class_member".
 *
 * @property integer $id
 * @property integer $class_id
 * @property integer $user_id
 * @property integer $join_time
 * @property string $position
 */
class ClassMember extends \system\models\Model
{
    //是否记录日志
    public $log_flag = true;
    //日志信息配置
    public $log_options = [
        'target_name' => 'class_id',//日志目标对应的字段名，默认name
        'model_name' => '班级成员',//模型名称
        'normal_field' => ['class_id', 'user_id', 'join_time', 'position'],// 要记录日志的普通字段 并非所有字段都需要记录，比如更新时间、创建人不需要记录，操作日志的作用是便于管理员排错，记录必要的信息即可。
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_class_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['class_id', 'user_id'], 'integer'],
            [['join_time'], 'safe'],
            [['position'], 'string'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'ID',
            'class_id' => '班级ID',
            'user_id' => '学员ID',
            'join_time' => '进班时间',
            'position' => '班级职位',
        ], parent::attributeLabels());
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            $this->join_time = strtotime($this->join_time);

            return true;
        }

        return false;
    }

    /**
     * 关联班级信息表
     * @return \yii\db\ActiveQuery
     */
    public function getClass()
    {
        return $this->hasOne(ClassDetail::className(), ['id' => 'class_id']);
    }
}
