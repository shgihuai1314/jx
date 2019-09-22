<?php

namespace system\modules\notify\migrations;

use console\models\Migration;
use yii\db\Expression;

/**
 * Handles adding send_wechat to table `tab_notify_node`.
 */
class M180313061463Add_send_wechat_columns_to_tab_notify_node_table extends Migration
{
    // 所属模块
    public $module_id = 'notify';

    // 更新说明
    public $description = '给消息节点表增加是否发送微信公众号消息字段send_wechat';

    public $config = [
        //配置
        'systemConfig' => [
            [
                'name' => 'WECHAT_APPID',
                'title' => '微信开发者ID',
                'type' => 'string',
                'value' => 'wxc7026a510cc5c4bf',
                'remark' => '开发者ID是公众号开发识别码，配合开发者密码可调用公众号的接口能力。'
            ],
            [
                'name' => 'WECHAT_APPSECRET',
                'title' => '微信开发者密码',
                'type' => 'string',
                'value' => '',
                'remark' => '开发者密码是校验公众号开发者身份的密码，具有极高的安全性。'
            ],
            [
                'name' => 'WECHAT_TEMPLATE_ID',
                'title' => '微信公众号默认消息模板ID',
                'type' => 'array',
                'value' => 'vXXyQl0IpEfjT6F5qddK147NXVj18YX2z_oBtGAVdjA',
                'remark' => '微信公众号消息模板ID'
            ],
            [
                'name' => 'WECHAT_TEMPLATE_PARAMS',
                'title' => '微信公众号默认消息模板参数',
                'type' => 'string',
                'value' => "first={module}\r\nkeyword1=雨滴OA\r\nkeyword2={name}\r\nkeyword3={time}\r\nremark={content}",
                'remark' => '微信公众号默认消息模板参数'
            ]
        ],
        //拓展字段
        'extendsField' => [
            [
                'table_name' => 'tab_notify_node',
                'field_name' => 'wechat_template_id',
                'field_title' => '公众号消息模板',
                'field_type' => 'string',
                'show_type' => 'text',
            ],
            [
                'table_name' => 'tab_notify_node',
                'field_name' => 'wechat_template_params',
                'field_title' => '公众号模板参数',
                'field_type' => 'string',
                'show_type' => 'textarea',
                'field_explain' => '{openid}表示用户的微信openid，{module}表示模板名称，{name}表示用户姓名，{content}表示消息内容，{time}表示当前时间'
            ],
            [
                'table_name' => 'tab_user_extend',
                'field_name' => 'extend_openid',
                'field_title' => '微信标识',
                'field_type' => 'string',
                'show_type' => 'text',
            ]
        ]
    ];

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('tab_notify_node', 'send_app',
            $this->smallInteger(1)->notNull()->defaultValue(0)->comment('app发送'));
        $this->addColumn('tab_notify_node', 'send_qywx',
            $this->smallInteger(1)->notNull()->defaultValue(0)->comment('企业微信'));
        $this->addColumn('tab_notify_node', 'send_wechat',
            $this->smallInteger(1)->notNull()->defaultValue(0)->comment('发送微信公众号消息'));

        $this->update('tab_config', ['value' => new Expression("concat(value, '\r\ntab_user_extend=用户扩展表')")], ['name' => 'EXTEND_FIELD_TABLE_NAME']);

        parent::up();
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('tab_notify_node', 'send_wechat');
        $this->dropColumn('tab_notify_node', 'send_qywx');
        $this->dropColumn('tab_notify_node', 'send_app');

        $this->update('tab_config', ['value' => new Expression("replace(value, '\r\ntab_user_extend=用户扩展表', '')")], ['name' => 'EXTEND_FIELD_TABLE_NAME']);

        parent::down();
    }
}
