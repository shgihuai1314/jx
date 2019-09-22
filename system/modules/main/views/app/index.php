<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/23
 * Time: 18:57
 */

/** @var \yii\web\View $this */
/** @var \system\modules\main\models\App $model */
/** @var array $list */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$category_id = Yii::$app->request->get('category_id', 0);

// 权限
$canAdd = Yii::$app->user->can('main/app/add');
$canEdit = Yii::$app->user->can('main/app/edit');
$canDel = Yii::$app->user->can('main/app/delete');

$this->title = '应用分类';
?>
<style>
    .custom-table td .layui-table-cell {height: 64px; line-height: 64px;}
</style>
<div class="layui-tab layui-tab-brief">
	<ul class="layui-tab-title">
		<li class="layui-this"><?= $this->title ?></li>
		<?php if ($canAdd): ?>
			<li><a href="<?= Url::toRoute(['add', 'category_id' => $category_id]) ?>">添加应用</a></li>
		<?php endif; ?>
	</ul>
</div>
<div class="layui-row">
    <div style="float: left; width: 270px; margin-right: -270px; position: relative;">
		<script>
            var onSelect = function (event, treeId, treeNode) {
                if (treeNode != null && treeNode.id != '<?= $category_id ?>') {
                    window.location.href = "<?= Url::to(['index', 'category_id' => '']) ?>" + treeNode.id
                }
            }
		</script>
		<?= \system\widgets\ZTreeWidget::widget([
			'divId' => 'category',
			'note_id' => $category_id,
			'getUrl' => ['/main/app-category/ajax', 'action' => 'get-nodes'],
			'divOption' => 'style="padding: 0 10px;"',
			'onSelect' => 'onSelect',
		]) ?>
	</div>
    <div style="float: right; width: 100%">
        <div style="margin-left: 270px;">
            <?= \system\widgets\GridViewWidget::widget([
                'data' => $list,//数据
                'model' => '\system\modules\main\models\App',//模型类
                'search' => [//查询字段
                    'items' => [
                        [
                            'type' => 'hidden',
                            'name' => 'category_id',
                        ],
                        [
                            'class' => 'width-240',
                            'type' => 'input',
                            'name' => 'search',
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
                                return Html::img($field, ['style' => 'width:60px;height: 60px;']);
                            },
                    ],
                    'name' => [100, 'edit'],
                    [
                        'field' => 'use_range',
                        'width' => 150,
                        'custom' => function ($field) {
                            return \system\modules\user\components\UserWithGroup::getNamesBySelect($field);
                        },
                    ],
                    [
                        'field' => 'content',
                        'minWidth' => 200,
                        'custom' => function ($field) {
                            return \yii\helpers\StringHelper::truncate($field, 28);
                        },
                    ],
                    'is_show' => [100, 'checkbox'],
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
            ]); ?>
	    </div>
	</div>
</div>