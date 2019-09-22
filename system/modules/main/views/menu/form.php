<?php

/** @var yii\web\View $this */
/** @var \system\modules\main\models\Menu $model */

use yii\helpers\ArrayHelper;
use yii\helpers\Url;

$id = $model->pid;
$this->title = $model->isNewRecord ? '添加菜单' : '编辑菜单: ' . $model->menu_name;

$operate = '';
if (is_array($model->operate)) {
	foreach ($model->operate as $val) {
		$operate .= trim(ArrayHelper::getValue($val, 'action', '')) . '=' . trim(ArrayHelper::getValue($val, 'name', '')) . "\r\n";
	}
}
?>
<div class="layui-tab layui-tab-brief">
	<ul class="layui-tab-title">
		<li><a href="<?= Url::to(['index', 'id' => $id])?>">菜单管理</a></li>
		<?php if ($model->isNewRecord) : ?>
			<li class="layui-this"><?= $this->title ?></li>
		<?php else : ?>
			<li><a href="<?= Url::to(['add', 'pid' => $id])?>">添加菜单</a></li>
			<li class="layui-this"><?= $this->title ?></li>
		<?php endif; ?>
	</ul>
</div>

<div class="layui-row">
	<?= system\widgets\FormViewWidget::widget([
		'model' => $model,
		'action' => $model->isNewRecord ? ['add', 'pid' => $model->pid] : ['edit', 'id' => $model->menu_id],
		'fields' => [
			'menu_name' => [
				'required' => true,
			],
			'pid' => [
				'type' => 'widget',
				'class' => \system\widgets\ZTreeWidget::className(),
				'config' => [
					'divId' => 'menu',
					'divOption' => 'style="padding: 8px; border: 1px solid #e6e6e6; max-height: 200px; overflow-y: auto;"',
					'getUrl' => ['ajax', 'action' => 'get-nodes'],
					'isExpand' => false
				]
			],
			'module',
			'path',
			'icon' => [
                'type' => 'widget',
                'class' => \system\modules\main\widgets\IconSelectWidget::className(),
                'hint' => '字体大小、颜色会根据样式自动调整'
            ],
			'is_show' => 'radio',
			'operate' => [
				'type' => 'textarea',
				'value' => $operate
			],
			'sort' => [
				'type' => 'number',
				'required' => true,
			],
		],
		'backUrl' => Url::to(['index', 'id' => $id])
	]) ?>
</div>