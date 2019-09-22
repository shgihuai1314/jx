<?php

/** @var yii\web\View $this */
/** @var null|integer $task_id */
/** @var array $data */

use \system\core\utils\Tool;
use yii\helpers\Url;

// 标题
$this->title = "定时器列表";
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this"><?= $this->title ?></li>
        <li><a href="<?= Url::toRoute(['add', 'task_id' => $task_id]) ?>">添加定时器</a></li>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'data' => $data,
        'model' => \system\modules\cron\models\Cron::className(),
        'search' => [
            'items' => [
                [
                    'type' => 'hidden',
                    'name' => 'task_id'
                ],
                [
                    'name' => 'name',
                    'class' => 'width-300',
                    'type' => 'input',
                    'placeholder' => '请输入',
                    'label' => ''
                ],
            ],
        ],
        'columns' => [
            ['label' => '任务名称', 'custom' => function ($val) { return $val['task']['name']; }],
            'start_time' => [160, 'datetime' => ['format' => 'Y-m-d H:i']],
            'interval_time' => [150, 'custom' => function ($val) {return Tool::ftime($val);}],
            'status' => [120, 'checkbox'],
            [
                'type' => 'operate',
                'button' => ['edit', 'del'],
            ],
        ],
    ]) ?>
</div>