<?php

/** @var \yii\web\View $this */
/** @var \system\modules\user\models\User $model */


use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $model->isNewRecord ? '新增用户' : '修改用户：' . $model->username;

?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li><a href="<?= Url::toRoute('index')?>">用户列表</a></li>
        <li class="layui-this"><?= $this->title ?></li>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\FormViewWidget::widget([
        'model' => $model,
        'fields' => [

        ]
    ])?>
</div>