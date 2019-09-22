<?php
/** @var yii\web\View $this */
/** @var \system\modules\main\models\ExtendsField $model */

use system\modules\main\models\ExtendsField;

$label = $model->attributeLabels();
$this->title = $model->isNewRecord ? '添加字段' : '编辑字段：' . $model->field_title;
?>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li><a href="<?= \yii\helpers\Url::toRoute('index') ?>">扩展字段</a></li>
        <?php if ($model->isNewRecord): ?>
            <li class="layui-this">新增字段</li>
        <?php else: ?>
            <li class="layui-this">修改字段</li>
        <?php endif; ?>
    </ul>
</div>

<?= \system\widgets\FormViewWidget::widget([
    'model' => $model,
    'fields' => [
        'table_name' => 'select',
        'field_name' => [
            'required' => true,
            'verify' => 'required|field_name',
            'hint' => '以英文开头，英文、数字、_ 组成；比如：address、country等容易标示的字符；入库前会自动添加extend_前缀'
        ],
        'field_title' => [
            'required' => true,
            'hint' => '字段描述会显示在界面中，比如：住址、国家等；描述尽量简洁'
        ],
        'field_explain' => [
            'hint' => '字段提示会显示在页面上，比如：电话必须为数字且符合电话号码格式'
        ],
        'is_must' => 'radio',
        'is_show' => [
            'type' => 'radio',
            'box' => 'layui-col-xs6',
            'hint' => '设置字段是否在表格或表单中显示'
        ],
        'is_search' => [
            'type' => 'radio',
            'box' => 'layui-col-xs6',
            'hint' => '是否把字段加入到表格的搜索项中'
        ],
        'field_type' => 'select',
        'show_type' => 'select',
        [
            'div-box' => 'show_type',
            'filter' => ['show_type' => 'custom', 'flag' => '!='],
            'fields' => [
                'template' => [
                    'type' => 'textarea',
                    'hint' => '填写相关html代码'
                ]
            ]
        ],
        'field_value' => [
            'type' => 'textarea',
            'tip' => '<i class="fa fa-info-circle layui-bg-gray system-tip" data-tip="选填；当展示类型为下拉框，单选框，复选框时有效，书写格式为：<br />标示符1=内容1<br />标示符2=内容2<br />一行为一条记录，标识符可以省略，比如证件类型的写法可以是：<br/>
                        1=身份证<br/>
                        2=护照<br/>
                        那么存储到数据库中就是1或2这种形式，也可以写成如下形式：<br />
                        身份证<br />
                        护照<br />
                        那么存储到数据库中就是身份证或护照的形式，请根据需要选择" aria-hidden="true"></i>'
        ],
        'template_parameter' => [
            'type' => 'textarea',
            'hint' => '选填；这是定义选择的展示类型模板中的参数，格式如字段选项'
        ],
        'default_value' => [
            'box' => 'layui-col-xs6'
        ],
        'is_null' => [
            'type' => 'radio',
            'box' => 'layui-col-xs6',
            'hint' => '此字段的默认值,最好根据业务填写'
        ],
        'sort' => [
            'type' => 'number'
        ]
    ]
])?>

<script>
    form.verify({
        field_name: function(value) {
            if (!/^[a-zA-Z][a-zA-Z0-9_]*$/.test(value)) {
                return '必须以英文开头，只能包含英文，数字，_'
            }
        }
    });

</script>