<?php

namespace system\modules\main\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_flow".
 *
 * @property integer $id
 * @property string $up_side
 * @property string $down_side
 * @property integer $update_at
 */
class Flow extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_flow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['create_at'], 'integer'],
            [['up_side', 'down_side'], 'string'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '流量id',
            'up_side' => '上行流量',
            'down_side' => '下行流量',
            'create_at' => '添加时间',
        ], parent::attributeLabels());
    }
}
