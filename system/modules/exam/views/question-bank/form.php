<?php

/** @var yii\web\View $this */
/** @var system\modules\payment\models\PayTradeDetail $model */

use yii\helpers\Html;
use yii\helpers\Url;

// 标题
$this->title = $model->isNewRecord ? '添加' : '编辑';
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li><a href="<?= Url::toRoute('index') ?>">列表</a></li>
        <li class="layui-this"><?= $this->title ?></li>
    </ul>
</div>

<div class="layui-row">
    <?= system\widgets\FormViewWidget::widget([
        'model' => $model,
        'fields' => [
            'name' => [
                'required' => true,
            ],

            'is_question_bank' => 'radio',
        ],
    ]) ?>
</div>
