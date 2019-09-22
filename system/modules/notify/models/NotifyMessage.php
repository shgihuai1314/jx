<?php

namespace system\modules\notify\models;

use system\modules\main\models\Modules;
use system\modules\user\models\User;
use system\modules\user\models\UserExtend;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_notify_message".
 *
 * @property integer $message_id            消息id
 * @property integer $user_id               用户id
 * @property string $node_name              节点名称
 * @property string $module                 模块名称
 * @property string $content                消息内容
 * @property integer $sender_id             发送人
 * @property integer $created_at            创建时间
 * @property integer $is_read               是否已读；0未读，1已读
 * @property integer $read_at               读取时间
 * @property string $code                   路由标识
 * @property string $params                 路由参数
 * @property integer $is_delete             是否删除；0未删除，1删除
 */
class NotifyMessage extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_notify_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['user_id', 'sender_id', 'created_at', 'is_read', 'is_delete', 'read_at'], 'integer'],
            [['content'], 'string'],
            [['node_name','code','params'], 'string', 'max' => 255],
            [['module'], 'string', 'max' => 64],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'message_id' => '消息id',
            'user_id' => '用户id',
            'node_name' => '节点名称',
            'module' => '节点模块',
            'content' => '内容',
            'sender_id' => '发送人',
            'created_at' => '创建时间',
            'is_read' => '是否已读',
            'read_at' => '读取时间',
            'code' => '路由标识',
            'params' => '路由参数',
            'is_delete' => '是否删除',
        ], parent::attributeLabels());
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
            }

            // 如果新设置为已读
            if ($this->is_read == 1 && $this->read_at == 0) {
                $this->read_at = time();
            }

            return true;
        }

        return false;
    }

    /**
     * 关联消息接收人
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * 关联消息发送人
     */
    public function getSender()
    {
        return $this->hasOne(User::className(), ['user_id' => 'sender_id']);
    }

    /**
     * 获取模块信息
     */
    public function getModuleinfo()
    {
        return $this->hasOne(Modules::className(), ['module_id' => 'module'])->select(['name', 'icon', 'module_id']);
    }

    /**
     * 获取未读消息
     */
    public static function messageInfo()
    {
        $data = self::find()->asArray()
            ->where(['user_id' => Yii::$app->user->id, 'is_delete' => 0, 'is_read' => 0])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return empty($data) ? [] : ArrayHelper::index($data, null, 'module');
    }

    /**
     * 查看用户接收到的消息的阅读状态
     * @return bool
     */
    public static function UserMessageStatus()
    {
        return NotifyMessage::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->andWhere(['module' => 'approval'])
            ->andWhere(['is_read' => 0])
            ->exists();
    }

    /**
     * 获取消息提醒类型名
     * @param $module
     * @return string
     */
    public static function getNotifyType($module)
    {
        $notify_type = Yii::$app->systemConfig->getValue('NOTIFY_TYPE', []);

        $data = '';

        foreach ($notify_type as $k => $v){
            if($k == $module){
                $data .= $v;
            }
        }

        return $data;
    }

    /**
     * 判断用户是否开启了此消息的提醒
     * @param $node_name
     * @param $user_id
     * @return bool
     */
    public static function isSendMessage($node_name,$user_id)
    {
        $message_notify= UserExtend::find()->select('extend_message_notify')
            ->where(['user_id' => $user_id])
            ->asArray()->one();

        if(!$message_notify['extend_message_notify']){
            return true;
        }

        $data = explode(',',$message_notify['extend_message_notify']);

        if(in_array($node_name,$data)){
            return false;
        }else{
            return true;
        }
    }
}
