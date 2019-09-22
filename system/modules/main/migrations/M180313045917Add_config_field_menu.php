<?php

namespace system\modules\main\migrations;

use console\models\Migration;

class M180313045917Add_config_field_menu extends Migration
{
    // 所属模块
    public $module_id = 'main';

    // 更新说明
    public $description = '增加主模块相关配置';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //配置
        'systemConfig' => [
            // 全局
            [
                'name' => 'SYSTEM_ALLOW_IP',
                'title' => '系统允许访问的IP',
                'type' => 'array',
                'remark' => '每行一条数据，可以使用通配符*，如果不配置表示不限制IP访问.',
            ],
            [
                'name' => 'SYSTEM_FORBIDDEN_IP',
                'title' => '系统禁止访问IP',
                'type' => 'array',
                'remark' => '每行一条数据，禁止访问系统ip集合，可以是单独ip，也可以使用通配符，比如：192.168.1.*，不输入则代表不限制ip访问',
            ],
            [
                'name' => 'LIST_ROWS',
                'title' => '后台每页记录数',
                'type' => 'number',
                'value' => '10',
                'remark' => '后台数据列表每页显示记录数',
            ],
            [
                'name' => 'SYSTEM_AUTHOR',
                'title' => '系统作者',
                'type' => 'string',
                'value' => "武汉雨滴科技有限公司",
            ],
            [
                'name' => 'MAIN_LAYOUT_HEADER_EXTEND',
                'title' => '后台主布局文件头部扩展',
                'type' => 'text',
            ],
            [
                'name' => 'MAIN_FRAME_NAV_SPREAD',
                'title' => '后台二级菜单默认展开',
                'type' => 'enum',
                'value' => '0',
                'extra' => "0=不展开\r\n1=展开",
            ],
            // 配置功能
            [
                'name' => 'CONFIG_TYPE_LIST',   // 必填，配置名称
                'title' => '配置类型列表',       // 必填，配置的标题
                'type' => 'array',              // 必填 类型，默认string，有如下类型：number数字，string字符串，text文本，array数组，num枚举
                'value' => "number=数字\r\nstring=字符\r\ntext=长文本\r\narray=数组\r\nenum=枚举\r\nimage=图片", // 设置的值
                'remark' => '数据解析时会根据类型进行解析成不同的数据', // 配置说明
                //'sort' => 0,  // 排序，可选项，默认0
            ],
            // 日志，这个可能会弃用
            [
                'name' => 'LOG_TYPE_LIST',
                'title' => '日志类型',
                'type' => 'array',
                'value' => "login=登录\r\nconfig=配置\r\nuser=用户\r\nrole=角色\r\ngroup=部门\r\nposition=职位\r\napp=应用",
                'remark' => '日志的类别',
            ],
            // 日志操作类型
            [
                'name' => 'OPERATE_LOG_ACTION_TYPE',
                'title' => '日志操作类型',
                'type' => 'array',
                'value' => "add=新增\r\nedit=修改\r\ndelete=删除",
                'remark' => '操作日志的操作类型'
            ],
            // 操作日志内容模板
            [
                'name' => 'OPERATE_LOG_CONTENT_TEMPLATE',
                'title' => '操作日志内容模板',
                'type' => 'array',
                'value' => 'default={$action_type}了 {$model_name}【{$target_name}】 {$data}\r\ndelete=删除了 {$model_name}【{$target_name}】',
                'remark' => '操作日志数据内容展示的模板,{$operator}是操作人，{$action_type}是操作类型，{$model_name}是数据模型名称，{$target_name}是操作目标名称'
            ],
            // 扩展字段
            [
                'name' => 'EXTEND_FIELD_TABLE_NAME',
                'title' => '扩展字段的表列表',
                'type' => 'array',
                'value' => "tab_user=用户表\r\ntab_notify_node=消息节点表\r\ntab_content_permission=内容权限表",
            ],
            [
                'name' => 'EXTEND_FIELD_TYPE_LIST',
                'title' => '扩展字段类型列表',
                'type' => 'array',
                'value' => "string=单行文本\r\ntext=长文本\r\nboolean=枚举\r\ninteger=整数\r\nmoney=小数",
                'remark' => '系统会根据此类型建立数据字段：可以选择的类型有以下几种：pk=自增主键，bigpk=int20的自增主键等等',
            ],
            [
                'name' => 'EXTEND_FIELD_SHOW_TYPE_LIST',
                'title' => '扩展字段展示类型列表',
                'type' => 'array',
                'value' => "text=文本框\r\ntextarea=文本域\r\nselect=下拉框\r\nradio=单选组\r\ncheckbox=多选组\r\npassword=密码框\r\nuserGroupSelect=用户选择弹框\r\ncustom=自定义模板\r\n",
            ],
            // 批量操作
            [
                'name' => 'BATCH_OPERATION_TABLE',
                'title' => '批量操作的数据表',
                'type' => 'array',
                'value' => 'system\modules\user\models\User=用户表',
            ],
            [
                'name' => 'MOBILE_APP_LIST',
                'title' => '移动端入口',
                'type' => 'array',
                'value' => "mobile\r\nqywx\r\napp",
                'remark' => '用来对消息的url进行转换',
            ],
            [
                'name' => 'CACHE_VERSION',
                'title' => '缓存版本',
                'type' => 'number',
                'value' => '1', // 设置的值
                'remark' => 'json文件缓存版本，每次刷新缓存后自动+1'
            ],
        ],
        //拓展字段
        'extendsField' => [

        ],
        //菜单
        'menu' => [
            // 定义一级菜单, 注意定义一级菜单必须定义menu_id，menu_id 1000 以内系统预留
            [
                // 定义主菜单
                'menu_id' => 1,             // 菜单id
                'menu_name' => '内容',   // 菜单名称，
                'pid' => 0,                 // 父级
                'module' => '',             // 所属模块
                'path' => 'MainContent',    // 路径
                'icon' => 'fa fa-desktop',     // 图标
            ],
            [
                // 定义主菜单
                'menu_id' => 2,
                'menu_name' => '用户',
                'pid' => 0,
                'module' => '',
                'path' => 'MainUser',
                'icon' => 'fa fa-users',
            ],
            [
                // 定义主菜单
                'menu_id' => 3,
                'menu_name' => '系统',
                'pid' => 0,
                'module' => '',
                'path' => 'MainSetting',
                'icon' => 'fa fa-gears',
            ],
            [
                // 定义主菜单
                'menu_id' => 4,
                'menu_name' => '扩展',
                'pid' => 0,
                'module' => '',
                'path' => 'MainPlugs',
                'icon' => 'fa fa-plug',
            ],

            // 定义正常菜单，
            [
                'menu_id' => 1001, // 第一个模块的第一个菜单从1000开始，1000以内的菜单预留
                'menu_name' => '基本设置',
                'module' => 'main',
                'path' => 'system/modules/main',
                'pid' => 3,
                'icon' => 'fa fa-cogs',
                'children' => [
                    [
                        'menu_name' => '菜单管理',
                        'path' => 'main/menu/index',
                        'icon' => 'fa fa-users',
                        'children' => [
                            [
                                'menu_name' => '添加',
                                'path' => 'main/menu/add',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '修改',
                                'path' => 'main/menu/edit',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '删除',
                                'path' => 'main/menu/del',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '其他操作',
                                'path' => 'main/menu/ajax',
                                'type' => 1,
                            ],
                        ]
                    ],
                    [
                        'menu_name' => '系统配置',
                        'path' => 'main/config/index',
                        'icon' => 'fa fa-cog',
                        'children' => [
                            [
                                'menu_name' => '添加',
                                'path' => 'main/config/add',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '修改',
                                'path' => 'main/config/edit',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '删除',
                                'path' => 'main/config/delete',
                                'type' => 1,
                            ],
                        ]
                    ],
                    [
                        'menu_name' => '扩展字段',
                        'path' => 'main/extends-field/index',
                        'icon' => 'fa fa-circle-o-notch',
                        'children' => [
                            [
                                'menu_name' => '添加',
                                'path' => 'main/extends-field/add',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '修改',
                                'path' => 'main/extends-field/edit',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '删除',
                                'path' => 'main/extends-field/del',
                                'type' => 1,
                            ],
                        ]
                    ],
                    [
                        'menu_name' => '数据库升级',
                        'path' => 'main/migrate/index',
                        'icon' => 'fa fa-database',
                    ],
                    [
                        'menu_name' => '批量操作',
                        'path' => 'main/batch-operation/index',
                        'icon' => 'fa fa-files-o',
                        'children' => [
                            [
                                'menu_name' => '批量修改',
                                'path' => 'main/batch-operation/update',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '批量删除',
                                'path' => 'main/batch-operation/delete',
                                'type' => 1,
                            ],
                        ]
                    ],
                    [
                        'menu_name' => '模块管理',
                        'path' => 'main/modules/index',
                        'icon' => 'fa fa-th-large',
                        'children' => [
                            [
                                'menu_name' => '未安装模块',
                                'path' => 'main/modules/not-install',
                                'icon' => 'fa fa-th-large',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '模块安装',
                                'path' => 'main/modules/install',
                                'icon' => 'fa fa-th-large',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '编辑',
                                'path' => 'main/modules/edit',
                                'icon' => 'fa fa-th-large',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '卸载',
                                'path' => 'main/modules/uninstall',
                                'icon' => 'fa fa-th-large',
                                'type' => 1,
                            ],
                        ]
                    ],
                    [
                        'menu_name' => '代码生成器',
                        'path' => 'main/gii/index',
                        'icon' => 'fa fa-code',
                    ],
                ]
            ],
            [
                'menu_name' => '清理缓存',
                'path' => 'main/clear/index',
                'icon' => 'iconfont icon-shanchu',
                'pid' => 3,
            ],
            [
                'menu_name' => '系统日志',
                'module' => 'main',
                'path' => 'system/modules/main/log',
                'pid' => 3,
                'icon' => 'fa fa-file-text-o',
                'children' => [
                    [
                        'menu_name' => '操作日志',
                        'path' => 'main/log/operate',
                    ],
                    [
                        'menu_name' => '登录日志',
                        'path' => 'main/log/index',
                    ],
                    [
                        'menu_name' => '错误日志',
                        'path' => 'main/log/error',
                    ],
                ]
            ],
            [
                'menu_name' => '应用管理',
                'path' => 'system/modules/main/app',
                'module' => 'main',
                'pid' => 3,
                'icon' => 'fa fa-cubes',
                'children' => [
                    [
                        'menu_name' => '应用管理',
                        'path' => 'main/app/index',
                        'icon' => 'fa fa-cubes',
                        'children' => [
                            [
                                'menu_name' => '添加',
                                'path' => 'main/app/add',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '修改',
                                'path' => 'main/app/edit',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '删除',
                                'path' => 'main/app/del',
                                'type' => 1,
                            ],
                        ]
                    ],
                    [
                        'menu_name' => '应用分类',
                        'path' => 'main/app-category/index',
                        'icon' => 'fa fa-navicon',
                        'children' => [
                            [
                                'menu_name' => '添加',
                                'path' => 'main/app-category/add',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '修改',
                                'path' => 'main/app-category/edit',
                                'type' => 1,
                            ],
                            [
                                'menu_name' => '删除',
                                'path' => 'main/app-category/del',
                                'type' => 1,
                            ],
                        ]
                    ],
                ]
            ],
            [
                'menu_name' => '附件管理',
                'module' => 'fileinfo',
                'path' => 'system/main/file-info',
                'pid' => 3,
                'icon' => 'fa fa-file',
                'children' => [
                    [
                        'menu_name' => '附件列表',
                        'path' => 'main/file-info/index',
                        'icon' => 'fa fa-file-o'
                    ],
                ]
            ],
        ]
    ];

    public function up()
    {
        parent::up();
    }

    public function down()
    {
        parent::down();
//        echo " 主模块重要配置不能删除！\n";
//        return false;
    }
}
