<?php

/** @var yii\web\View $this */
/** @var system\modules\cron\models\Cron $model */

use yii\helpers\Html;
use yii\helpers\Url;

if ($model->interval_time == 0) {
    $number = 0;
    $times = 1;
} elseif ($model->interval_time % 86400 == 0) {
    $number = $model->interval_time / 86400;
    $times = 86400;
} elseif ($model->interval_time % 3600 == 0) {
    $number = $model->interval_time / 3600;
    $times = 3600;
} elseif ($model->interval_time % 60 == 0) {
    $number = $model->interval_time / 60;
    $times = 60;
} else {
    $number = $model->interval_time;
    $times = 1;
}

$units = [
    86400 => '天',
    3600 => '小时',
    60 => '分钟',
    1 => '秒',
];
// 标题
$this->title = $model->isNewRecord ? '添加定时器' : '编辑定时器';
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li><a href="<?= Url::toRoute(['index', 'task_id' => $model->task_id])?>">定时器列表</a></li>
        <li class="layui-this"><?= $this->title ?></li>
    </ul>
</div>

<div class="layui-row">
    <?= system\widgets\FormViewWidget::widget([
        'model' => $model,
        'fields' => [
            'task_id' => [
                'required' => true,
                'type' => 'select',
                'options' => [
                    'disabled' => $model->task_id != 0
                ]
            ],
            'start_time' => [
                'required' => true,
                'type' => 'datetime',
            ],
            [
                'label' => '间隔时间',
                'html' => Html::input('hidden', 'interval_time', $model->interval_time, ['id' => 'interval_time']) .
                    Html::tag('div', Html::input('number', null, $number, ['class' => 'layui-input', 'id' => 'number']),
                        ['class' => 'layui-col width-120']) .
                    Html::tag('div', Html::dropDownList('', $times, $units, ['id' => 'times']),
                        ['class' => 'layui-col width-90'])
            ],
            'status' => [
                'type' => 'radio'
            ],
        ],
    ]) ?>
</div>
<script>
    form.on('submit(submit)', function () {
        var number = $('#number').val();
        var times = $('#times').val();

        if (number <= 0) {
            layerObj.msg('间隔时间必须大于0', {offset: '150px', icon: 2, anim: 6});
            return false;
        }

        $('#interval_time').val(times * number);
        return true;
    })
</script>