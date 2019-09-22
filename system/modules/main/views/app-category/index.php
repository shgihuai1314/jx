<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/23
 * Time: 18:57
 */

/** @var \yii\web\View $this */
/** @var \system\modules\main\models\AppCategory $model */
/** @var array $list */
/** @var array $params */
/** @var integer $id */
/** @var \yii\data\Pagination $pagination */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

// 权限
$canAdd = Yii::$app->user->can('main/app-category/add');
$canEdit = Yii::$app->user->can('main/app-category/edit');
$canDel = Yii::$app->user->can('main/app-category/delete');
$this->title = '应用分类';
?>
<style>
    .custom-table td .layui-table-cell {height: 64px; line-height: 64px;}
</style>
<div class="layui-tab layui-tab-brief">
	<ul class="layui-tab-title">
		<li class="layui-this"><?= $this->title ?></li>
		<?php if ($canAdd): ?>
			<li><a href="<?= Url::toRoute(['add']) ?>">添加分类</a></li>
		<?php endif; ?>
	</ul>
</div>
<div class="layui-row">
    <div style="float: left; width: 270px; margin-right: -270px; position: relative;">
		<script>
            var onSelect = function (event, treeId, treeNode) {
                if (treeNode != null && treeNode.id != '<?= $id ?>') {
                    window.location.href = "<?= Url::to(['index', 'id' => '']) ?>" + treeNode.id
                }
            }
		</script>
		<?= \system\widgets\ZTreeWidget::widget([
			'divId' => 'category',
			'note_id' => $id,
			'getUrl' => ['ajax', 'action' => 'get-nodes'],
			'divOption' => 'style="padding: 0 10px;"',
			'onSelect' => 'onSelect',
		]) ?>
	</div>
    <div style="float: right; width: 100%">
        <div style="margin-left: 270px;">
            <?= \system\widgets\GridViewWidget::widget([
                'data' => $list,//数据
                'model' => '\system\modules\main\models\AppCategory',//模型类
                'params' => $params,//参数
                'search' => [//查询字段
                    'items' => [
                        [
                            'type' => 'hidden',
                            'name' => 'id',
                        ],
                        [
                            'class' => 'width-240',
                            'type' => 'input',
                            'name' => 'name',
                            'label' => '',
                            'placeholder' => '输入关键字搜索',
                        ],
                    ],
                ],//查询
                'columns' => [//列表信息
                    [
                        'field' => 'image',
                        'width' => 120,
                        'custom' =>
                            function ($field) {
                                return $field?Html::img($field, ['style' => 'width:60px;height: 60px;']):'';
                            },
                    ],
                    'name' => [120, 'edit'],
                    'pid',
                    'code' => [120, 'edit'],
                    'is_display' => [100, 'checkbox'],
                    'sort' => [100, 'edit'],
                    [
                        'type' => 'operate',
                        'button' => [
                            'edit' => $canEdit,
                            'del' => $canDel,
                        ],
                    ],
                ],//列参数
                'batchBtn' => ['del'],//批处理按钮
                'pagination' => $pagination,//分页
            ]); ?>
        </div>
	</div>
</div>