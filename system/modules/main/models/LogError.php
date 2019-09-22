<?php

namespace system\modules\main\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tab_log_error".
 *
 * @property string $id
 * @property integer $level
 * @property string $category
 * @property double $log_time
 * @property string $prefix
 * @property string $message
 */
class LogError extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_log_error';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['level'], 'integer'],
            [['log_time'], 'number'],
            [['prefix', 'message'], 'string'],
            [['category'], 'string', 'max' => 255],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'ID',
            'level' => 'Level',
            'category' => 'Category',
            'log_time' => 'Log Time',
            'prefix' => 'Prefix',
            'message' => 'Message',
        ], parent::attributeLabels());
    }
}
