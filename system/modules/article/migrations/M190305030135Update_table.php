<?php

namespace system\modules\article\migrations;

use console\models\Migration;

class M190305030135Update_table extends Migration
{
    // 所属模块
    public $module_id = 'article';

    // 更新说明
    public $description = '修改文章模块表数据';

    // 版本号
    public $version = '1.0';

    public function up()
    {
        $this->addColumn('tab_article_category', 'create_by',
            $this->integer()->notNull()->defaultValue(0)->comment('创建人'));
        $this->addColumn('tab_article_category', 'create_at',
            $this->integer()->notNull()->defaultValue(0)->comment('创建时间'));
        $this->addColumn('tab_article_category', 'update_by',
            $this->integer()->notNull()->defaultValue(0)->comment('更新人'));
        $this->addColumn('tab_article_category', 'update_at',
            $this->integer()->notNull()->defaultValue(0)->comment('更新时间'));

        $this->createIndex('is_display', 'tab_article_category', 'is_display');

        $this->renameTable('tab_article_content','tab_article');

        $this->addColumn('tab_article', 'update_by',
            $this->integer()->notNull()->defaultValue(0)->comment('更新人'));
        $this->addColumn('tab_article', 'update_at',
            $this->integer()->notNull()->defaultValue(0)->comment('更新时间'));

        $this->renameColumn('tab_article','category_id','cate_id');

        $this->createIndex('cate_id', 'tab_article', 'cate_id');
    }

    public function down()
    {
        parent::down();
    }
}
