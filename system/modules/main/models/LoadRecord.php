<?php

namespace system\modules\main\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_load_record".
 *
 * @property integer $id
 * @property string $load
 * @property integer $create_at
 */
class LoadRecord extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_load_record';
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
            'load' => '系统负载率',
            'create_at' => '添加时间',
        ], parent::attributeLabels());
    }


}
