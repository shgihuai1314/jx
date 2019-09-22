<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/14
 * Time: 19:09
 */

/** @var yii\web\View $this */
/** @var \system\modules\main\models\AppCategory $model */
/** @var yii\bootstrap\ActiveForm $form */

use yii\helpers\Url;

$this->title = $model->isNewRecord ? '添加分类' : '编辑分类: ' . $model->name;
?>

<div class="layui-tab layui-tab-brief">
	<ul class="layui-tab-title clearfix">
		<li><a href="<?= \yii\helpers\Url::toRoute('index')?>">分类列表</a></li>
		<?php if ($model->isNewRecord) : ?>
			<li class="layui-this"><?= $this->title ?></li>
		<?php else : ?>
			<li><a href="<?= \yii\helpers\Url::toRoute('add')?>">添加分类</a></li>
			<li class="layui-this"><?= $this->title ?></li>
		<?php endif; ?>
	</ul>
</div>
<div class="layui-row">
    <?= system\widgets\FormViewWidget::widget([
        'model' => $model,
        'fields' => [
            'name' => [
                'required' => true,
            ],
            'pid' => [
                'type' => 'widget',
                'class' => \system\widgets\ZTreeWidget::className(),
                'config' => [
                    'divId' => 'appcategoryTree',
                    'divOption' => 'style="padding: 8px; border: 1px solid #e6e6e6; max-height: 200px; overflow-y: auto;"',
                    'getUrl' => ['ajax', 'action' => 'get-nodes'],
                    'isExpand' => false
                ]
            ],
            'image' => [
                'type' => 'widget',
                'class' => \system\modules\main\widgets\FileUploadWidget::className(),
                'config' => [
                    'flag' => 1,
                ],
            ],
            'code',
            'sort' => [
                'box' => 'layui-col-xs6',
                'type' => 'number',
                'required' => true,
            ],
            'is_display' => [
                'type' => 'radio',
                'box' => 'layui-col-xs6',
            ],
        ],
    ]) ?>
</div>
<script>

</script>