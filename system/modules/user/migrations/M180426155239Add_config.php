<?php

namespace system\modules\user\migrations;

use console\models\Migration;

class M180426155239Add_config extends Migration
{
    // 所属模块
    public $module_id = 'user';

    // 更新说明
    public $description = '增加了cas单点登录方式的相关配置';

    // 版本号
    public $version = '1.0';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //配置
        'systemConfig' => [
            [
                'name' => 'USER_LOGIN_CAS',
                'title' => 'CAS认证参数',
                'type' => 'array',
                'value' =>"loginUrl=https://cas.whcp.edu.cn/lyuapServer/login\r\nvalidUrl=https://cas.whcp.edu.cn/lyuapServer/proxyValidate",
                'remark' => 'loginUrl:登录的url，validUrl:验证ticket的url',
            ],
            [
                'name' => 'USER_LOGIN_CAS_APP',
                'title' => '通过Cas进行认证的应用',
                'type' => 'array',
                'value' =>"",
                'remark' => '填写入口',
            ],
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
