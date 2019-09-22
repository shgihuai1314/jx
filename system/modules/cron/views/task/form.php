<?php

/** @var yii\web\View $this */
/** @var system\modules\cron\models\CronTasks $model */

use system\modules\cron\models\CronTasks;
use yii\helpers\Html;
use yii\helpers\Url;

// 标题
$this->title = $model->isNewRecord ? '添加任务' : '编辑任务';
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li><a href="<?= Url::toRoute('index')?>">任务管理</a></li>
        <li class="layui-this"><?= $this->title ?></li>
    </ul>
</div>

<div class="layui-row">
    <?= system\widgets\FormViewWidget::widget([
        'model' => $model,
        'fields' => [
            // 表单字段设置详情请查看文档
            'name' => [
                'required' => true,
                'box' => 'layui-col-xs8'
            ],
            'module_id' => [
                'type' => 'select',
                'required' => true,
                'box' => 'layui-col-xs6'
            ],
            'type' => [
                'type' => 'radio',
                'required' => true,
            ],
            [
                'fields' => [
                    [
                        'box' => 'cron-type0',
                        'label' => '模块方法',
                        'html' => '<input type="text" class="layui-input" name="command0" value="' . ($model->type == 0 ? $model->command : '') . '" placeholder="如schedule/notice">' .
                            '<p class="help-block">在指定模块的console/中配置的方法，会根据所属模块找到对应的console/config.php配置文件</p>',
                    ],
                    [
                        'box' => 'cron-type1',
                        'label' => '执行命令',
                        'html' => '<input type="text" class="layui-input" name="command1" value="' . ($model->type == 1 ? $model->command : '') . '" placeholder="如：md {date(\'YmdHis\')}；创建当前时间命名的文件夹">' .
                            '<p class="help-block">直接输入控制台命令，可以定时删除指定文件夹下的文件，可以在"{}"内加入php代码，执行前会自动解析</p>',
                    ],
                    [
                        'box' => 'cron-type2',
                        'label' => '选择文件',
                        'html' => Html::tag('div', Html::tag('div', Html::dropDownList('command2', $model->type == 2 ? $model->command : '', CronTasks::getTaskFiles(), ['id' => 'file-list', 'prompt' => '请选择']),
                                    ['class' => 'layui-col width-300']) .
                                Html::tag('div', Html::button('<i class="iconfont icon-upload"></i>&nbsp;上传文件', ['class' => 'layui-btn ', 'id' => 'upload-btn']),
                                    ['class' => 'layui-col']), ['class' => 'layui-block clearfix']) .
                            '<p class="help-block">直接上传php脚本文件到“@service-hall/extension/cron/”文件夹中</p>'
                    ],
                ]
            ],
            'desc' => [
                'type' => 'textarea',
            ],
            'sort' => [
                'type' => 'number',
                'box' => 'layui-col-xs6'
            ],
        ],
    ]) ?>
</div>

<script>
    changeType(<?= $model->type ?>);
    function changeType(val) {
        $('.cron-type' + val).show().siblings().hide();
    }
    form.on('radio(type)', function (data) {
        var type = data.elem.value;
        changeType(type);
    });

    layui.use('upload', function(){
        var upload = layui.upload;

        //执行实例
        upload.render({
            elem: '#upload-btn', //绑定元素
            url: "<?= Url::toRoute(['upload']) ?>", //上传接口
            accept: 'file',
            exts: 'php',
            done: function(res){
                if (res.code == 0) {
                    layerObj.msg(res.message, {offset: '150px'});
                    var src = res.data.src;
                    var name = getFileName(src);
                    $('#file-list').append('<option value="' + src + '">' + name + '</option>');
                    $('#file-list').val(src);
                    form.render();
                } else {
                    layerObj.msg(res.message, {offset: '150px', icon : 2, anim: 6});
                }
            },
        });
    });

</script>