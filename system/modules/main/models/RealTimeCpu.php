<?php

namespace system\modules\main\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_real_time_cpu".
 *
 * @property integer $id
 * @property string $cpu_usage
 * @property integer $create_at
 */
class RealTimeCpu extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_real_time_cpu';
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
            'cpu_usage' => 'cpu负载率',
            'create_at' => '添加时间',
        ], parent::attributeLabels());
    }


}
