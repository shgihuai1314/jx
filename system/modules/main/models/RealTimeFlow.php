<?php

namespace system\modules\main\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_real_time_flow".
 *
 * @property integer $id
 * @property double $up_side
 * @property double $down_side
 * @property double $net_out_speed
 * @property double $net_input_speed
 * @property integer $create_at
 */
class RealTimeFlow extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_real_time_flow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['create_at'], 'integer'],
            [['up_side', 'down_side', 'net_out_speed', 'net_input_speed'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '实时流量id',
            'up_side' => '上行流量',
            'down_side' => '下行流量',
            'net_out_speed' => '上行流量',
            'net_input_speed' => '上行流量',
            'create_at' => '添加时间',
        ], parent::attributeLabels());
    }
}
