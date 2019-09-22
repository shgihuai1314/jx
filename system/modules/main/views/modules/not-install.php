<?php
use system\modules\main\models\ExtendsField;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $data */

use yii\helpers\Url;
$this->title = '未安装模块';
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title clearfix">
        <li><a href="<?=Url::toRoute(['index'])?>">已安装模块</a></li>
        <li class="layui-this">未安装模块</li>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'data' => $data,
        'primaryKey' => 'module_id',
        'model' => \system\modules\main\models\Modules::className(),
        'search' => [
            'url' => ['not-install'],
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
                'field' => 'describe',
                'minWidth' => 500,
                'align' => 'left',
            ],
            [
                'field' => 'author',
                'width' => 120,
            ],
            [
                'field' => 'version',
                'width' => 120,
            ],
            [
                'type' => 'operate',
                'button' => [
                    function ($model) {
                        return Html::tag('a', '安装', [
                            'class' => 'layui-btn layui-btn-sm',
                            'onclick' => 'install("'. $model['module_id'] . '")',
                        ]);
                    },
                ],
            ]
        ],
        'batchBtn' => ["<a class='layui-btn layui-btn-sm btn-install'>批量安装</a>"]
    ])?>
</div>

<script>
    $('body').on('click', '.btn-install', function () {
        var modules = [];
        var checkStatus = table.checkStatus('parse-table');
        var checked = checkStatus.data;
        $.each(checked, function (index, data) {
            modules.push(data.id)
        });

        if (modules.length == 0) {
            layerObj.msg('请选择要安装的模块！', {
                offset: '150px'
            });
        } else {
            install(modules);
        }
    });

    function install(modules) {
        layerObj.open({
            type: 2,
            title: '信息',
            area: ['800px', '560px'],
            btn: ['确定'],
            skin: 'layui-layer-lan layui-layer-custom', //窗口皮肤，可自定义
            content: '<?= Url::toRoute(['install', 'modules' => ''])?>' + modules,
            yes: function (index, layero) {
                window.location.reload();
                layerObj.closeAll();
            },
            cancel : function (index, layero) {
                window.location.reload();
                layerObj.closeAll();
            }
        });
    }
</script>