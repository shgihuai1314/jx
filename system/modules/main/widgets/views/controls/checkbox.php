<?php
$select = \system\modules\main\models\ExtendsField::valueToArray($data['field_value']);
// 模版参数
$templateParams = \system\modules\main\models\ExtendsField::valueToArray($data['template_parameter']);
?>

<div class="layui-form-item">
    <label class="layui-form-label">
        <?= $data['field_title'] ?><?= $data['is_must'] ? '<span class="text-red">*</span>' : '' ?>
    </label>
    <div class="<?php if (isset($templateParams['cssClass'])):?><?= $templateParams['cssClass']?><?php else:?>layui-input-block<?php endif;?>" style="<?php if (isset($templateParams['cssStyle'])):?><?= $templateParams['cssStyle']?><?php endif;?>">

        <?php foreach ($select as $k => $v): ?>
            <input type="checkbox" id="extend_filed_<?= $data['field_name'] ?>_<?= $k?>" lay-skin="primary" title="<?= $v ?>" class="extend_field_checkbox" value="<?= $k?>" lay-filter="extend_field_checkbox_<?= $data['field_name'] ?>">
        <?php endforeach; ?>
        <input type="hidden" id="id_extend_field_checkbox_<?= $data['field_name'] ?>" name="<?= $data['field_name'] ?>" value="<?= $model->{$data['field_name']} ?>">

        <?php if($data['field_explain']):?><div class="help-block"><?=$data['field_explain']?></div><?php endif;?>
    </div>

</div>
<script type="text/javascript">
    // 初始化
    var valArr = $("#id_extend_field_checkbox_<?= $data['field_name'] ?>").val().split(',');
    $.each(valArr, function (i, item) {
        $("#extend_filed_<?= $data['field_name'] ?>_"+item).attr("checked","true");
    });
    form.render('checkbox');

    // 选中事件
    form.on('checkbox(extend_field_checkbox_<?= $data['field_name'] ?>)', function(data){
        var str = [];
        var child = $(data.elem).parents('.layui-input-block').find('.extend_field_checkbox');
        child.each(function(index, item){
            if($(item).is(":checked")){
                str.push($(this).val());
            }
        });
        $("#id_extend_field_checkbox_<?= $data['field_name'] ?>").val(str);
    });
</script>
