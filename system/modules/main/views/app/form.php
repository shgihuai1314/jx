<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/14
 * Time: 19:09
 */

/** @var yii\web\View $this */
/** @var \system\modules\main\models\App $model */
/** @var yii\bootstrap\ActiveForm $form */

use yii\helpers\Url;
use \yii\helpers\ArrayHelper;
$this->title = $model->isNewRecord ? '添加应用' : '编辑应用: ' . $model->name;
$category_id = $model->category ? implode(',',ArrayHelper::getColumn(ArrayHelper::toArray($model->category),'cate_id')) : 0;
?>

<div class="layui-tab layui-tab-brief">
	<ul class="layui-tab-title clearfix">
		<li><a href="<?= \yii\helpers\Url::toRoute('index')?>">应用列表</a></li>
		<?php if ($model->isNewRecord) : ?>
			<li class="layui-this"><?= $this->title ?></li>
		<?php else : ?>
			<li><a href="<?= \yii\helpers\Url::toRoute('add', ['category_id' => $category_id])?>">添加应用</a></li>
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
            'category_id' => [
                'type' => 'widget',
                'class' => \system\widgets\ZTreeWidget::className(),
                'config' => [
                    'divId' => 'menu',
                    'isMulti' => true,
                    'divOption' => 'style="padding: 8px; border: 1px solid #e6e6e6; max-height: 500px; overflow-y: auto;"',
                    'getUrl' => ['/main/app-category/ajax', 'action' => 'get-nodes'],
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
            'url',
            'is_show' => [
                'type' => 'radio',
                'box' => 'layui-col-xs6'
            ],
            'is_hot' => [
                'type' => 'radio',
                'box' => 'layui-col-xs6'
            ],
            'is_recommend' => [
                'type' => 'radio',
                'box' => 'layui-col-xs6'
            ],
            'use_range' => [
                'type' => 'input',
                'class' => 'user-group-select',
            ],
            'content' => [
                'type' => 'widget',
                'style' => 'min-height: 360px;'
            ],
            'sort' => [
                'type' => 'number',
                'required' => true,
            ],
        ],
    ]) ?>
</div>