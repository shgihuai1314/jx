<?php

/** @var yii\web\View $this */
/** @var array $data */

use yii\helpers\Html;
use yii\helpers\Url;

// 标题
$this->title = "文章列表";
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this"><?= $this->title ?></li>
        <li><a href="<?= Url::toRoute('add') ?>">添加文章</a></li>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'data' => $data,
        'model' => \system\modules\article\models\Article::className(),
        'search' => [
            'items' => [
                // 搜索项设置详情请查看文档
                [
                    'name' => 'search',
                    'class' => 'width-300',
                    'type' => 'input',
                    'placeholder' => '请输入',
                ],
            ],
        ],
        'columns' => [
            // 列表字段设置详情请查看文档
            'title' => ['minwidth' => 150, 'edit'],
            'category_id' => [100, 'custom' => function($arr) {
                return $arr['category']['title'] ? $arr['category']['title'] : '无';
            }, 'paramsType' => 'array'],
            'is_display' => [100, 'checkbox'],
            'author' => [100],
            'click_num' => [100],
            [
                'type' => 'operate',
                'button' => ['edit', 'del'],
            ],
        ],
        'batchBtn' => ['del']
    ]) ?>
</div>