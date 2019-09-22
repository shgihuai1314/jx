<?php

namespace system\modules\payment\migrations;

use console\models\Migration;

class M180828093710Add_config_filed_menu extends Migration
{
    // 所属模块
    public $module_id = 'payment';

    // 更新说明
    public $description = '配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu';

    // 版本号
    public $version = '1.0';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //配置
        'systemConfig'=>[
            //支付宝相关配置
            [
                'name' => 'SYSTEM_ALIPAY_CONFIG',
                'title' => '支付宝支付配置',
                'type' => 'array',
                'value' => "appid=xx\r\npublic_key=xx\r\nprivate_key=xx",
            ],
            //微信支付相关配置
            [
                'name' => 'SYSTEM_WEPAY_CONFIG',
                'title' => '微信支付配置',
                'type' => 'array',
                'value' => "appid=xx\r\nmchid=xx\r\nsub_appid=xx\r\nsub_mch_id=xx\r\nkey=xx",
            ],

            [
                'name' => 'SYSTEM_HOST',
                'title' => '系统域名',
                'type' => 'string',
                'value' => "http://net.hubu.edu.cn",
            ],

            //支付类型
            [
                'name' => 'PAYMENT_PAY_TYPE',
                'title' => '支付平台支付方式',
                'type' => 'array',

                'value' => "alipay=支付宝\r\nwechat=微信",
            ],

            //支付类型对应的类
            [
                'name' => 'PAYMENT_PAY_CLASS',
                'title' => '支付类型对应的支付类',
                'type' => 'array',
                'value' => "alipay=\system\modules\payment\models\Alipay\r\nwechat=\system\modules\payment\models\WxPay",
            ],

            //支付类型对应的类
            [
                'name' => 'PAYMENT_PAY_CLASS',
                'title' => '支付类型对应的支付类',
                'type' => 'array',
                'value' => "alipay=\system\modules\payment\models\Alipay\r\nwechat=\system\modules\payment\models\WxPay",
            ],
        ],
        //拓展字段
        'extendsField' => [

        ],

        // 菜单
        'menu' => [
            'menu_name' => '支付',
            'module' => 'payment',
            'path' => 'system/modules/payment',
            'pid' => 4,
            'icon' => 'iconfont icon-no-checked',
            'children' => [
                [
                    'menu_name' => '支付配置',
                    'path' => 'payment/default/index',
                    'icon' => 'iconfont icon-no-checked',
                    'children' => [
                        [
                            'menu_name' => '添加配置',
                            'path' => 'payment/default/add',
                            'type' => 1,
                        ],
                        [
                            'menu_name' => '删除配置',
                            'path' => 'payment/default/del',
                            'type' => 1,
                        ],
                        [
                            'menu_name' => '编辑配置',
                            'path' => 'payment/default/edit',
                            'type' => 1,
                        ],
                    ]
                ],
            ]
        ],
    ];

    public function up()
    {
        parent::up();

    }

    public function down()
    {
        parent::down();
    }
}
