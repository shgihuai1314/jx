<?php

namespace system\modules\main\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_real_time_memory".
 *
 * @property integer $id
 * @property string $memory_usage
 * @property integer $create_at
 */
class RealTimeMemory extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_real_time_memory';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['create_at'], 'integer'],

        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'id',
            'memory_usage' => '运行内存',
            'create_at' => '添加时间',
        ], parent::attributeLabels());
    }


}
