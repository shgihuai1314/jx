<?php

/** @var yii\web\View $this */
/** @var array $data */

use yii\helpers\Html;
use yii\helpers\Url;
use system\modules\user\models\User;

// 标题
$this->title = "分类列表";
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this"><?= $this->title ?></li>
        <li><a href="<?= Url::toRoute('add') ?>">添加分类</a></li>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'data' => $data,
        'model' => \system\modules\article\models\ArticleCategory::className(),
        'search' => [
            'items' => [
                [
                    'name' => 'search',
                    'class' => 'width-300',
                    'type' => 'input',
                    'placeholder' => '请输入',
                ],
            ],
        ],
        'columns' => [
            'title' => ['edit'],
            'code' => [100],
            'is_link' => [100, 'checkbox'],
            'target' => [100],
            'is_display' => [100, 'checkbox'],
            'sort' => [100],
            'create_by' => [100, 'custom' => function($val) {
                return User::getInfo($val);
            }],
            'create_at' => [150, 'custom' => function($val) {
                return date('Y-m-d', $val);
            }],
            [
                'type' => 'operate',
                'button' => [
                    'edit',
                    'del',
                ],
            ],
        ],
        'batchBtn' => [
            'del'
        ]
    ]) ?>
</div>