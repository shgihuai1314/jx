<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/23
 * Time: 20:24
 */

/** @var yii\web\View $this */
/** @var array $items */
/** @var array $permission */

?>
<style>
	.layui-colla-title .layui-form-checkbox {padding-bottom: 4px;}
	.layui-colla-item {position: relative}
	.layui-colla-item .colla-checkbox {position: absolute; top: 0; left: 35px; z-index: 100; padding: 10px 2px; line-height: 14px}
	.layui-colla-item .layui-form-checkbox[lay-skin=primary] span {padding: 1px 6px; font-size: 14px}
	.layui-colla-item .layui-form-checkbox[lay-skin=primary] i {font-size: 14px; width: 20px; height: 20px; line-height: 20px}
	.layui-colla-item .operate-box .layui-form-checkbox {height: 24px; line-height: 23px; padding-right: 22px; margin: 0}
	.layui-colla-item .operate-box .layui-form-checkbox span {padding: 0 6px}
	.layui-colla-item .operate-box .layui-form-checkbox i {width: 22px; font-size: 14px}
</style>
<div class="layui-collapse" lay-accordion>
	<?php foreach ($permission as $key1 => $val1) : ?>
		<div class="layui-colla-item">
			<div class="colla-checkbox">
				<input type="checkbox" name="permission[]" lay-filter="permission" title="<?= $val1['menu_name'] ?>" lay-skin="primary"
						class="<?= substr(md5('a-'.$key1), 8, 8) ?>" id="<?= substr(md5('a-'.$key1), 8, 8) ?>"
						value="<?= $val1['path'] ?>" <?= in_array($val1['path'], $items) ? 'checked' : '' ?>>
			</div>
			<h2 class="layui-colla-title"></h2>
			<div class="layui-colla-content">
				<?php foreach ($val1['children'] as $key2 => $val2) : ?>
					<div class="layui-col-xs-offset1">
						<div class="layui-form-item">
							<input type="checkbox" name="permission[]" lay-filter="permission" lay-skin="primary"
									class="<?= substr(md5('a-'.$key1), 8, 8) . ' ' . substr(md5('b-'.$key2), 8, 8) ?>"
									id="<?= substr(md5('b-'.$key2), 8, 8) ?>" title="<?= $val2['menu_name'] ?>"
									value="<?= $val2['path'] ?>" <?= in_array($val2['path'], $items) ? 'checked' : '' ?>>
						</div>
						<?php
						$children = $val2['children'];
                        foreach ($children as $n => $one) {
                            if ($one['type'] == 1) {
                            	unset($children[$n]);
                            }
                        }
						?>
						<?php if (!empty($children)) : ?>
							<?php foreach ($val2['children'] as $key3 => $val3) : ?>
								<div class="layui-col-xs-offset1">
									<div class="layui-form-item">
										<input type="checkbox" name="permission[]" lay-filter="permission" lay-skin="primary"
												class="<?= substr(md5('a-'.$key1), 8, 8) . ' ' . substr(md5('b-'.$key2), 8, 8) . ' ' . substr(md5('c-'.$key3), 8, 8) ?>"
												id="<?= substr(md5('c-'.$key3), 8, 8) ?>" title="<?= $val3['menu_name'] ?>"
												value="<?= $val3['path'] ?>" <?= in_array($val3['path'], $items) ? 'checked' : '' ?>>
									</div>
									<div class="layui-col-xs-offset1 layui-form-item">
									<?php foreach ($val3['children'] as $key4 => $val4) : ?>
										<div class="layui-col width-120 operate-box">
											<input type="checkbox" name="permission[]"
													class="<?= substr(md5('a-'.$key1), 8, 8) . ' ' . substr(md5('b-'.$key2), 8, 8)
													. ' ' . substr(md5('c-'.$key3), 8, 8) . ' ' . substr(md5('d-'.$key4), 8, 8) ?>"
													id="<?= substr(md5('d-'.$key4), 8, 8) ?>" title="<?= $val4['menu_name'] ?>"
													value="<?= $val4['path'] ?>" <?= in_array($val4['path'], $items) ? 'checked' : '' ?>>
										</div>
									<?php endforeach; ?>
									</div>
									<span class="layui-clear"></span>
								</div>
							<?php endforeach; ?>
						<?php else: ?>
							<div class="layui-col-xs-offset1 layui-form-item">
							<?php foreach ($val2['children'] as $key3 => $val3) : ?>
								<div class="layui-col width-120 operate-box">
									<input type="checkbox" name="permission[]" lay-filter="permission" class="<?= substr(md5('a-'.$key1), 8, 8) . ' ' .
											substr(md5('b-'.$key2), 8, 8) . ' ' . substr(md5('c-'.$key3), 8, 8) ?>"
											id="<?= substr(md5('d-'.$key3), 8, 8) ?>" title="<?= $val3['menu_name'] ?>"
											value="<?= $val3['path'] ?>" <?= in_array($val3['path'], $items) ? 'checked' : '' ?>>
								</div>
							<?php endforeach; ?>
							</div>
							<span class="layui-clear"></span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<script>
    form.on('checkbox(permission)', function (data) {
        var id = $(this).attr('id');
        var child = $(data.elem).parents('.layui-field-box').find('.' + id);
        child.each(function (index, item) {
            item.checked = data.elem.checked;
        });

        if (data.elem.checked) {//如果子菜单选择,则将其父菜单选中
            var parents = $(this).attr('class').split(' ');
            $.each(parents, function (index, item) {
                $('#' + item).prop('checked', true);
            });
        }
        form.render('checkbox');
    })
</script>