<?php

namespace system\modules\main\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tab_app_relation".
 *
 * @property string $id
 * @property integer $cate_id
 * @property integer $app_id
 */
class AppRelation extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_app_relation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['cate_id', 'app_id'], 'required'],
            [['cate_id', 'app_id'], 'integer'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'ID',
            'cate_id' => 'Cate ID',
            'app_id' => 'App ID',
        ], parent::attributeLabels());
    }

    public function getApps()
    {
        return $this->hasOne(App::className(),['id' => 'app_id'])
            ->where([App::tableName().'.is_show'=>1]);
    }
}
