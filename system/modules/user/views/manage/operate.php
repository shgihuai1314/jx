<?php

use system\modules\user\models\User;

$status = User::getAttributesList('status');
$position = User::getAttributesList('position_id');
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title" style="margin-left: 8px;">
        <li class="layui-this">修改状态</li>
        <li>修改职位</li>
        <li>修改部门</li>
    </ul>
    <div class="layui-tab-content layui-form" style="padding: 10px 30px">
        <div class="layui-tab-item layui-show" data-field="status">
            <?php foreach ($status as $id => $one) : ?>
                <div>
                    <input type="radio" name="status" value="<?= $id ?>" title="<?= $one ?>" lay-filter=""/>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="layui-tab-item" data-field="position_id">
            <?php foreach ($position as $id => $one) : ?>
                <div>
                    <input type="radio" name="position_id" value="<?= $id ?>" title="<?= $one ?>" lay-filter=""/>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="layui-tab-item" data-field="group_id">
            <?= \system\widgets\ZTreeWidget::widget([
                'getUrl' => ['/user/group/ajax'],
                'divOption' => 'style="padding: 0; overflow-x: hidden;"',
            ]) ?>
        </div>
    </div>
</div>