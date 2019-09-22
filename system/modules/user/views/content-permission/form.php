<?php

/** @var yii\web\View $this */
/** @var \system\modules\user\models\ContentPermission $model */

$label = $model->attributeLabels();
$this->title = $model->isNewRecord ? '添加内容权限' : '编辑内容权限';
?>
<style>
    .layui-col-lg7, .layui-col-lg5 { max-width: 1000px; width: 100%;}
</style>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li><a href="<?= \yii\helpers\Url::toRoute('index') ?>">内容权限列表</a></li>
        <?php if ($model->isNewRecord): ?>
            <li class="layui-this">新增权限</li>
        <?php else: ?>
            <li class="layui-this">修改权限</li>
        <?php endif; ?>
    </ul>
</div>

<?= \system\widgets\FormViewWidget::widget([
	'model' => $model,
	'action' => $model->isNewRecord ? ['create'] : ['update', 'id' => $model->id],
	'fields' => [
		'user_id' => [
			'class' => 'user-group-select',
			'options' => [
				'data-select_type' => 'user',
				'data-select_max' => '1'
			]
		]
	]
])?>