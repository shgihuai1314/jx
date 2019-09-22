<?php

namespace system\modules\user\migrations;

use console\models\Migration;

class M180313050525Add_config_field_menu extends Migration
{
    // 所属模块
    public $module_id = 'user';

    // 更新说明
    public $description = '增加用户模块相关配置';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //配置
        'systemConfig'=>[
            [
                'name' => 'USER_STATUS_LIST',
                'title' => '用户状态',
                'type' => 'array',
                'value' =>"0=正常\r\n1=禁用\r\n2=锁定\r\n3=删除",
            ],
            [
                'name' => 'USER_LOGIN_ERROR_TIMES',
                'title' => '允许用户错误登录次数',
                'type' => 'number',
                'value' => '5',
                'remark'=>'一定时间后才可以继续登录',
            ],
            [
                'name' => 'USER_LOGIN_ERROR_INTERVAL',
                'title' => '用户登录错误间隔期',
                'type' => 'number',
                'value' => '5',
                'remark'=>'用户连续登录错误达到一定次数后，会锁定ip和用户名，锁定的ip在这段间隔期间内不能登录系统；过了时间可以继续登录；单位：分钟；比如设置为15，那么用户登录错误次数达到5次以后，15分钟内不能登',
            ],
            [
                'name' => 'USER_LOGIN_ERROR_LOCK',
                'title' => '用户登录错误达到次数后是否更改为锁定状态',
                'type' => 'enum',
                'value' => '1',
                'extra'=>"0=不锁定\r\n1=锁定",
                'remark'=>'某个用户名连续登录错误达到一定次数后，是否要将此用户的状态更改为“锁定”状态，更改后用户就算输入正确，也无法再次登录，需要管理员在后台对用户进行解锁后才能继续登录；',
            ],
            [
                'name' => 'USER_LOGIN_SHOW_CAPTCHA',
                'title' => '用户登录是否显示验证码',
                'type' => 'enum',
                'value' => '0',
                'extra'=>"0=不显示\r\n1=显示",
                'remark'=>'用户登录时是否要显示验证码',
            ],
            [
                'name' => 'USER_LOGIN_ERROR_CAPTCHA',
                'title' => '用户达到错误次数显示验证码',
                'type' => 'number',
                'value' => '3',
                'remark'=>'用户连续登录错误多少次后显示验证码，一般默认时3次，如果设置为0，那么不显示验证码；如果设置了登录显示验证码，那么此参数无效；',
            ],
            [
                'name' => 'USER_GENDER_LIST',
                'title' => '用户性别',
                'type' => 'array',
                'value' => "0=保密\r\n1=男\r\n2=女",
            ],
            [
                'name' => 'USER_LIST_SHOW_CHILD',
                'title' => '用户列表是否默认显示子部门用户',
                'type' => 'number',
                'value' => '0',
                'remark'=>'1代表默认显示，0代表默认不显示',
            ],
            [
                'name' => 'USER_LIST_HEADER_SHOW_PAGE',
                'title' => '用户列表头部显示分页',
                'type' => 'number',
                'value' => '0',
                'remark'=>'因为分页在最下面，所以翻页不太方便，所以做了这个配置，0不显示，1显示',
            ],
            [
                'name' => 'USER_FIELD_REQUIRED_LIST',
                'title' => '用户必填字段设置',
                'type' => 'array',
                'value' => 'realname',
                'remark'=>'必填字段：一行一个，必填字段包括：姓名：realname，手机号码：phone，邮箱：email',
            ],
            [
                'name' => 'USER_LOGIN_PAGE_HELP',
                'title' => '用户登录页面显示帮助',
                'type' => 'text',
                'value' => "<ul>
                    <li>用户名使用学号或教工号</li>
                    <li>密码默认使用身份证后6位</li>
                    <li>如果没有身份证，默认密码是888888</li>
                    </ul>",
            ],
            [
                'name' => 'USER_GROUP_FILTER',
                'title' => '用户选择器过滤内容',
                'type' => 'string',
                'value' => '',
                'remark'=>'可以将用户选择器中不需要显示的用户、部门、职位在这里进行设置',
            ],
        ],
        //拓展字段
        'extendsField'=>[
            [
                'table_name' => 'tab_content_permission',
                'field_name' => 'extend_user_group_type',
                'field_title' => '用户管理范围',
                'field_type' => 'string',
                'show_type' => 'checkbox',
                'field_value' => "1=本部门\r\n2=主管部门",
            ],
            [
                'table_name' => 'tab_content_permission',
                'field_name' => 'extend_user_group',
                'field_title' => '指定部门',
                'field_type' => 'text',
                'show_type' => 'userGroupSelect',
                'template_parameter' => "show_user=0\r\nshow_page=department\r\nselect_type=department",
            ],
        ],
        //菜单
        'menu' => [
            [
                'menu_name' => '用户管理',
                'module' => 'user',
                'path' => 'user/manage/index',
                'pid' => 2,
                'icon' => 'fa fa-users',
                'sort' => 10,
                'children' => [
                    [
                        'menu_name' => '添加用户',
                        'path' => 'user/manage/add',
                        'type' => 1,
                    ],
                    [
                        'menu_name' => '修改用户',
                        'path' => 'user/manage/edit',
                        'type' => 1,
                    ],
                    [
                        'menu_name' => '删除用户',
                        'path' => 'user/manage/delete',
                        'type' => 1,
                    ],
                    [
                        'menu_name' => '批量操作',
                        'path' => 'user/manage/batch',
                        'type' => 1,
                    ],
                ]
            ],
            [
                'menu_name' => '内容权限',
                'path' => 'user/content-permission/index',
                'pid' => 2,
                'module' => 'user',
                'icon' => 'fa fa-unlock-alt',
                'children' => [
                    [
                        'menu_name' => '添加',
                        'path' => 'user/content-permission/create',
                        'type' => 1,
                    ],
                    [
                        'menu_name' => '编辑',
                        'path' => 'user/content-permission/update',
                        'type' => 1,
                    ],
                    [
                        'menu_name' => '删除',
                        'path' => 'user/content-permission/delete',
                        'type' => 1,
                    ],
                ]
            ],
            [
                'menu_name' => '部门管理',
                'module' => 'user',
                'path' => 'user/group/index',
                'pid' => 2,
                'icon' => 'fa fa-building-o',
                'children' => [
                    [
                        'menu_name' => '更新组织架构',
                        'path' => 'user/group/update',
                        'type' => 1,
                    ],
                    [
                        'menu_name' => '部门赋权',
                        'path' => 'user/group/edit',
                        'type' => 1,
                    ],
                ]
            ],
            [
                'menu_name' => '职位管理',
                'module' => 'user',
                'path' => 'user/position/index',
                'pid' => 2,
                'icon' => 'fa fa-address-card-o',
                'children' => [
                    [
                        'menu_name' => '增加',
                        'path' => 'user/position/add',
                        'type' => 1,
                    ],
                    [
                        'menu_name' => '编辑',
                        'path' => 'user/position/edit',
                        'type' => 1,
                    ],
                    [
                        'menu_name' => '删除',
                        'path' => 'user/position/delete',
                        'type' => 1,
                    ],
                ]
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
