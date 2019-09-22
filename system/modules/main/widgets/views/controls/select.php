<?php
$select = \system\modules\main\models\ExtendsField::valueToArray($data['field_value']);
// 模版参数
$templateParams = \system\modules\main\models\ExtendsField::valueToArray($data['template_parameter']);
?>

<div class="layui-form-item">
    <label class="layui-form-label">
        <?= $data['field_title'] ?><?= $data['is_must'] ? '<span class="text-red">*</span>' : '' ?>
    </label>
    <div class="<?php if (isset($templateParams['cssClass'])):?><?= $templateParams['cssClass']?><?php else:?>layui-input-inline<?php endif;?>" style="<?php if (isset($templateParams['cssStyle'])):?><?= $templateParams['cssStyle']?><?php endif;?>">
        <select name="<?= $data['field_name'] ?>" lay-verify="<?= $data['is_must'] ? 'required' : '' ?>">
            <?php foreach ($select as $k => $v): ?>
                <option value="<?= $k ?>" <?= $k == $model->{$data['field_name']} ? 'selected' : '' ?> ><?= $v ?></option>
            <?php endforeach; ?>
        </select>

        <?php if($data['field_explain']):?><div class="help-block"><?=$data['field_explain']?></div><?php endif;?>
    </div>

</div>
