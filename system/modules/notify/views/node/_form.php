<?php
/** @var \system\modules\notify\models\NotifyNode $model */

$modulesMap = \system\modules\main\models\Modules::getModuleMap();
?>

<?= \system\widgets\FormViewWidget::widget([
    'model' => $model,
    'action' => $model->isNewRecord ? ['add'] : ['edit', 'id' => $model->node_id],
    'fields' => [
        'node_name' => [
            'required' => true,
            'verify' => 'required|node_name',
            'placeholder' => '用户程序调用，只能使用英文且不能重复',
        ],
        'node_info' => [
            'required' => true,
        ],
        'module' => [
            'type' => 'select',
            'required'=>true,
            'items' => $category
        ],
        'icon' => [
            'type' => 'widget',
            'hint' => '供页面消息提醒图标展示',
            'class' => \system\modules\main\widgets\FileUploadWidget::className(),
            'config' => [
                'item' => [
                    'accept' => 'images',
                    'title' => '上传小图标',
                ],
            ],
        ],
        'content' => [
            'type' => 'textarea',
            'hint' => '使用${name}的方式定义参数，name即为参数名称，发送时可以传递相同的名称来替换参数',
        ],
        'is_self' => [
            'type' => 'radio',
        ],
        'send_message' => [
            'type' => 'radio',
        ],
        'send_email' => [
            'type' => 'radio',
        ],
        'send_sms' => [
            'type' => 'radio',
        ],
        'send_app' => [
            'type' => 'radio',
        ],
        'send_qywx' => [
            'type' => 'radio',
        ],
        'send_wechat' => [
            'type' => 'radio',
        ],
    ],
]) ?>
<script type="text/javascript">
    //自定义验证规则
    form.verify({
        node_name: function (value) {
            var message;
            $.ajax({
                type: "post",
                url: 'edit',
                data: {action: 'name-exit', id: '<?= $model->node_id ?>', node_name: value},
                async: false,
                success: function (res) {
                    if (res.code == 1) {
                        message = res.message;
                    }
                }
            }, 'json');
            return message;
        }
    });

</script>
