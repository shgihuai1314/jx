<?php

namespace system\modules\user\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tab_user_extend".
 *
 * @property integer $user_id
 */
class UserExtend extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_user_extend';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['user_id'], 'integer'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'user_id' => '用户id',
        ], parent::attributeLabels());
    }

    /**
     * 选择字段属性列表
     * @param string $field 字段
     * @param string $key 字段对应的key
     * @param bool $default 默认值
     * @return array|bool|string
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
        $list = [];
        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * 获取用户基本信息
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

}
