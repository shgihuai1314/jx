<?php

/** @var yii\web\View $this */
/** @var array $data */

use system\modules\cron\models\Cron;
use system\core\utils\Tool;
use yii\helpers\Url;

// 标题
$this->title = "任务管理";
?>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this"><?= $this->title ?></li>
        <li><a href="<?= Url::toRoute('add') ?>">添加任务</a></li>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'data' => $data,
        'model' => \system\modules\cron\models\CronTasks::className(),
        'search' => [
            'items' => [
                [
                    'name' => 'name',
                    'class' => 'width-300',
                    'placeholder' => '请输入',
                    'label' => ''
                ],
                [
                    'type' => 'select',
                    'name' => 'module_id',
                    'class' => 'width-180',
                    'prompt' => '请选择',
                ],
            ],
        ],
        'columns' => [
            // 列表字段设置详情请查看文档
            'name' => [150],
            'module_id' => [120],
//            'type' => [120],
//            'command' => [360, 'align' => 'left'],
            'desc',
            'sort' => [100, 'edit'],
            [
                'width' => 300,
                'label' => '定时器',
                'custom' => function ($val) {
                    if (count($val->cron) == 0) {
                        $content = '<span class="text-red">暂无</span>&nbsp;&nbsp;<a class="layui-btn layui-btn-sm btn-cron-add" data-id="' . $val->id . '">添加</a>';
                    } else {
                        $tips = [];
                        $interval = [];
                        foreach ($val->cron as $cron) {
                            $tips[] = '开始时间：' . date('Y-m-d H:i', $cron['start_time']) . '<br/>
                                间隔时间：' .Tool::ftime($cron['interval_time']) . '<br/>
                                状态：' . Cron::getAttributesList('status', $cron['status']);
                            $interval[] = Tool::ftime($cron['interval_time']);
                        }

                        $content = '<span class="system-tip" data-tip="' . implode("<hr/>", $tips) . '">每 <span class="bold">' . implode('|', $interval) . '</span> 执行一次</span>&nbsp;&nbsp;
                                    <a class="layui-btn layui-btn-sm layui-btn-primary btn-cron-detail" data-id="' . $val->id . '">详情</span>';
                    }
                    return $content;
                }
            ],
            [
                'type' => 'operate',
                'button' => ['edit', 'del'],
            ],
        ],
    ]) ?>
</div>

<script>
    // 添加定时器
    $('body').on('click', '.btn-cron-add', function () {
        var task_id = $(this).data('id');
        layerObj.open({
            type: 2,
            title: '添加定时器',
            area: ['880px', '560px'],
            skin: 'layui-layer-lan layui-layer-custom', //窗口皮肤，可自定义
            content: '<?= Url::toRoute(['timer/add', 'task_id' => ''])?>' + task_id,
            success: function (layero, index) {
                layerObj.getChildFrame('.submit-box a.layui-btn', index).remove();
            },
            cancel: function(index, layero){
                layerObj.close(index);
                window.location.reload();
            }
        });
    });

    // 查看定时器
    $('body').on('click', '.btn-cron-detail', function () {
        var task_id = $(this).data('id');
        layerObj.open({
            type: 2,
            title: '详情',
            area: ['880px', '560px'],
            skin: 'layui-layer-lan layui-layer-custom', //窗口皮肤，可自定义
            content: '<?= Url::toRoute(['timer/index', 'task_id' => ''])?>' + task_id,
            cancel: function(index, layero){
                layerObj.close(index);
                window.location.reload();
            }
        })
    })
</script>