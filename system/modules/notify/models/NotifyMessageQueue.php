<?php

namespace system\modules\notify\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tab_notify_message_queue".
 *
 * @property integer $queue_id
 * @property integer $user_id
 * @property string $node_name
 * @property string $data
 * @property integer $created_at
 * @property integer $state
 */
class NotifyMessageQueue extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_notify_message_queue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['user_id', 'created_at', 'state'], 'integer'],
            [['data'], 'required'],
            [['data'], 'string'],
            [['node_name'], 'string', 'max' => 255],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'queue_id' => '消息队列id',
            'user_id' => '用户id',
            'node_name' => '节点名称',
            'data' => '消息参数',
            'created_at' => '创建时间',
            'state' => '0未处理；1处理中',
        ], parent::attributeLabels());
    }
}
