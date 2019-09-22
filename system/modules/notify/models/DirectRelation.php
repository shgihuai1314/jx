<?php

namespace system\modules\notify\models;

use system\modules\user\models\User;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tab_direct_relation".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $chat_user
 * @property integer $latest_message
 * @property integer $update_at
 */
class DirectRelation extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_direct_relation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['user_id', 'chat_user', 'latest_message', 'update_at'], 'integer'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'Id',
            'user_id' => '用户ID',
            'chat_user' => '聊天对象',
            'latest_message' => '最新消息id',
            'update_at' => '发送时间'
        ], parent::attributeLabels());
    }

    /**
     * 关联私信消息
     * @return \yii\db\ActiveQuery
     */
    public function getMessage()
    {
        return $this->hasOne(DirectMessage::className(), ['id' => 'latest_message']);
    }

    /**
     * 关联私信对象
     * @return \yii\db\ActiveQuery
     */
    public function getChatUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'chat_user'])->select('realname, avatar, user_id, gender');
    }

    /**
     * 保存记录
     * @param $from_user
     * @param $to_user
     * @param $message_id
     * @param $time
     * @return bool|null|DirectRelation|static
     */
    public static function saveRecord($from_user, $to_user, $message_id, $time)
    {
        $model = self::findOne(['user_id' => $from_user, 'chat_user' => $to_user]);

        if (!$model) {
            $model = new self();
            $model->user_id = $from_user;
            $model->chat_user = $to_user;
        }

        $model->latest_message = $message_id;
        $model->update_at = $time;
        $model->save();
    }
}
