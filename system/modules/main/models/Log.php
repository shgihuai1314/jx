<?php

namespace system\modules\main\models;

use system\modules\user\models\User;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%tab_log}}".
 *
 * @property integer $log_id
 * @property string $type  日志类型，如login，factory，gateway
 * @property integer $target_id 目标id，比如网关id，厂商id等
 * @property integer $target_id2 目标id，有时候业务可能需要多个id，比如网关类型对应的网关id和设备id
 * @property string $content 日志内容
 * @property integer $add_time
 * @property string $ip
 * @property string $user_id
 */
class Log extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['target_id', 'target_id2', 'add_time', 'user_id'], 'integer'],
            [['content'], 'string'],
            [['type'], 'string', 'max' => 32],
            [['ip'], 'string', 'max' => 64],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'log_id' => 'ID号',
            'type' => '消息类型；system，login，manage，',
            'target_id' => '日志对应的id，比如设备id',
            'target_id2' => '日志对应的id，比如设备id',
            'content' => '消息内容',
            'add_time' => '操作时间',
            'ip' => '操作ip',
            'user_id' => '创建人',
        ], parent::attributeLabels());
    }

    /**
     * 关联创建者的user_id
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * 获取日志记录
     * @param $type
     * @param $target_id
     * @param $order
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDataByTypeAndId($type, $target_id, $order = SORT_ASC)
    {
        return self::find()->where(['type' => $type, 'target_id' => $target_id])->with('user')->orderBy(['log_id' => $order])->asArray()->all();
    }
}
