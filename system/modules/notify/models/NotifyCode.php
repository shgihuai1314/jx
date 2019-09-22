<?php

namespace system\modules\notify\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tab_notify_sms_record".
 *
 * @property integer $id            自增id
 * @property integer $user_id       用户id
 * @property string $target         发送的目标
 * @property string $send_type      发送的类型
 * @property string $node           消息节点
 * @property string $code           验证码
 * @property integer $create_at     创建时间
 * @property integer $is_verify     是否已验证，验证一次就失效了
 */
class NotifyCode extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_notify_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['create_at', 'user_id', 'is_verify'], 'integer'],
            [['target', 'node', 'code'], 'string', 'max' => 64],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '自增id',
            'user_id' => '用户id',
            'target' => '发送的目标',
            'node' => '消息节点',
            'code' => '验证码',
            'create_at' => '创建时间',
            'is_verify' => '是否已验证'
        ], parent::attributeLabels());
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($insert) {
                $this->create_at = time();
            }

            return true;
        }

        return false;
    }


}
