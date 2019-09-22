<?php

use \system\modules\main\models\Modules;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

$this->title = '消息中心';

system\modules\notify\assets\NotifyAsset::register($this);
$module = ArrayHelper::index(Modules::getAllModule(), 'module_id');
?>
<div class="notify layui-form ">
    <div class="notify-tp">
        <div class="check-box">
            <input name="<?= Yii::$app->request->csrfParam ?>" type="hidden"
                   value="<?= Yii::$app->request->csrfToken ?>">
            <input type="checkbox" lay-skin="primary" title="" lay-filter="allread">
        </div>
        <button type="button" class="layui-btn btn-batch-read">标记为已读</button>
        <button type="button" class="layui-btn layui-btn-primary btn-all-del">删除</button>
    </div>

    <div class="notify-block">
        <?php foreach ($data as $key => $val): ?>
            <?php $isNew = array_key_exists('new', $val); ?>
            <div class="notify-item layui-row">
                <div class="layui-col-xs1 notify-icon">
                    <div class="msg-pre">
                        <?php if ($val['count'] > 0): ?>
                            <span class="no-read-msg"><?= $val['count'] ?></span>
                        <?php endif; ?>
                        <a href="<?= Url::to(['view', 'module' => $key]) ?>">
                            <img src="<?= empty($module[$key]['icon']) ? '/static/images/icon.jpg' : $module[$key]['icon']; ?>"">
                        </a>
                    </div>
                </div>
                <div class="layui-col-xs11 notify-info">
                    <?php if ($isNew): ?>
                        <ul class="notify-info-ul">
                            <li><a href="<?= Url::to(['view', 'module' => $key]) ?>"
                                   class="edit-read"><strong><?= $module[$key]['name'] ?>:</strong></a></li>
                            <?php foreach ($val['new'] as $k => $v): ?>
                                <li><a href="<?= Url::to([$v['url']]) ?>" class="edit-read" <?= $key == 'workflow' ? 'target="_blank"' : ''?>
                                       m-id="<?= $v['message_id'] ?>"><?= $v['content'] ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="layui-row notify-date">
                            <div class="layui-col-xs6 al">
                                <input type="checkbox" name="is_read[]" value="<?= $key ?>" lay-skin="primary"
                                       title="<?= date('Y-m-d H:i:s', $v['created_at']) ?>">
                            </div>
                            <div class="layui-col-xs6 show-item-btn">
                                <div class="layui-col-xs2 layui-col-xs-offset9 more-item">
                                    <a href="<?= Url::to(['view', 'module' => $key]) ?>" class="system-tip"
                                       data-tip="查看更多">
                                        <i class="fa fa-navicon"></i>
                                    </a>
                                </div>
                                <div class="layui-col-xs1 deleta-item">
                                    <i class="iconfont icon-shanchu btn-one-del" data-id="<?= $key ?>"></i>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <ul class="notify-info-ul">
                            <li><a href="<?= Url::to([$val['last']['url']]) ?>" class="edit-read" <?= $key == 'workflow' ? 'target="_blank"' : ''?>
                                   m-id="<?= $val['last']['message_id'] ?>"><strong><?= $module[$key]['name'] ?>:</strong> <?= $val['last']['content'] ?></a></li>
                        </ul>
                        <div class="layui-row notify-date">
                            <div class="layui-col-xs6 al">
                                <input type="checkbox" name="is_read[]" value="<?= $key ?>" lay-skin="primary"
                                       title="<?= date('Y-m-d H:i:s', $val['last']['created_at']) ?>">
                            </div>
                            <div class="layui-col-xs6 show-item-btn">
                                <div class="layui-col-xs2 layui-col-xs-offset9 more-item">
                                    <a href="<?= Url::to(['view', 'module' => $key]) ?>" class="system-tip"
                                       data-tip="查看更多">
                                        <i class="fa fa-navicon"></i>
                                    </a>
                                </div>
                                <div class="layui-col-xs1 deleta-item ">
                                    <i class="iconfont icon-shanchu btn-one-del" data-id="<?= $key ?>"></i>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<script>
    //全选
    form.on('checkbox(allread)', function (data) {
        var child = $(data.elem).parents('.notify').find('.notify-date input[type="checkbox"]');
        child.each(function (index, item) {
            item.checked = data.elem.checked;
        });
        form.render('checkbox');
    });

    //鼠标移到板块上面 显示查看更多 跟删除按钮
    $(".notify-item").each(function (index, el) {
        $(el).on("mouseenter", function () {
            $(el).find(".show-item-btn").show()
        }).on('mouseleave', function () {
            $(el).find(".show-item-btn").hide()
        });
    });

    //批量删除表格数据
    $('.btn-all-del').click(function () {
        var checked = $('input[name="is_read[]"]');
        var ids = [];
        checked.each(function (index, element) {
            if (element.checked) {
                ids.push($(element).val())
            }
        })

        if (ids.length == 0) {
            parent.layer.msg('请选择要删除的数据！', {
                offset: '150px'
            });
        } else {
            parent.layer.confirm('确定要删除这些数据吗？', {icon: 3, title: '提示', offset: '150px'}, function (index) {
                $.post('ajax', $.extend({
                    ids: ids,
                    type: 'btn-all-del',
                }, param), function (res) {
                    parent.layer.msg(res.code == 0 ? '删除成功' : '删除失败，error：' + res.msg, {
                        offset: '150px'
                    });
                    if (res.code == 0) {
                        window.location.reload();
                    }
                }, 'json');
            });
        }
    })

    //批量修改已读状态
    $('.btn-batch-read').click(function () {
        var checked = $('input[name="is_read[]"]');
        var ids = [];
        checked.each(function (index, element) {
            if (element.checked) {
                ids.push($(element).val())
            }
        })
        //alert(ids);
        if (ids.length == 0) {
            parent.layer.msg('请选择要修改的数据！', {
                offset: '150px'
            });
        } else {
            parent.layer.confirm('确定要修改这些数据吗？', {icon: 3, title: '提示', offset: '150px'}, function (index) {
                $.post('ajax', $.extend({
                    ids: ids,
                    type: 'btn-batch-read',
                }, param), function (res) {
                    parent.layer.msg(res.code == 0 ? '修改成功' : '修改失败，error：' + res.msg, {
                        offset: '150px'
                    });
                    if (res.code == 0) {
                        window.location.reload();
                    }
                }, 'json');
            });
        }
    })

    //删除表格数据
    $('.btn-one-del').click(function () {
        var id = $(this).data('id');
        parent.layer.confirm('确定要删除该数据吗？', {icon: 3, title: '提示', offset: '150px'}, function (index) {
            $.post('ajax', $.extend({
                id: id,
                type: 'btn-one-del',
            }, param), function (res) {
                parent.layer.msg(res.code == 0 ? '删除成功' : '删除失败，error：' + res.msg, {
                    offset: '150px'
                });
                if (res.code == 0) {
                    window.location.reload();
                }
            }, 'json');
        });
    });

    //单个修改已读状态数据
    $('.edit-read').click(function () {
        var id = $(this).attr('m-id');
        $.post('ajax', $.extend({
            id: id,
            type: 'edit-read',
        }, param), function (res) {

        }, 'json');
    });

</script>