<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/11/6
 * Time: 18:47
 */

/** @var \yii\web\View $this */
/** @var string $action */
/** @var array $params */

use yii\helpers\Url;

\system\assets\SortableAsset::register($this);
$tables = Yii::$app->systemConfig->getValue('BATCH_OPERATION_TABLE', []);
$this->title = '批量处理';

?>
<style>
    #batch-fields > div {
        height: 30px;
        border: 1px dashed #ccc;
        border-radius: 2px;
        padding-left: 10px;
        margin: 3px 5px 3px 0;
    }
    #batch-fields .layui-form-checkbox[lay-skin=primary] {
        margin-top: 6px;
    }
</style>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
	    <ul class="layui-tab-title">
		    <li <?= $action == 'create' ? 'class="layui-this"' : '' ?> >
			    <a href="<?= Url::to(['index', 'action' => 'create']) ?>">添加操作</a>
		    </li>
		    <li <?= $action == 'update' ? 'class="layui-this"' : '' ?>>
			    <a href="<?= Url::to(['index', 'action' => 'update']) ?>">更新操作</a>
		    </li>
		    <li <?= $action == 'delete' ? 'class="layui-this"' : '' ?>>
			    <a href="<?= Url::to(['index', 'action' => 'delete']) ?>">删除操作</a>
		    </li>
	    </ul>
    </ul>
</div>

