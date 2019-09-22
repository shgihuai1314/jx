<?php

namespace system\modules\article\migrations;

use console\models\Migration;

/**
 * Handles the creation of table `tab_article`.
 */
class M180828091819Create_tab_article_table extends Migration
{
    // 所属模块
    public $module_id = 'article';

    // 更新说明
    public $description = '创建文章模块的文章表和分类表';

    // 版本号
    public $version = '1.0';

    /**
     * @inheritdoc
     */
    public function up()
    {
        //文章分类
        $this->createTable('tab_article_category', [
            'id' => $this->primaryKey(11)->notNull()->comment('分类id'),
            'code' => $this->char(64)->notNull()->defaultValue('')->comment('分类代码'),
            'name' => $this->char(64)->notNull()->defaultValue('')->comment('分类名称'),
            'icon' => $this->char(128)->notNull()->defaultValue('')->comment('分类图标'),
            'pid' => $this->integer()->notNull()->defaultValue(0)->comment('上级分类'),
            'path' => $this->string()->notNull()->defaultValue('')->comment('结构路径'),
            'is_display' => $this->smallInteger(1)->notNull()->defaultValue(1)->comment('是否显示'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('排序'),
        ]);

        //索引
        $this->createIndex('parent_id', 'tab_article_category', 'pid');
        $this->createIndex('code', 'tab_article_category', 'code',true);

        //文章详情
        $this->createTable('tab_article_content', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull()->defaultValue('')->comment('文章标题'),
            'content' => $this->text()->comment('文章内容'),
            'category_id' => $this->integer()->notNull()->defaultValue(0)->comment('栏目ID'),
            'is_display' => $this->boolean()->notNull()->defaultValue(1)->comment('是否显示(0:否;1:是)'),
            'sort' => $this->integer()->notNull()->defaultValue(0)->comment('排序'),
            'author' => $this->char(64)->notNull()->defaultValue('')->comment('作者'),
            'is_recommend' => $this->boolean()->notNull()->defaultValue(0)->comment('是否推荐(0:否;1:是)'),
            'create_by' => $this->integer()->notNull()->defaultValue(0)->comment('创建人'),
            'create_at' => $this->integer()->notNull()->defaultValue(0)->comment('创建时间'),
            'is_del' => $this->boolean()->notNull()->defaultValue(0)->comment('是否删除(0:否;1:是)'),
        ]);

        //索引
        $this->createIndex('is_display', 'tab_article_content', 'is_display');
        $this->createIndex('is_del', 'tab_article_content', 'is_del');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('tab_article_content');
        $this->dropTable('tab_article_category');
    }
}
