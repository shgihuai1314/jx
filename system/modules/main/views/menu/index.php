<?php

/** @var \yii\web\View $this */
/** @var \system\modules\main\models\Menu $model */
/** @var array $list */
/** @var integer $id */

/** @var bool $type */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = '菜单列表';
?>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this"><?= $this->title ?></li>
        <li><a href="<?= Url::to(['add', 'pid' => $id]) ?>">添加菜单</a></li>
    </ul>
</div>
<div class="layui-row">
    <div style="float: left; width: 270px; margin-right: -270px; position: relative;">
        <script>
            var onSelect = function (event, treeId, treeNode) {
                if (treeNode != null && treeNode.id != '<?= $id ?>') {
                    window.location.href = "<?= Url::to(['index', 'id' => '']) ?>" + treeNode.id
                }
            }
        </script>
        <div class="layui-form layui-col-xs-offset1">
            <input type="checkbox" title="显示操作目录" lay-skin="primary" lay-filter="type"
                <?= $type ? 'checked' : '' ?>/>
        </div>
        <?= \system\widgets\ZTreeWidget::widget([
            'divId' => 'menu',
            'note_id' => $id,
            'getUrl' => ['ajax', 'action' => 'get-nodes'],
            'divOption' => 'style="padding: 0 10px;"',
            'onSelect' => 'onSelect',
            'isExpand' => false
        ]) ?>
    </div>
    <div style="float: right; width: 100%">
        <div style="margin-left: 270px;">
            <?= \system\widgets\GridViewWidget::widget([
                'parseData' => ['height' => 'full-150'],
                'data' => $list,
                'model' => \system\modules\main\models\Menu::className(),
                'columns' => [
                    ['type' => 'ID', 'width' => 70],
                    [
                        'field' => 'menu_name',
                        'minWidth' => 200,
                        'custom' => function ($arr) {
                            //菜单图标
                            $icon = !empty($arr['icon']) ? $arr['icon'] : (!empty($arr['url']) ? 'fa fa-circle-o' : 'iconfont icon-test');
                            //菜单名称
                            return Html::tag('div', '<i class="' . $icon . '"></i>&nbsp;&nbsp;' . $arr['menu_name'],
                                ['style' => 'padding-left: ' . ($arr['level'] * 25 - 15) . 'px;']);
                        },
                        'paramsType' => 'array',
                    ],
                    'module' => [100],
                    'path' => [250, 'edit', 'align' => 'left'],
                    'is_show' => [100, 'checkbox'],
                    'sort' => [80, 'edit'],
                    [
                        'type' => 'operate',
                        'button' => ['edit', 'del'],
                    ]
                ]
            ]) ?>
        </div>
    </div>
</div>

<script>
    form.on('checkbox(type)', function (data) {
        var href = window.location.href;
        href = changeURLPar(href, 'type', data.elem.checked ? 1 : 0);
        window.location.href = href;
    });
</script>