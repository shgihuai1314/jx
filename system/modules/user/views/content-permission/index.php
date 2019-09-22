<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/7
 * Time: 上午10:13
 */

/** @var \yii\web\View $this */
/** @var array $data */

use yii\helpers\Url;

$this->title = '内容权限';
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this">内容权限列表</li>
        <li><a href="<?= Url::toRoute('create') ?>">新增权限</a></li>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'data' => $data,
        'model' => \system\modules\user\models\ContentPermission::className(),
        'search' => [
            'items' => [
                [
                    'class' => 'width-300',
                    'name' => 'search',
                    'label' => '',
                    'placeholder' => '请输入'
                ]
            ]
        ],
        'columns' => [
            ['type' => 'ID'],
            [
                'label' => '权限作用于',
                'custom' => function ($val) {
                    return $val->user->realname;
                }
            ],
            [
                'width' => 150,
                'label' => '修改人',
                'custom' => function ($val) {
                    return $val->updateBy->realname;
                }
            ],
            'update_time' => [160, 'datetime'],
            [
                'type' => 'operate',
                'button' => ['edit', 'del']
            ]
        ]
    ]) ?>
</div>