<div class="layui-row">
    <div class="separate-20"></div>
    <div class="layui-col-lg7">
        <form class="layui-form custom-form">
            <input type="hidden" name="<?= \Yii::$app->request->csrfParam ?>" value="<?= \Yii::$app->request->csrfToken ?>">

            <div class="layui-form-item">
                <label class="layui-form-label">选择操作的表</label>
                <div class="layui-input-block">
                    <select name="model" lay-verify="required" lay-filter="table-name" id="table-name">
                        <option value="">请选择...</option>
                        <?php foreach ($tables as $k => $value): ?>
                            <option value="<?= $k ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">选择约束的字段</label>
                <div class="layui-input-block">
                    <select name="constraint_field" lay-verify="required" lay-filter="constraint_field" id="constraint_field">
                        <option value="">请选择...</option>
                    </select>
                    <div class="help-block">以选择的字段作为约束条件</div>
                </div>
            </div>

            <?php if ($action != 'delete'): ?>
                <div class="layui-form-item">
                    <label class="layui-form-label">选择操作的字段</label>
                    <div class="layui-input-block">
                        <div id="batch-fields" style="overflow: hidden;"></div>
                        <div class="help-block ">选择批量处理需要处理的字段，会根据选择的字段生成xls模板</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($action == 'create'): ?>
                <div class="layui-form-item">
                    <label class="layui-form-label">是否清除原数据</label>
                    <div class="layui-input-block">
                        <input type="radio" name="is_clear" value="0" checked title="否" lay-filter="is_clear">
                        <input type="radio" name="is_clear" value="1" title="是" lay-filter="is_clear">
                        <div class="help-block">选择“是”会把原数据清除掉重新插入数据，否则只会更新原有数据和添加最新数据</div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="layui-form-item">
                <label class="layui-form-label">遇到错误跳过</label>
                <div class="layui-input-block">
                    <input type="radio" name="is_continue" value="0" title="否" lay-filter="is_continue">
                    <input type="radio" name="is_continue" value="1" checked title="是" lay-filter="is_continue">
                    <div class="help-block">选择“是”导入数据过程中遇到错误跳过直接处理下一条，否则会直接返回失败</div>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">下载模板</label>
                <div class="layui-input-block">
                    <button class="layui-btn layui-btn-primary" id="down-template"><i class="iconfont icon-dowload"></i>点击下载模板</button>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">上传数据</label>
                <div class="layui-input-block">
                    <?= \system\modules\main\widgets\FileUploadWidget::widget([
                        'inputName' => 'data',
                        'resetName' => true,
                        'item' => [
                            'title' => '上传excel表格',
                            'accept' => 'file',
                            'exts' => 'xls',
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button type="button" class="layui-btn" id="submit-btn">开始导入</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
	form.on('select(table-name)', function (data) {
        $('#batch-fields').html('');
        $('#constraint_field').html('');
        form.render();
        if (data.value != '') {
            var model = data.value;// 选择的模型
            var constraint = null;// 约束字段
            var list = [];// 排序的字段列表
            var checked = [];// 勾选的字段
            $.post('ajax', $.extend({action: 'get-fields', model: model}, param), function (res) {
                if (res.code == 0) {
                    $.each(res.data, function (key, value) {
                        // 批处理字段
                        $('#batch-fields').append('<div class="layui-col">' +
                                '<input type="checkbox" class="batch-field" name="batch_fields[]" ' +
                                'lay-filter="batch-field" lay-skin="primary" value="' + key + '" title="' + value + '" ' +
                                ( $.inArray(key, res.cache.checked) != -1 ? 'checked' : '') + '>' +
                            '</div>');
                        // 约束字段
                        $('#constraint_field').append('<option value="' + key + '" ' + (res.cache.constraint == key ? 'selected' : '') + '>' +
                            value + '</option>');
                        list.push(key);
                        checked.push(key);
                    });

                    form.render();

                    // 选择约束字段
                    form.on('select(constraint_field)', function (data) {
                        constraint = data.value;
                        setCache();
                    });
                    // 勾选批处理字段事件
                    form.on('checkbox(batch-field)', function (data) {
                        checked = [];
                        $.each($('input[name="batch_fields[]"]:checked'), function (index, item) {
                            checked.push(item.value);
                        });
                        setCache();
                    });

                    Sortable.create(document.getElementById('batch-fields'), {
                        animation: 150,             //动画参数
                        onEnd: function (evt) {       //拖拽完毕之后发生该事件
                            arr = $(evt.from).find('input[name="batch_fields[]"]');
                            list = [];
                            $.each(arr, function (index, item) {
                                list.push($(item).val());
                            });
                            setCache();
                        }
                    });
                }
            }, 'json');

            function setCache() {
                var data = {
                    action: 'set-cache',
                    model: model,
                    constraint: constraint,
                    list: list,
                    checked: checked
                };
                $.post('ajax', $.extend(data, param), function (res) {

                }, 'json')
            }
        }
    });

    //下载模板
    $('#down-template').click(function () {
        //操作表
        var table_name = $('#table-name').find('option:checked').html();
        //约束字段
        var constraint_field = $('select[name="constraint_field"]').find('option:checked').html();
        //操作的字段
        var field_name = $('#batch-fields').find('input[type="checkbox"]:checked');
        var field_names = [constraint_field];
        field_name.each(function (index) {
            if (constraint_field != $(this).attr('title')) {
                field_names.push($(this).attr('title'));
            }
        });
        
        if (field_names.length == 0) {
			parent.layer.msg('请勾选要操作的字段', {icon: 2, anim: 6, offset: '150px'})
        } else {
            
            $.get('ajax', {action: 'down-template', field_names: field_names, table: table_name}, function (res) {
                if (res.code == 0) {
                    window.location.href = res.data;
                }
            }, 'json');
        }
    });

    $('#submit-btn').click(function () {
        var file = '';
        $('input[name="data"]').each(function (index) {
            file = $(this).val();
        })
        if (file == '') {
            parent.layer.msg('请上传要导入的数据！', { offset: '150px' });
        } else {
            //上传的附件
            file = JSON.parse(file);
            //约束字段
            var constraint_field = $('select[name="constraint_field"]').val();
            //操作的字段
            var field_name = $('#batch-fields').find('input[type="checkbox"]:checked');
            var field_names = [constraint_field];
            field_name.each(function (index) {
                if (constraint_field != $(this).val()) {
                    field_names.push($(this).val());
                }
            });

            parent.layer.load(2);
            $.post('index?action=<?=$action?>', $.extend({
	            model: $('select[name="model"]').val(),
                constraint_field: constraint_field,
	            fields: field_names,
                is_clear: $('input[name="is_clear"]:checked').val(),
                is_continue: $('input[name="is_continue"]:checked').val(),
                file: file.src
            }, param), function (res) {
                parent.layer.closeAll('loading');
                parent.layer.msg(res.msg, {
                    offset: '150px'
                });
                if (res.code == 0) {
                    window.location.reload();
                }
            }, 'json');
        }
    })
</script>