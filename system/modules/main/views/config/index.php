<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/7
 * Time: 上午10:13
 */

/** @var yii\web\View $this */
/** @var array $data */

use yii\helpers\Html;
use yii\helpers\Url;

// 权限
$canAdd = Yii::$app->user->can('main/config/add');
$canEdit = Yii::$app->user->can('main/config/edit');
$canDel = Yii::$app->user->can('main/config/del');
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this">配置列表</li>
        <?php if ($canAdd): ?>
            <li><a href="<?= Url::toRoute('add') ?>">新增配置</a></li>
        <?php endif; ?>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'parseData' => ['height' => 'full-200'],
        'data' => $data,
        'model' => \system\modules\main\models\Config::className(),
        'search' => [
            'items' => [
                [
                    'name' => 'search',
                    'class' => 'width-300',
                    'type' => 'input',
                    'placeholder' => '请输入',
                ],
                [
                    'name' => 'module',
                    'class' => 'width-180',
                    'type' => 'select',
                    'prompt' => '请选择',
                ],
            ],
        ],
        'columns' => [
            'name' => [300, 'align' => 'left'],
            'title' => ['minWidth' => 200],
            'module' => [150],
            'type' => [150],
            [
                'type' => 'operate',
                'button' => [
                    'edit' => $canEdit,
                    'del' => $canDel,
                ],
            ],
        ],
    ]) ?>
</div>
