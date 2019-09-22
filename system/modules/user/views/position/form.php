<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/13
 * Time: 下午1:49
 */

/** @var \yii\web\View $this */
/** @var \system\modules\user\models\Position $model */

use yii\helpers\Url;

$this->title = $model->isNewRecord ? '新增职位' : '编辑职位：' . $model->name;
?>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li><a href="<?= Url::toRoute('index') ?>">角色列表</a></li>
        <li class="layui-this"><?= $this->title ?></li>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\FormViewWidget::widget([
        'model' => $model,
        'fields' => [
            'name' => [
                'required' => true,
            ],
            'sort' => ['type' => 'number'],
        ]
    ])?>
</div>
