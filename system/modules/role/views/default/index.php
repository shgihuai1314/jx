<?php
/**
 * 用户列表
 * User: ligang
 * Date: 2017/3/7
 * Time: 上午10:13
 */

use yii\helpers\Url;

// 用户状态列表
$user_status_list = Yii::$app->systemConfig->getValue('USER_STATUS_LIST', []);
// 当前组
$status = Yii::$app->request->get('status', '');
// 权限判断
$canAdd = Yii::$app->user->can('role/default/add');
$canEdit = Yii::$app->user->can('role/default/edit');
$canDel = Yii::$app->user->can('role/default/del');

$this->title = "管理员";
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this">角色列表</li>
        <?php if ($canAdd): ?>
            <li><a href="<?= Url::toRoute('add') ?>">新增角色</a></li>
        <?php endif; ?>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'data' => $data,
        'model' => \system\modules\role\models\AuthRole::className(),
        'search' => [
            'items' => [
                [
                    'class' => 'width-300',
                    'name' => 'search',
                    'placeholder' => '请输入',
                    'label' => ''
                ]
            ]
        ],
        'columns' => [
            ['type' => 'ID'],
            'name' => [200],
            'description',
            [
                'width' => 300,
                'label' => '成员',
                'align' => 'left',
                'custom' => function ($val) use ($users) {
                    $html = '';
                    if (isset($users[$val['role_id']])) {
                        foreach ($users[$val['role_id']] as $user) {
                            $html .= "<span class='system-tip' data-tip='{$user['realname']}<br>{$user['username']}'>
                            <img class='avatar-mini img-circle' src='{$user['avatar']}' alt='{$user['realname']}' /> " . $user['realname'] . '</span> ';
                        }
                    }
                    return $html;
                }
            ],
            [
                'type' => 'operate',
                'button' => [
                    'edit' => $canEdit,
                    'del' => function ($val) use ($canDel) {
                        if ($val['is_init'] == 0 && $canDel) {
                            return \yii\helpers\Html::tag('a', '删除', [
                                'class' => 'layui-btn layui-btn-primary layui-btn-sm btn-del',
                                'data-id' => $val['role_id']]);
                        } else {
                            return false;
                        }
                    },
                ]
            ]
        ]
    ])?>
</div>