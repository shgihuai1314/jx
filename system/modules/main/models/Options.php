<?php

namespace system\modules\main\models;

use yii\helpers\ArrayHelper;

/**
 * 选项
 *
 * @property integer $id
 * @property string $name
 * @property string $value
 */
class Options extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_options';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['name'], 'required'],
            [['value'], 'string'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'name' => 'Name',
            'value' => 'Value',
        ], parent::attributeLabels());
    }

}
