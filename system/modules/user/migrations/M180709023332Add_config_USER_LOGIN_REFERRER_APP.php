<?php

namespace system\modules\user\migrations;

use console\models\Migration;

class M180709023332Add_config_USER_LOGIN_REFERRER_APP extends Migration
{
    // 所属模块
    public $module_id = 'user';

    // 更新说明
    public $description = '增加配置项：允许自动跳转到登录前的应用USER_LOGIN_REFERRER_APP';

    // 版本号
    public $version = '1.0';

    public $config = [
        //配置
        'systemConfig'=>[
            [
                'name' => 'USER_LOGIN_REFERRER_APP',
                'title' => '允许自动跳转到登录前的应用',
                'type' => 'array',
                'value' =>"",
                'remark' => '一行一个入口标识'
            ],
        ]
    ];

    public function up()
    {
        parent::up();
    }

    public function down()
    {

    }
}
