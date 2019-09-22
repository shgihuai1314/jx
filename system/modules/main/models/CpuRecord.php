<?php

namespace system\modules\main\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_cpu_record".
 *
 * @property integer $id
 * @property string $cpu
 * @property integer $create_at
 */
class CpuRecord extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_cpu_record';
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
            'cpu' => 'cpu负载率',
            'create_at' => '添加时间',
        ], parent::attributeLabels());
    }


}
