<?php
/**
 * 用户列表
 * User: ligang
 * Date: 2017/3/7
 * Time: 上午10:13
 */

/** @var \yii\web\View $this */
/** @var array $data */

use system\modules\user\models\Position;
use yii\helpers\Url;

// 搜索关键字
$position = Position::getPosition();

// 权限判断
$canAdd = Yii::$app->user->can('user/position/add');
$canEdit = Yii::$app->user->can('user/position/edit');
$canDel = Yii::$app->user->can('user/position/delete');
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this">所有职位</li>
        <?php if ($canAdd): ?>
            <li><a href="<?= Url::toRoute('add') ?>">新增职位</a></li>
        <?php endif; ?>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'data' => $data,
        'model' => Position::className(),
        'search' => [
            'items' => [
                [
                    'class' => 'width-300',
                    'name' => 'name',
                    'label' => '',
                    'placeholder' => '请输入'
                ]
            ]
        ],
        'columns' => [
            ['type' => 'ID'],
            'name',
            'sort' => [150, 'edit'],
            [
                'label' => '在职人数',
                'width' => 100,
                'custom' => function ($val) use ($position) {
                    return isset($position[$val['id']]) ? $position[$val['id']] : 0;
                }
            ],
            [
                'type' => 'operate',
                'button' => [
                    'edit' => $canEdit,
                    'del' => $canDel
                ]
            ]
        ]
    ]) ?>
</div>