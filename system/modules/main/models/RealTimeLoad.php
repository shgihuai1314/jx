<?php

namespace system\modules\main\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_real_time_load".
 *
 * @property integer $id
 * @property string $load_usage
 * @property integer $create_at
 */
class RealTimeLoad extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_real_time_load';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['create_at'], 'integer'],
            [['load_usage'], 'string'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'id',
            'load_usage' => '系统负载率',
            'create_at' => '添加时间',
        ], parent::attributeLabels());
    }


}
