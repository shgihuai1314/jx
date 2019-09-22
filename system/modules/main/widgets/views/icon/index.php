<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-5-30
 * Time: 10:31
 */

/** @var \yii\web\View $this */
/** @var string $icon */
/** @var string $name */
/** @var array $iconMap */

?>
<style>
    .icon-select {
        overflow: hidden;
    }

    .icon-select .btn-icon {
        padding: 0 10px;
        float: left;
        margin-right: 15px;
    }

    .icon-select .icon-show {
        float: left;
        line-height: 38px;
    }

    .icon-select .icon-show i {
        font-size: 32px;
        display: inline-block;
        line-height: 38px;
    }
</style>
<div class="icon-select">
    <input type="hidden" name="<?= $name ?>" value="<?= $icon ?>"/>
    <button type='button' class='layui-btn layui-btn-primary btn-icon' id="btn-icon-<?= $name ?>">选择图标</button>
    <div class="icon-show"><i class="<?= $icon ?>"></i></div>
</div>

<div id="icon-box" style="display: none;">
    <style>
        .icon-list li:hover {border: 1px solid #ddd;background-color: #f5f5f5;}
    </style>

    <div class="layui-col width-240" style="margin: 20px 0 10px 50px; position: relative;">
        <input type="text" id="search" class="layui-input" value="" placeholder="搜索" style="padding-left: 28px;"/>
        <i class="iconfont icon-search" style="position: absolute; line-height: 42px; top: 0; left: 8px; color: #888;"></i>
    </div>
    <ul class="layui-row icon-list" style="padding: 20px 36px;">
        <?php foreach ($iconMap as $class => $val) : ?>
            <li class="layui-col" data-class="<?= $class ?>" style="text-align: center;width: 90px;height: 100px;padding: 12px 8px;border-radius: 4px;cursor: pointer;box-sizing: border-box;">
                <div class="icon-class" style="display: block;overflow: hidden;">
                    <i class="iconfont <?= $class ?>" style="font-size: 32px;color: #666;"></i>
                </div>
                <div class="icon-name"><?= $val ?></div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<script>
    $('body').on('click', '.btn-icon', function () {
        layerObj.open({
            type: 1,
            title: '选择图标',
            skin: 'layui-layer-lan layui-layer-custom',
            area: ['640px', '720px'],
            content: $('#icon-box').html(),
            success: function (layero, index) {
                $(layero).on('click', '.icon-list li', function () {
                    var icon = $(this).data('class');
                    $('input[name="<?= $name ?>"]').val('iconfont ' + icon);
                    $('.icon-show i').removeClass().addClass('iconfont ' + icon);
                    layerObj.closeAll();
                });

                $(layero).on('change', '#search', function () {
                    $(layero).find(".icon-list li").hide().filter(":contains('"+($(this).val())+"')").show();
                })
            }
        })
    })
</script>