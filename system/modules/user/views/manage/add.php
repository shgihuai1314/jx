<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/12
 * Time: 下午5:18
 */
$this->title = '添加用户';
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title clearfix">
        <li><a href="<?= \yii\helpers\Url::toRoute('index')?>">用户列表</a></li>
        <li class="layui-this">新增用户</li>
    </ul>

</div>

<?= $this->render('_form', [
    'model' => $model,
    'position' => $position,
    'role' => $role,
]);?>