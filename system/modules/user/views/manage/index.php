<?php

/** @var \yii\web\View $this */
/** @var array $data */
/** @var bool $flag */

use yii\helpers\Url;
use yii\helpers\Html;

$flag = Yii::$app->request->get('flag');
$id = Yii::$app->request->get('group');


$canAdd = Yii::$app->user->can('user/manage/add');
$canEdit = Yii::$app->user->can('user/manage/edit');
$canDel = Yii::$app->user->can('user/manage/delete');

\system\modules\user\assets\UserAsset::register($this);

$this->title = '用户列表';
?>
<style>
    .custom-table td .layui-table-cell {height: 40px;line-height: 40px;}
</style>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this"><?= $this->title ?></li>
        <?php if ($canAdd) : ?>
            <li><a href="<?= Url::to(['add', 'group_id' => $id]) ?>">添加用户</a></li>
        <?php endif; ?>
    </ul>
</div>
<div class="layui-row">
    <div style="float: left; width: 270px; margin-right: -270px; position: relative;">
        <script>
            var onSelect = function (event, treeId, treeNode) {
                if (treeNode != null && treeNode.id != '<?= $id ?>') {
                    window.location.href = "<?= Url::to(['index', 'flag' => $flag, 'group' => '']) ?>" + treeNode.id
                }
            }
        </script>
        <div class="layui-form" style="padding-left: 12px;">
            <input type="checkbox" title="显示子部门用户" lay-skin="primary" lay-filter="flag" <?= $flag ? 'checked' : '' ?>/>
        </div>
        <?= \system\widgets\ZTreeWidget::widget([
            'divId' => 'group',
            'note_id' => $id,
            'getUrl' => ['/user/group/ajax'],
            'divOption' => 'style="padding: 0 10px;"',
            'onSelect' => 'onSelect',
            'isExpand' => false
        ]) ?>
    </div>
    <div style="float: right; width: 100%">
        <div style="margin-left: 270px;">
            <?= \system\widgets\GridViewWidget::widget([
            'data' => $data,
            'model' => \system\modules\user\models\User::className(),
            'search' => [
                'items' => [
                    [
                        'type' => 'hidden',
                        'name' => 'group'
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'flag'
                    ],
                    [
                        'class' => 'width-300',
                        'type' => 'input',
                        'name' => 'search',
                        'label' => '',
                        'placeholder' => '请输入',
                    ],
                ],
                'breadcrumb' => [
                    'name' => 'status',
                    'prompt' => '全部',
                ]
            ],
            'columns' => [
                'user_id' => ['label' => 'ID', 'width' => 80],
                [
                    'label' => '姓名',
                    'width' => 200,
                    'custom' => function ($val) {
                        return '<div class="system-name system-tip" data-tip="' . $val['username'] . '<br>' . $val['realname'] . '">
                        <div class="use-img">
                            <img class="avatar-mini img-circle" src="' . $val['avatar'] . '"/>
                        </div>
                        <div class="use-name">
                            ' . $val['username'] . '<br/>
                            ' . $val['realname'] . '
                        </div>
                    </div>';
                    }
                ],
                [
                    'label' => '联系方式',
                    'width' => 180,
                    'custom' => function ($val) {
                        return '<div class="system-name system-tip" data-tip="' . $val['phone'] . '<br>' . $val['email'] . '">
                        <div class="system-email">
                            ' . $val['phone'] . '<br/>
                            ' . $val['email'] . '
                        </div>
                    </div>';
                    }
                ],
                [
                    'label' => '部门职位',
                    'minWidth' => 200,
                    'custom' => function ($val) {
                        $group = \system\modules\user\models\Group::getNamePath($val['group_id']);
                        $arr = \system\modules\user\models\Position::getAllMap();
                        $position = isset($arr[$val['position_id']]) ? $arr[$val['position_id']] : '-';
                        return '<div class="system-name system-tip" data-tip="' . $group . '<br>' . $position . '">
                        <div class="system-group">
                            ' . $group . '<br/>
                            ' . $position . '
                        </div>
                    </div>';
                    }
                ],
                /*'role_id' => [150],*/
                [
                    'label' => '角色',
                    'custom' => function($val) {
                        $role_name = \system\modules\role\models\AuthAssign::getRoleFiledByUser($val['user_id'], 'name');
                        return implode(',', $role_name);
                    }
                ],
                'status' => [100],
                'sort' => [80, 'edit'],
                [
                    'type' => 'operate',
                    'button' => [
                        'edit' => $canEdit,
                        'del' => $canDel,
                    ],
                ]
            ],
            'batchBtn' => ['<a class="layui-btn layui-btn-sm btn-operate" >批量操作</a>']
        ]) ?>
        </div>
    </div>
</div>

<script>
    form.on('checkbox(flag)', function (data) {
        var href = window.location.href;
        href = changeURLPar(href, 'flag', data.elem.checked ? 1 : 0);
        window.location.href = href;
    });

    $('body').on('click', '.btn-operate', function () {
        var ids = [];
        var checkStatus = table.checkStatus('parse-table');
        var checked = checkStatus.data;
        $.each(checked, function (index, data) {
            ids.push(data.id)
        });

        if (ids.length == 0) {
            layerObj.msg('请选择要操作的用户！', {
                offset: '150px'
            });
        } else {
            layerObj.open({
                type: 2,
                title: '请选择',                            //弹窗标题
                skin: 'layui-layer-lan layui-layer-custom', //窗口皮肤，可自定义
                offset: '150px',
                area: ['360px', '480px'],                   //窗口大小
                btn: ['确认', '取消'],                      //按钮
                content: '<?= Url::toRoute(['batch'])?>',
                yes: function (index, layero) {               //点击确定按钮回调
                    var body = parent.layer.getChildFrame('body', index);
                    var item = body.find('.layui-tab-item.layui-show');
                    var field = $(item).data('field');
                    var input = 'input[name="' + field +'"]';
                    input += (field == 'status' || field == 'position_id') ? ':checked' : '';
                    var val = body.find(input).val();
                    if (typeof val == 'undefined') {
                        layerObj.msg('请选择修改的值', {icon: 2, anim: 6});
                    } else {
                        $.post('batch', $.extend({uids: ids, field: field, val: val}, param), function (res) {
                            if (res.code == 0) {
                                layerObj.msg('修改成功！', {offset: '150px'});
                            } else {
                                layerObj.msg('修改失败！', {offset: '150px', icon: 2, anim: 6});
                            }
                            parent.layer.close(index);                     //如果设定了yes回调，需进行手工关闭
                            window.location.reload();
                        }, 'json');
                    }
                }
            })
        }
    });
</script>