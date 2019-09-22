<?php

namespace system\modules\user\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_class_detail".
 *
 * @property integer $id
 * @property string $name
 * @property string $headmaster
 * @property integer $create_at
 */
class ClassDetail extends \system\models\Model
{
    //是否记录日志
    public $log_flag = true;
    //日志信息配置
    public $log_options = [
        'model_name' => '班级信息',//模型名称
        'normal_field' => ['name', 'headmaster', 'create_at'],// 要记录日志的普通字段 并非所有字段都需要记录，比如更新时间、创建人不需要记录，操作日志的作用是便于管理员排错，记录必要的信息即可。
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_class_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['name'], 'required'],
            [['create_at'], 'safe'],
            [['name', 'headmaster'], 'string'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'ID',
            'name' => '班级名称',
            'headmaster' => '班主任',
            'create_at' => '开班时间',
        ], parent::attributeLabels());
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            $this->create_at = strtotime($this->create_at);

            return true;
        }

        return false;
    }
}
