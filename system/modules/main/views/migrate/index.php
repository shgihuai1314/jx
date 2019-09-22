<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2018-3-6
 * Time: 15:58
 */

/** @var \yii\web\View $this */
/** @var array $data */

use yii\helpers\Url;

$this->title = '更新记录';
?>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this">更新记录</li>
    </ul>
</div>
<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'data' => $data,
        'search' => [
            'items' => [
                [
                    'class' => 'width-240',
                    'type' => 'input',
                    'name' => 'search',
                    'label' => '',
                    'placeholder' => '输入关键字搜索',
                ],
            ],
            'customBtn' => [
                '<a class="layui-btn  btn-appliy">检查更新</a>',
            ],
        ],
        'model' => \system\modules\main\models\Migration::className(),
        'columns' => [
            'module_id' => [150],
            'desc' => ['minWidth' => 400],
            'class_name' => [500, 'align' => 'left'],
            'version' => [100],
            'apply_time' => [180, 'fixed' => 'right', 'date' => ['format' => 'Y-m-d H:i:s']],
            [
                'type' => 'operate',
                'button' => [
                    'revert' => function ($one) {
                        return "<a class = 'layui-btn layui-btn-sm layui-btn-primary' 
                        onclick=\"Revert('" . $one['class_name'] . "')\">还原</a>";
                    },
                ],
            ],
        ],
        'batchBtn' => ['<a class="layui-btn layui-btn-sm layui-btn-primary btn-revert" >批量还原</a>'],
    ]) ?>
</div>

<script>
    $('body').on('click', '.btn-appliy', function () {
        $.get('check-update', {}, function (res) {
            if (res.length == 0) {
                layerObj.msg("您的数据库已经是最新版本了！", {offset: '150px'});
            } else {
                var msg = "<div id='updateMsg' style='padding: 15px 30px; line-height: 28px;'>" +
                    "<p>有以下" + res.length + "条更新还未处理：</p>" +
                    "<ol style='list-style: decimal; padding: 0 40px;'>";
                $.each(res, function (index, item) {
                    msg += '<li class="item-class" style="list-style: decimal;" data-class="' + item.class + '">' +
                        '【' + item.module + '】' + item.desc + '<span class="fr text-red bold" style="cursor: pointer;" ' +
                        'onclick="javascript:$(this).parent().remove()">忽略</span></li>';
                });
                msg += "</ol>";
                msg += "<br/><p>是否现在更新？</p></div>";

                layerObj.open({
                    type: 1,
                    title: '信息',
                    area: ['720px', '480px'],
                    offset : '150px',
                    content: msg,
                    btn: ['确定', '取消'],
                    yes: function (index, layero) {
                        var li = layero.find('li.item-class');
                        var classList = [];
                        li.each(function (n, obj) {
                            classList.push($(obj).data('class'));
                        });
                        layerObj.closeAll();

                        layerObj.open({
                            type: 2,
                            title: '更新信息',
                            area: ['800px', '560px'],
                            btn: ['确定'],
                            skin: 'layui-layer-lan layui-layer-custom', //窗口皮肤，可自定义
                            content: '<?= Url::toRoute(['operate', 'action' => 'up', 'class' => ''])?>' + classList,
                            yes: function (index, layero) {
                                window.location.reload();
                                layerObj.closeAll();
                            }
                        });
                    }
                })
            }
        }, 'json');
    });

    $('body').on('click', '.btn-revert', function () {
        var classList = [];
        var checkStatus = table.checkStatus('parse-table');
        var checked = checkStatus.data;
        $.each(checked, function (index, data) {
            classList.push(data.id)
        });

        if (classList.length == 0) {
            layerObj.msg('请选择要还原的记录！', {
                offset: '150px'
            });
        } else {
            Revert(classList);
        }
    });

    function Revert(classList) {
        layerObj.confirm('确定要进行该操作吗？', {icon: 3, title: '提示', offset: '150px'}, function (index) {
            layerObj.closeAll();

            layerObj.open({
                type: 2,
                title: '更新信息',
                area: ['800px', '560px'],
                btn: ['确定'],
                skin: 'layui-layer-lan layui-layer-custom', //窗口皮肤，可自定义
                content: '<?= Url::toRoute(['operate', 'action' => 'down', 'class' => ''])?>' + classList,
                yes: function (index, layero) {
                    window.location.reload();
                    layerObj.closeAll();
                }
            });
        });
    }
</script>