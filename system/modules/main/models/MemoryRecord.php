<?php

namespace system\modules\main\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_memory_record".
 *
 * @property integer $id
 * @property string $memory
 * @property integer $create_at
 */
class MemoryRecord extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_memory_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['create_at'], 'integer'],
           // [['memory'], 'string'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'id',
            'memory' => '运行内存',
            'create_at' => '添加时间',
        ], parent::attributeLabels());
    }


}
