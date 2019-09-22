<?php

/** @var yii\web\View $this */
/** @var system\modules\article\models\Article $model */

use yii\helpers\Html;
use yii\helpers\Url;

// 标题
$this->title = $model->isNewRecord ? '添加文章' : '编辑文章';
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li><a href="<?= Url::toRoute('index')?>">列表</a></li>
        <li class="layui-this"><?= $this->title ?></li>
    </ul>
</div>

<div class="layui-row">
    <?= system\widgets\FormViewWidget::widget([
        'model' => $model,
        'fields' => [
            // 表单字段设置详情请查看文档
            'title' => [
                'required' => true
            ],
            'category_id' => [
                'type' => 'widget',
                'class' => \system\widgets\ZTreeWidget::className(),
                'config' => [
                    'divId' => 'menu',
                    'divOption' => 'style="padding: 8px; border: 1px solid #e6e6e6; max-height: 200px; overflow-y: auto;"',
                    'getUrl' => ['category/ajax', 'action' => 'get-nodes'],
                    'isExpand' => false,
                ]

            ],
            'image' => [
                'type' => 'widget',
                'class' => \system\modules\main\widgets\FileUploadWidget::className(),
                'config' => [
                    'item' => [
                        'accept' => 'images',
                        'multiple' => false,
                        'btnId' => 'image-btn',
                    ],
                ],
                'hint' => '图片可以展示在文章列表中，有助于读者对文章的了解',
            ],
            'summary' => [
                'type' => 'textarea'
            ],
            'content' => [
                'type' => 'widget',
            ],
            'author' => [
                'box' => 'layui-col-xs6',
            ],
            'sort' => [
                'box' => 'layui-col-xs6',
            ],
            'recommend_sort',
            'is_display' => [
                'type' => 'radio',
            ],

            'file' => [
                'type' => 'widget',
                'class' => \system\modules\main\widgets\FileUploadWidget::className(),
                'config' => [
                    'item' => [
                        'accept' => 'file',
                        'multiple' => true,
                        'btnId' => 'files-btn',
                    ],
                ],
            ],
        ],
    ]) ?>
</div>
