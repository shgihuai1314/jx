<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/13
 * Time: 下午1:49
 */

/** @var \yii\web\View $this */
/** @var \system\modules\role\models\AuthRole $model */
/** @var array $user */

/** @var array $allUser */

use yii\helpers\Url;
use yii\helpers\Html;

// 加载所有的菜单权限
$navBar = \system\modules\main\models\Menu::getMenusTree();
// 用户当前拥有的权限
$model->permission = empty($model->permission) ? [] : explode(',', $model->permission);

$this->title = $model->isNewRecord ? '新增角色' : '编辑角色：' . $model->name;
?>
<style>
    .system-tip i {
        margin-top: 6px
    }

    .members {
        height: 38px;
        line-height: 38px;
    }

    .members .layui-form-checkbox[lay-skin=primary] i {
        top: 5px;
    }

    .layui-form-item .layui-form-checkbox[lay-skin="primary"] {
        margin-top: 0;
    }

    .layui-field-box {
        padding: 0;
    }
</style>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li><a href="<?= Url::toRoute('index') ?>">角色列表</a></li>
        <li class="layui-this"><?= $this->title ?></li>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\FormViewWidget::widget([
        'model' => $model,
        'fields' => [
            'name' => [
                'required' => true,
            ],
            'code' => [
                'required' => true,
            ],
            'description' => 'textarea',
            [
                'label' => '成员',
                'html' => function ($data) use ($user, $allUser) {
                    $content = '';
                    foreach ($allUser as $key => $one) {
                        $content .= Html::tag('div',
                            Html::checkbox('users[]', in_array($one['user_id'], $user), [
                                'value' => $one['user_id'],
                                'lay-skin' => "primary",
                                'title' => '<img class="avatar-mini2 img-circle" src="' . $one['avatar'] . '"> ' . $one['realname'],
                            ]),
                            ['class' => 'layui-col members']);
                    }
                    return $content;
                }
            ],
            [
                'label' => '分配权限',
                'html' => "<div class='layui-field-box'>" .
                    $this->render('permission', [
                        'permission' => $navBar,
                        'items' => $model->permission,
                    ]) . "</div>"
            ]
        ]
    ]) ?>
</div>