<?php

/** @var yii\web\View $this */
/** @var array $data */

use yii\helpers\Html;
use yii\helpers\Url;

// 标题
$this->title = "列表";
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this"><?= $this->title ?></li>
        <li><a href="<?= Url::toRoute('add') ?>">添加</a></li>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'data' => $data,
        'model' => \system\modules\payment\models\paymentApp::className(),
        'search' => [
            'items' => [
                // 搜索项设置详情请查看文档
                [
                    'name' => 'search',
                    'class' => 'width-300',
                    'type' => 'input',
                    'placeholder' => '请输入',
                ],
            ],
        ],
        'columns' => [
            // 列表字段设置详情请查看文档
            'name',
            'code',
            'pay_type' => ['custom' => function ($res) {
                foreach (explode(",", $res) as $val) {
                    $data[] = \system\modules\payment\models\paymentApp::getAttributesList()['pay_type'][$val];
                }
                return implode(',', $data);
            }],

            'status' => ['100', 'checkbox'],
            'describle',
            'notify_class',
            'notify_url',
            'pay_nums',
            'app_rand',
            [
                'type' => 'operate',
                'button' => [
                    'edit',
                    'del',
                ],
            ],
        ],
    ]) ?>
</div>