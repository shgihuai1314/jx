<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-5-28
 * Time: 12:54
 */

/** @var \yii\web\View $this */
/** @var \system\modules\main\models\Config $model */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $model->isNewRecord ? '新增配合' : '修改配置：' . $model->name;
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li><a href="<?= Url::toRoute('index')?>">配置列表</a></li>
        <li class="layui-this"><?= $this->title ?></li>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\FormViewWidget::widget([
        'model' => $model,
        'fields' => [
            'name' => [
                'required' => true,
                'verify' => 'config_name',
                'placeholder' => '用户程序调用，只能使用英文且不能重复',
            ],
            'title' => [
                'required' => true,
            ],
            'module' => [
                'box' => 'layui-col-xs8',
                'type' => 'select',
                'hint' => '一般请勿修改，模块的安装卸载会操作此字段'
            ],
            'type' => [
                'box' => 'layui-col-xs8',
                'type' => 'select',
                'hint' => '系统会根据不同类型解析配置值'
            ],
            [
                'div-box' => 'field-value',
                'filter' => $model->type == 'image',
                'fields' => [
                    'value' => [
                        'type' => 'textarea',
                        'hint' => '如果是数组，每行一组数据，格式：组id=组名称，比如：1=开启'
                    ],
                ]
            ],

            [
                'div-box' => 'field-image',
                'filter' => $model->type != 'image',
                'fields' => [
                    'value_image' => [
                        'type' => 'widget',
                        'class' => \system\modules\main\widgets\FileUploadWidget::className(),
                        'config' => [
                            'flag' => 1,
                        ],
                    ],
                ]
            ],
            [
                'div-box' => 'field-extra',
                'filter' => $model->type != 'enum',
                'fields' => [
                    'extra' => [
                        'type' => 'textarea',
                        'hint' => '枚举类型需要配置该项，格式同数组，如：1=是\r\n0=否'
                    ],
                ]
            ],
            'remark' => [
                'type' => 'textarea',
                'hint' => '配置详细说明，会显示在界面上给用户进行配置'
            ],
            'sort' => [
                'box' => 'layui-col-xs8',
                'type' => 'number',
                'hint' => '用于分组显示的顺序，数字越大越靠前'
            ],
        ]
    ])?>
</div>

<script type="text/javascript">
    //自定义验证规则
    form.verify({
        config_name: function(value) {
            var message;
            $.ajax({
                type : "post",
                url : '<?= Url::toRoute(['ajax'])?>',
                data : {action: 'name-exit', id : '<?= $model->id ?>', name: value },
                async : false,
                dataType: 'json',
                success : function(res){
                    if (res.code == 1) {
                        message = res.message;
                    }
                }
            }, 'json');
            return message;
        }
    });

    form.on('select(type)', function (data) {
        if (data.elem.value == 'image') {
            $('.field-value').hide();
            $('.field-image').show();
        } else {
            $('.field-value').show();
            $('.field-image').hide();
        }

        if (data.elem.value == 'enum') {
            $('.field-extra').show();
        } else {
            $('.field-extra').hide()
        }
    })
</script>