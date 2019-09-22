<?php

namespace system\modules\notify\models;

use system\modules\user\models\Attention;
use system\modules\user\models\User;
use system\modules\user\models\UserExtend;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "tab_direct_message".
 *
 * @property integer $id
 * @property integer $from_user
 * @property integer $to_user
 * @property string $content
 * @property integer $create_at
 * @property integer $is_read
 * @property integer $from_delete
 * @property integer $to_delete
 */
class DirectMessage extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_direct_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['from_user', 'to_user', 'create_at', 'is_read', 'from_delete', 'to_delete'], 'integer'],
            [['content'], 'string'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'Id',
            'from_user' => '发送人',
            'to_user' => '接收人',
            'content' => '内容',
            'create_at' => '发送时间',
            'is_read' => '是否已读:0否;1是',
            'from_delete' => '发送人删除状态',
            'to_delete' => '接收人删除状态',
        ], parent::attributeLabels());
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->from_user = \Yii::$app->user->getId();
                $this->create_at = time();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        DirectRelation::saveRecord($this->from_user, $this->to_user, $this->id, $this->create_at);
        DirectRelation::saveRecord($this->to_user, $this->from_user, $this->id, $this->create_at);
        $this->pushMessage();
    }

    /**
     * 关联接收人
     * @return \yii\db\Query
     */
    public function getGetUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'to_user'])->select('realname, avatar, user_id');
    }

    /**
     * 关联发送人
     * @return \yii\db\Query
     */
    public function getSendUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'from_user'])->select('realname, avatar, user_id');
    }

    /**
     *  推送私信消息
     */
    public function pushMessage()
    {
        // 推送的url地址，使用自己的服务器地址
        $push_api_url = "tcp://127.0.0.1:8902";

        $postData = ArrayHelper::toArray($this);

        $postData['getUser'] = ArrayHelper::toArray($this->getUser);
        $postData['sendUser'] = ArrayHelper::toArray($this->sendUser);

        $client = stream_socket_client($push_api_url, $errno, $errmsg, 1);
        fwrite($client, json_encode($postData)."\n");
        echo fread($client, 8192);
        fclose($client);
    }

    /**
     * 判断用户是否只接受粉丝私信
     * @param $user_id
     * @return bool
     */
    public static function isSend($user_id)
    {
        $message_notify= UserExtend::find()->select('extend_direct_message')
            ->where(['user_id' => $user_id])
            ->asArray()->one();

        if($message_notify['extend_direct_message'] == 1){
            if(Attention::isFans($user_id)){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }
}
