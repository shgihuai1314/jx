<?php

namespace system\modules\main\migrations;

use console\models\Migration;

class M180313033906Create_table extends Migration
{
    // 所属模块
    public $module_id = 'main';

    // 更新说明
    public $description = '创建主模块相关表';

    public function up()
    {
        // 模块表
        $this->createTable('tab_modules', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'name' => $this->string()->notNull()->defaultValue('')->comment('名称'),
            'module_id' => $this->string()->notNull()->defaultValue('')->comment('模块标识'),
            'icon' => $this->string()->notNull()->defaultValue('')->comment('模块图标'),
            'version' => $this->string()->notNull()->defaultValue('')->comment('版本'),
            'describe' => $this->string()->notNull()->defaultValue('')->comment('描述'),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('默认0关闭，1开启'),
            'core' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否核心，0否，1是'),
            'author' => $this->string()->notNull()->defaultValue('')->comment('作者'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
            'update_at' => $this->integer()->notNull()->defaultValue(0)->comment('修改时间'),
        ]);

        // 扩展字段表
        $this->createTable('tab_extends_field', [
            'id' => $this->primaryKey(11)->notNull()->comment('流水'),
            'table_name' => $this->char(64)->notNull()->defaultValue('users')->comment('表名'),
            'field_name' => $this->char(50)->notNull()->defaultValue('')->comment('字段名'),
            'field_title' => $this->char(64)->notNull()->defaultValue('')->comment('字段展示名'),
            'field_type' => $this->char(64)->notNull()->defaultValue('')->comment('配置类型'),
            'show_type' => $this->char(64)->notNull()->defaultValue('')->comment('展示方式;'),
            'field_value' => $this->text()->comment('配置选项'),
            'template' => $this->text()->comment('模版'),
            'template_parameter' => $this->text()->comment('模版参数'),
            'is_null' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否null，0非，1是'),
            'default_value' => $this->string()->notNull()->defaultValue('')->comment('默认值'),
            'field_explain' => $this->string()->notNull()->defaultValue('')->comment('字段提示信息'),
            'is_must' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否必须；1必填，0非必填'),
            'is_show' => $this->smallInteger(1)->notNull()->defaultValue(1)->comment('是否显示；0不显示，1显示'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('排序'),
            'is_search' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否加入搜索项')
        ]);
        // 创建索引
        $this->createIndex('table_field', 'tab_extends_field', ['table_name', 'field_name'], true);

        // 系统配置表
        $this->createTable('tab_config', [
            'id' => $this->primaryKey(11)->notNull()->comment('配置ID'),
            'name' => $this->char(100)->notNull()->defaultValue('')->comment('配置名称'),
            'type' => $this->char(64)->notNull()->defaultValue('')->comment('配置类型'),
            'title' => $this->char(64)->notNull()->defaultValue('')->comment('配置说明'),
            'module' => $this->char(64)->notNull()->defaultValue('')->comment('所属模块'),
            'value' => $this->text()->comment('配置值'),
            'extra' => $this->string()->notNull()->defaultValue('')->comment('配置选项'),
            'remark' => $this->string()->notNull()->defaultValue('')->comment('配置说明'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('排序'),
            'create_time' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
            'update_time' => $this->integer()->notNull()->defaultValue(0)->comment('更新时间')
        ]);
        // 创建索引
        $this->createIndex('uk_name', 'tab_config', 'name', true);
        $this->createIndex('type', 'tab_config', 'type');
        $this->createIndex('module', 'tab_config', 'module');

        // 系统日志表
        $this->createTable('tab_log', [
            'log_id' => $this->primaryKey(20)->notNull()->comment('ID号'),
            'type' => $this->char(32)->notNull()->defaultValue('')->comment('日志类型；system，login，user等'),
            'target_id' => $this->integer()->notNull()->defaultValue(0)->comment('日志对应的目标id，比如用户id'),
            'target_id2' => $this->integer()->notNull()->defaultValue(0)->comment('日志对应的扩展目标id，比如用户id'),
            'content' => $this->text()->comment('消息内容'),
            'add_time' => $this->integer()->notNull()->defaultValue(0)->comment('操作时间'),
            'ip' => $this->char(64)->notNull()->defaultValue('')->comment('操作ip'),
            'user_id' => $this->integer()->notNull()->defaultValue(0)->comment('创建人id'),
        ]);

        // 错误日志表
        $this->createTable('tab_log_error', [
            'id' => $this->primaryKey(20),
            'level' => $this->integer(),
            'category' => $this->string(),
            'log_time' => $this->double(),
            'prefix' => $this->text(),
            'message' => $this->text(),
        ]);
        $this->createIndex('idx_log_level', 'tab_log_error', 'level');
        $this->createIndex('idx_log_category', 'tab_log_error', 'category');

        // 操作日志表
        $this->createTable('tab_operate_log', [
            'id' => $this->primaryKey(11)->comment('日志ID'),
            'action_type' => $this->char(32)->notNull()->comment('操作类型(add,edit,delete)'),
            'module' => $this->char(32)->comment('模块名称'),
            'target_name' => $this->char(64)->comment('操作目标'),
            'target_id' => $this->integer()->comment('操作ID'),
            'template' => $this->text()->comment('显示模板'),
            'data' => $this->text()->comment('日志数据'),
            'content' => $this->text()->comment('日志内容'),
            'type' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('数据类型(0:格式数据;1:文本数据)'),
            'model_class' => $this->string()->comment('模型类'),
            'operator' => $this->integer()->notNull()->comment('操作人'),
            'opt_time' => $this->integer()->notNull()->comment('操作时间'),
            'opt_ip' => $this->char(32)->notNull()->comment('操作人IP'),
        ]);

        // 后台菜单表
        $this->createTable('tab_menu', [
            'menu_id' => $this->primaryKey(11)->notNull()->comment('菜单ID'),
            'menu_name' => $this->char(32)->notNull()->comment('菜单名称'),
            'pid' => $this->integer()->notNull()->defaultValue(0)->comment('上级菜单'),
            'module' => $this->char(32)->comment('所属模块'),
            'path' => $this->char(64)->comment('路径'),
            'icon' => $this->char(32)->comment('图标'),
            'type' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('类型(0:菜单;1:操作)'),
            'is_show' => $this->smallInteger(1)->notNull()->defaultValue(1)->comment('是否显示；0不显示，1显示'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('排序'),
        ]);

        $this->createTable('tab_options', [
            'name' => $this->string()->notNull()->defaultValue('')->comment('键'),
            'value' => $this->text(),
        ]);
        $this->createIndex('key', 'tab_options', 'name', true);

        // 应用管理表
        $this->createTable('tab_app', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'name' => $this->string()->notNull()->defaultValue('')->comment('应用名称'),
            'url' => $this->string()->notNull()->defaultValue('')->comment('设置应用主页'),
            'image' => $this->string()->notNull()->defaultValue('')->comment('logo'),
            'content' => $this->text()->comment('应用说明'),
            'use_range' => $this->text()->comment('使用范围'),
            'is_show' => $this->smallInteger(1)->notNull()->defaultValue(1)->comment('是否启用;0不启用，1启用，默认1'),
            'is_hot' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否热门，1是0非'),
            'is_recommend' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否推荐，1是0非'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('排序'),
            'created_at' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
            'update_at' => $this->integer()->notNull()->defaultValue(0)->comment('更新时间'),
            'created_by' => $this->integer()->notNull()->defaultValue(0)->comment('创建人'),
            'update_by' => $this->integer()->notNull()->defaultValue(0)->comment('更新人'),
        ]);

        // 应用关系表
        $this->createTable('tab_app_relation', [
            'id' => $this->primaryKey(11)->notNull(),
            'cate_id' => $this->integer()->notNull()->comment('分类id'),
            'app_id' => $this->integer()->notNull()->comment('应用id'),
        ]);
        $this->createIndex('cate_id', 'tab_app_relation', 'cate_id');
        $this->createIndex('app_id', 'tab_app_relation', 'app_id');

        // 应用分类表
        $this->createTable('tab_app_category', [
            'id' => $this->primaryKey(11)->notNull()->comment('分类id'),
            'name' => $this->char(100)->notNull()->defaultValue('')->comment('分类名称'),
            'image' => $this->string()->notNull()->defaultValue('')->comment('分类图标'),
            'pid' => $this->integer()->notNull()->defaultValue(0)->comment('上级分类'),
            'path' => $this->string()->notNull()->defaultValue('')->comment('结构路径'),
            'code' => $this->string()->notNull()->defaultValue('')->comment('代号'),
            'is_display' => $this->smallInteger(1)->notNull()->defaultValue(1)->comment('是否显示；0不显示，1显示，默认1'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('排列'),
        ]);

        // 附件信息表
        $this->createTable('tab_fileinfo', [
            'file_id' => $this->primaryKey(11)->notNull()->comment('附件ID'),
            'file_type' => $this->char(32)->notNull()->comment('附件类型'),
            'name' => $this->char(128)->notNull()->comment('附件名称'),
            'src' => $this->string()->notNull()->comment('路径'),
            'source' => $this->char(32)->notNull()->comment('附件来源'),
            'size' => $this->char(32)->comment('附件大小'),
            'upload_time' => $this->integer()->notNull()->defaultValue(0)->comment('上传时间'),
            'upload_user' => $this->char(32)->notNull()->comment('上传人'),
            'is_del' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('是否删除(0:未删除;1:已删除)'),
        ]);

        // 点击日志表
        $this->createTable('tab_click_log', [
            'id' => $this->primaryKey(11)->notNull(),
            'target_type' => $this->string()->notNull()->defaultValue('')->comment('自定义节点'),
            'target_id' => $this->integer()->notNull()->defaultValue(0)->comment('节点的唯一标识'),
            'data_time' => $this->integer()->notNull()->defaultValue(0)->comment('该记录的日期，以天为划分'),
            'number' => $this->integer()->notNull()->defaultValue(0)->comment('点击数'),
        ]);
        $this->createIndex('target_id', 'tab_click_log', 'target_type');

        // 点击总数表
        $this->createTable('tab_click_total', [
            'total_id' => $this->primaryKey(11)->notNull(),
            'target_type' => $this->string()->notNull()->defaultValue('')->comment('自定义节点'),
            'target_id' => $this->integer()->notNull()->defaultValue(0)->comment('节点的唯一标识'),
            'click_total' => $this->integer()->notNull()->comment('点击总数'),
        ]);
        $this->createIndex('target_id', 'tab_click_total', 'target_type');

        // 点击统计表
        $this->createTable('tab_click_count', [
            'count_id' => $this->primaryKey(11)->notNull()->comment('统计表id'),
            'target_type' => $this->string()->notNull()->defaultValue('')->comment('自定义节点'),
            'target_id' => $this->integer()->notNull()->defaultValue(0)->comment('节点唯一标识'),
            'count_type' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('统计类型  1周 2月 3年 4自定义'),
            'start_at' => $this->integer()->notNull()->defaultValue(0)->comment('统计开始时间'),
            'end_at' => $this->integer()->notNull()->defaultValue(0)->comment('统计截止时间'),
            'click_count' => $this->integer()->notNull()->defaultValue(0)->comment('统计点击量'),
        ]);
        $this->createIndex('target_id', 'tab_click_count', 'target_type');

        $this->createTable('tab_cpu_record', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'cpu' => $this->text()->comment('cpu负载率'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('添加时间'),
        ]);
        $this->createTable('tab_load_record', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'load' => $this->text()->comment('系统负载率'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('添加时间'),
        ]);
        $this->createTable('tab_memory_record', [
            'id' => $this->primaryKey(11)->notNull()->comment('id'),
            'memory' => $this->text()->comment('运行内存'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('添加时间'),
        ]);
    }

    public function down()
    {
        echo " 主模块相关表不能删除！\n";
        return false;
    }
}
