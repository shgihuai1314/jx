<?php

/** @var yii\web\View $this */
/** @var system\modules\article\models\ArticleCategory $model */

use yii\helpers\Html;
use yii\helpers\Url;

// 标题
$this->title = $model->isNewRecord ? '添加分类' : '编辑分类';
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li><a href="<?= Url::toRoute('index')?>">分类列表</a></li>
        <li class="layui-this"><?= $this->title ?></li>
    </ul>
</div>

<div class="layui-row">
    <?= system\widgets\FormViewWidget::widget([
        'model' => $model,
        'fields' => [
            // 表单字段设置详情请查看文档
            'title',
            'code',
            'image' => [
                'type' => 'widget',
                'class' => \system\modules\main\widgets\FileUploadWidget::className(),
                'config' => [
                    'item' => [
                        'accept' => 'images',
                    ]
                ]
            ],
            'pid' => [
                'type' => 'widget',
                'class' => \system\widgets\ZTreeWidget::className(),
                'config' => [
                    'divId' => 'menu',
                    'divOption' => 'style="padding: 8px; border: 1px solid #e6e6e6; max-height: 200px; overflow-y: auto;"',
                    'getUrl' => ['ajax', 'action' => 'get-nodes'],
                    'isExpand' => false,
                ]
            ],
            'is_link' => 'radio',
            [
                'div-box' => 'is_link',
                'filter' => [
                    'is_link' => 0
                ],
                'fields' => [
                    'url',
                    'target' => 'radio'
                ]
            ],
            'is_display' => [
                'type' => 'radio',
                'box' => 'layui-col-xs6',
            ],
            'sort' => [
                'box' => 'layui-col-xs6',
            ],
        ],
    ]) ?>
</div>