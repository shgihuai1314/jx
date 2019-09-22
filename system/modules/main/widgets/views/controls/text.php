<?php
// 模版参数
$templateParams = \system\modules\main\models\ExtendsField::valueToArray($data['template_parameter']);
?>

<div class="layui-form-item">
    <label class="layui-form-label"><?= $data['field_title'] ?>
        <?= $data['is_must'] ? '<span class="text-red">*</span>' : '' ?>
    </label>
    <div
         class="<?php if (isset($templateParams['cssClass'])): ?><?= $templateParams['cssClass'] ?><?php else: ?>layui-input-block<?php endif; ?>"
         style="<?php if (isset($templateParams['cssStyle'])): ?><?= $templateParams['cssStyle'] ?><?php endif; ?>"
        <?php if (isset($templateParams['divExtend'])): ?><?= $templateParams['divExtend'] ?><?php endif; ?>>
        <input
                type="text"
                name="<?= $data['field_name'] ?>"
                lay-verify="<?= $data['is_must'] ? 'required' : '' ?>"
                placeholder="<?= $data['field_title'] ?>"
                autocomplete="off"
                class="layui-input <?php if (isset($templateParams['inputClass'])): ?><?= $templateParams['inputClass'] ?><?php endif; ?>"
                value="<?= $model->{$data['field_name']} ?>"
                <?php if (isset($templateParams['inputExtend'])): ?>
                    <?= $templateParams['inputExtend'] ?>
                <?php endif; ?>
        >
        <?php if ($data['field_explain']): ?>
            <div class="help-block"><?= $data['field_explain'] ?></div><?php endif; ?>
    </div>
</div>