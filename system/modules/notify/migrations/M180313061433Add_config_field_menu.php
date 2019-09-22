<?php

namespace system\modules\notify\migrations;

use console\models\Migration;

class M180313061433Add_config_field_menu extends Migration
{
    // 所属模块
    public $module_id = 'notify';

    // 更新说明
    public $description = '添加消息模块相关配置';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //配置
        'systemConfig'=>[
            [
                'name' => 'MAIL_CONFIG',
                'title' => '邮件服务器配置',
                'type' => 'array',
                'value' => 'host=smtp.163.com\r\nport=25\r\nusername=xxx@163.com\r\npassword=xxxxxx\r\nfrom=xxx@163.com\r\nfromName=雨滴科技',
                'remark' => '根据需要配置，可以使用class参数自定义邮件发送类'
            ],
            [
                'name' => 'SMS_CONFIG',
                'title' => '短信发送配置',
                'type' => 'array',
                'value' => 'appKey=24571397\r\nsecretKey=28df389bd0fc601e43fa021840e0bf6a\r\nsignName=雨滴科技',
                'remark' => '根据需要配置，可以使用class参数自定义短信发送类，默认使用阿里大于短信服务'
            ],
            [
                'name' => 'VERIFY_CODE_VALIDITY',
                'title' => '验证码有效期',
                'type' => 'number',
                'value' => '30',
                'remark' => '单位：分钟；短信验证码在此期间内有效，过期则实效',
            ]
        ],
        //拓展字段
        'extendsField'=>[

        ],
        //菜单
        'menu' => [
            'menu_name' => '消息设置',
            'module' => 'notify',
            'path' => 'notify/node/index',
            'pid' => 3,  // 放到系统设置里面
            'icon' => 'fa fa-bell',
            'children' => [
                [
                    'menu_name' => '添加',
                    'path' => 'notify/node/add',
                    'type' => 1,
                ],
                [
                    'menu_name' => '修改',
                    'path' => 'notify/node/edit',
                    'type' => 1,
                ],
                [
                    'menu_name' => '删除',
                    'path' => 'notify/node/del',
                    'type' => 1,
                ],
            ]
        ]
    ];

    public function up()
    {
        parent::up();
    }

    public function down()
    {
        $this->printLog(" 核心模块重要配置不能删除！\n");
        return false;
        // parent::down();
    }
}
