<?php
/** @var yii\web\View $this */
/** @var array $list */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = '模块';
?>
<style>
	.layui-upload {width: 100px; margin: 0 auto; padding: 0}
    .layui-upload input[type="file"] {top: 0; width: 100px; height: 44px;}
    .custom-table td .layui-table-cell {height: 44px;line-height: 44px;}
    .custom-table td .layui-form-checkbox[lay-skin=primary] span {padding-left: 5px;}
	.upload-icon {position: relative; width: 88px; margin: 0 auto;}
	.upload-icon i {position: absolute; top: 0; right: 16px; font-size: 20px; border-radius: 50%; background-color: white; color: #ff5500; cursor: pointer;}
</style>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title clearfix">
        <li class="layui-this">已安装模块</li>
        <li><a href="<?= Url::to(['not-install']) ?>">未安装模块</a></li>
    </ul>
</div>

<div class="layui-row">
	<?= \system\widgets\GridViewWidget::widget([
		'parseData' => ['height' => 'full-200'],
		'data' => $list,
		'model' => \system\modules\main\models\Modules::className(),
        'search' => [
            'items' => [
                [
                    'name' => 'search',
                    'class' => 'width-300',
                    'type' => 'input',
                    'placeholder' => '请输入',
                ],
            ],
        ],
		'columns' => [
            [
                'field' => 'name',
                'width' => 120,
                'fixed' => 'left'
            ],
			[
				'field' => 'icon',
				'width' => 120,
				'custom' => function($val) {
                    if (empty($val['icon'])) {
                        return \system\modules\main\widgets\FileUploadWidget::widget([
	                        'item' => [
		                        'field' => 'icon',
		                        'url' => Url::to(['upload', 'id' => $val['id']]),
		                        'btnId' => 'upload-btn-' . $val['module_id'],
		                        'title' => '上传图标',
		                        'done' => 'window.location.reload();layerObj.closeAll();'
	                        ]
                        ]);
                    } else {
                    	return Html::tag('div', Html::img($val['icon'], ['style' => 'width: 36px; height: 36px'])
		                    . '<i class="fa fa-times-circle" onclick="delIcon('.$val['id'].')"></i>', [
		                    'class' => 'upload-icon'
	                    ]);
                    }
                },
				'paramsType' => 'array',
			],
            [
                'field' => 'describe',
                'minWidth' => 500,
                'custom' => function ($one) {
                    return '<div style="line-height: 22px">' . ($one['describe'] ? $one['describe'] : '暂无描述') .
                        '<div style="font-size: 12px; color: #999;">作者：' . $one['author'] . ' 版本：' . $one['version'] . '</div></div>';
                },
                'paramsType' => 'array',
            ],
			'status' => [100, 'checkbox', ['filter' => ['core' => 1]]],
            [
                'label' => '更新记录',
                'width' => 100,
                'custom' => function ($val) {
	                return Html::button('详情', [
                        'class' => 'layui-btn layui-btn-primary layui-btn-sm btn-up-record',
                        'data-id' => $val['module_id'],
                    ]);
                },
                'fixed' => 'right'
            ],
			[
				'type' => 'operate',
				'button' => [
                    function ($one) {
                        return $one['core'] == 0 ? Html::button('卸载', [
                            'class' => 'layui-btn layui-btn-primary layui-btn-sm uninstall',
                            'data-id' => $one['id'],
                        ]) : '系统内置';
                    },
				],
			]
		]
	])?>
</div>
<script>
    $('body').on('click', '.uninstall', function () {
        var id = $(this).data('id');
        layerObj.confirm('确认卸载吗？', {
            offset: '150px',
            btn: ['卸载', '取消'] //按钮
        }, function () {
            layerObj.closeAll();
            layerObj.open({
                type: 2,
                title: '更新信息',
                area: ['800px', '560px'],
                btn: ['确定'],
                skin: 'layui-layer-lan layui-layer-custom', //窗口皮肤，可自定义
                content: '<?= Url::toRoute(['uninstall', 'id' => ''])?>' + id,
                yes: function (index, layero) {
                    window.location.reload();
                    layerObj.closeAll();
                },
                cancel : function (index, layero) {
                    window.location.reload();
                    layerObj.closeAll();
                }
            });
        });
    });

    $('body').on('click', '.btn-up-record', function () {
        var module_id = $(this).data('id');
        $.get('get-record', {module_id: module_id}, function (res) {
            var msg = "<div style='padding: 15px 30px; line-height: 28px;'>";
            $.each(res, function (version, items) {
                msg  += "<p class='bold'>" + version + " 版本更新内容：</p>" +
                    "<ol style='list-style: decimal; padding: 0 40px 10px;'>";
                $.each(items, function (i, item) {
                    msg += '<li class="item-class" style="list-style: decimal;">' + item.desc + '</li>';
                });
                msg += "</ol>";
            });
            msg += "</div>";

            layerObj.open({
                type: 1,
                title: '更新内容',
                skin: 'layui-layer-lan layui-layer-custom',
                area: ['630px', '420px'],
                offset : '150px',
                content: msg,
                btn: ['确定']
            })
        }, 'json');
    });

    function delIcon(id) {
	    $.post('edit', $.extend({id: id, field: 'icon', val: ''}, param), function (res) {
            if (res.code == 0) {
	            layerObj.msg('操作成功!', {offset: '150px'});
				window.location.reload();
            } else {
                layerObj.msg('操作失败！', {offset: '150px', icon: 2, anim: 6});
            }
        }, 'json');
    }

    form.on('checkbox', function (data) {
        var val = [];
        $.each($('input[name="' + data.elem.name + '"]:checked'), function (index, obj) {
            val.push(obj.value);
        });

        $.post('edit', $.extend({
            id: data.elem.name.split('-')[1],
            field: data.elem.name.split('-')[0],
            val: val.join(',')
        }, param), function (res) {
            layerObj.msg(res.code == 0 ? '修改成功' : '修改失败，error：' + res.msg, {
                offset: '150px'
            });
        }, 'json');
    });
</script>
