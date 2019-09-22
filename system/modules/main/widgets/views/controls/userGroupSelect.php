<?php
// 模版参数
$templateParams = \system\modules\main\models\ExtendsField::valueToArray($data['template_parameter']);
?>

<div class="layui-form-item">
    <label class="layui-form-label">
        <?= $data['field_title'] ?><?= $data['is_must'] ? '<span class="text-red">*</span>' : '' ?>
    </label>
    <div class="<?php if (isset($templateParams['cssClass'])):?><?= $templateParams['cssClass']?><?php else:?>layui-input-block<?php endif;?>"
         style="<?php if (isset($templateParams['cssStyle'])):?><?= $templateParams['cssStyle']?><?php endif;?>">

        <input type="text" name="<?= $data['field_name'] ?>" value="<?= $model->{$data['field_name']} ?>" autocomplete="off" class="layui-input user-group-select"

        <?php if (isset($templateParams['show_user'])):?>
            data-show_user = "<?= $templateParams['show_user']?>"
        <?php endif;?>
        <?php if (isset($templateParams['show_page'])):?>
           data-show_page = "<?= $templateParams['show_page']?>"
        <?php endif;?>
        <?php if (isset($templateParams['select_max'])):?>
            data-select_max = "<?= $templateParams['select_max']?>"
        <?php endif;?>
        <?php if (isset($templateParams['select_type'])):?>
            data-select_type = "<?= $templateParams['select_type']?>"
        <?php endif;?>
        <?php if (isset($templateParams['show_range'])):?>
            data-show_range = "<?= $templateParams['show_range']?>"
        <?php endif;?>
        <?php if (isset($templateParams['range_type'])):?>
            data-range_type = "<?= $templateParams['range_type']?>"
        <?php endif;?>

        />

        <?php if($data['field_explain']):?><div class="help-block"><?=$data['field_explain']?></div><?php endif;?>
    </div>

</div>
