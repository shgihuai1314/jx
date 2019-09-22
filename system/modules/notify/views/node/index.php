<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/7
 * Time: 上午10:13
 */

use yii\helpers\Url;

$canAdd = Yii::$app->user->can('notify/node/add');
$canEdit = Yii::$app->user->can('notify/node/edit');
$canDel = Yii::$app->user->can('notify/node/del');
?>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title clearfix">
        <li class="layui-this">节点列表</li>
        <?php if ($canAdd): ?>
            <li><a href="<?= Url::toRoute('add') ?>">新增节点</a></li>
        <?php endif; ?>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'parseData' => ['height' => 'full-200'],
        'data' => $data,
        'model' => \system\modules\notify\models\NotifyNode::className(),
        'search' => [
            'items' => [
                [
                    'name' => 'name',
                    'class' => 'width-210',
                    'type' => 'input',
                    'label' => '策略名称',
                    'placeholder' => '策略名称',
                ],
                [
                    'name' => 'module',
                    'class' => 'width-180',
                    'type' => 'select',
                    'prompt' => '请选择',
                ],
            ]
        ],
        'columns' => [
            'module' => [120, 'fixed' => 'left'],
            'node_name' => [
                240,
                'label' => '提醒策略',
                'custom' => function ($one) {
                    return $one['node_name'] . '：' . $one['node_info'];
                },
                'paramsType' => 'array',
                'align' => 'left'
            ],
            'content' => ['minWidth' => 300],
            'send_message' => [100, 'checkbox' => ['items' => [1 => '开', 0 => '关']]],
            'send_email' => [100, 'checkbox' => ['items' => [1 => '开', 0 => '关']]],
            'send_sms' => [100, 'checkbox' => ['items' => [1 => '开', 0 => '关']]],
            'send_app' => [100, 'checkbox' => ['items' => [1 => '开', 0 => '关']]],
            'send_qywx' => [100, 'checkbox' => ['items' => [1 => '开', 0 => '关']]],
            'send_wechat' => [100, 'checkbox' => ['items' => [1 => '开', 0 => '关']]],
            [
                'type' => 'operate',
                'button' => [
                    'edit' => $canEdit,
                    'del' => $canDel,
                ]
            ]
        ]
    ])?>
</div>
