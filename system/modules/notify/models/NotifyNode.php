<?php

namespace system\modules\notify\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_notify_node".
 * @property integer $node_id           节点id
 * @property string $node_name          节点名称
 * @property string $node_info          节点信息
 * @property string $module             节点所属模块
 * @property string $icon               消息提醒图标
 * @property string $content            默认的消息内容
 * @property int $is_self               用户是否可设置开关；0否，1是
 * @property int $send_message          是否发送站内信；0不发送，1发送，默认1
 * @property int $send_email            是否发送email；0不发送，1发送，默认0
 * @property int $send_sms              是否发送短信；0不发送，1发送，默认0
 * @property int $send_app              是否发送app消息；0不发送，1发送，默认0
 * @property int $send_qywx             是否企业微信消息；0不发送，1发送，默认0
 * @property int $send_wechat           是否微信公众号消息；0不发送，1发送，默认0
 */
class NotifyNode extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_notify_node';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
	        [['content'], 'string'],
	        [['node_name', 'node_info', 'icon'], 'string', 'max' => 255],
	        [['module'], 'string', 'max' => 64],
	        [['node_name'], 'required'],
	        [['is_self', 'send_message', 'send_email', 'send_sms', 'send_app', 'send_qywx', 'send_wechat'], 'integer']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'node_id' => 'ID',
            'node_name' => '消息名称',
            'node_info' => '消息描述',
            'module' => '提醒类型',
            'icon' => '提醒图标',
            'content' => '消息模板',
            'is_self' => '用户设置开关',
            'send_message' => '系统消息',
            'send_email' => '邮件消息',
            'send_sms' => '短信消息',
            'send_app' => 'app消息',
            'send_qywx' => '企业微信',
            'send_wechat' => '微信公众号'
        ], parent::attributeLabels());
    }

    /**
     * 选择性属性列表
     * @param string $field 字段名
     * @param string $key 查找的key
     * @param string $default 默认值(未查到结果的情况下返回)
     * @return array|bool|string
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
        $list = [
            //'module' => \system\modules\main\models\Modules::getModuleMap(),
            'module' => Yii::$app->systemConfig->getValue('NOTIFY_TYPE', []),
            'is_self' => [1 => '是', 0 => '否'],
            'send_message' => [1 => '发送', 0 => '不发送'],
            'send_email' => [1 => '发送', 0 => '不发送'],
            'send_sms' => [1 => '发送', 0 => '不发送'],
            'send_app' => [1 => '发送', 0 => '不发送'],
            'send_qywx' => [1 => '发送', 0 => '不发送'],
            'send_wechat' => [1 => '发送', 0 => '不发送']
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * 获取所有的消息节点
     * @param $refresh bool 刷新缓存
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public static function getAllNode($refresh = false)
    {
        $key = 'notify:node:all';
        $allNode = Yii::$app->cache->get($key);
        if (!$allNode || $refresh) {
            $allNode = self::find()->asArray()->indexBy('node_name')->all();
            Yii::$app->cache->set($key, $allNode);
        }

        return $allNode;
    }

    /**
     * 获取node节点的数据
     * @param $node_name string 节点名称
     * @return array|mixed|\yii\db\ActiveRecord
     */
    public static function getOneNode($node_name)
    {
        $allNode = self::getAllNode();
        if (isset($allNode[$node_name])) {
            return $allNode[$node_name];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // 刷新缓存
        self::getAllNode(true);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        // 刷新缓存
        self::getAllNode(true);
    }


}
