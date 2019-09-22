<?php
/**
 * Created by PhpStorm.
 * User: pq
 * Date: 2017/11/1
 * Time: 14:22
 */
use yii\helpers\Url;

$this->title = '模块消息';

system\modules\notify\assets\NotifyAsset::register($this);
?>

<div class="notify layui-form">
    <div class="notify-tp">
        <div class="check-box">
            <input type="checkbox" name="like1[]" lay-skin="primary" title="" lay-filter="alldelate">
        </div>
        <button type="button" class="layui-btn btn-all-del">删除</button>
        <button type="button" class="layui-btn layui-btn-primary">返回</button>
    </div>

    <div class="notify-item-block">
        <ul class="layui-timeline">
            <?php if(!empty($data)):?>
            <?php foreach ($data as $key=>$val):?>
            <li class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                <div class="layui-timeline-content layui-text">
                    <h3 class="layui-timeline-title"><?= $key?></h3>
                    <ul class="time-line-ul">
                        <?php foreach ($val as $k=>$v):?>
                        <li>
                            <?php if ($v['url']):?>
                                <?php 
                                    $url = $v['url'];
                                    if (substr($url, 0, 1) != '/') {
                                        $url = '/'.$url;
                                    }
                                ?>
                                <a href="<?= Url::to([$url])?>" <?= $v['module'] == 'workflow' ? 'target="_blank"' : ''?>><?= $v['content']?></a>
                            <?php else:?>
                                <?= $v['content']?>
                            <?php endif;?>
                            <div class="item-date layui-row">
                                <div class="layui-col-xs6">
                                    <input type="checkbox" name="is_delete[]" value="<?= $v['message_id']?>" lay-skin="primary" title="<?= date('m-d H:i',$v['created_at'])?>">
                                </div>
                                <div class="layui-col-xs6">
                                    <span class="deleta-btn btn-one-del" data-id ="<?= $v['message_id']?>">删除</span>
                                </div>
                            </div>
                        </li>
                        <?php endforeach;?>
                    </ul>
                </div>
            </li>
            <?php endforeach;?>

            <li class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                <div class="layui-timeline-content layui-text">
                    <div class="layui-timeline-title">没有更多了</div>
                </div>
            </li>
            <?php else:?>
                <li><span>暂无最新消息</span></li>
            <?php endif;?>
        </ul>
    </div>
</div>

<script>
    layui.use(['form', 'jquery'], function () {
        var form = layui.form
            , $ = layui.jquery;

        //全选
        form.on('checkbox(alldelate)', function(data){
            var child = $(data.elem).parents('.notify').find('.notify-item-block input[type="checkbox"]');
            child.each(function(index, item){
                item.checked = data.elem.checked;
            });
            form.render('checkbox');

        });

        //鼠标移到板块上面 显示查看更多 跟删除按钮
        $(".time-line-ul li").each(function (index, el) {
            $(el).on("mouseenter", function () {
                $(el).find(".deleta-btn").show()
            }).on('mouseleave', function () {
                $(el).find(".deleta-btn").hide()
            });
        })
    });

    //返回按钮
    $(".layui-btn-primary").click(function (){
        window.history.go(-1);
    });
    //批量删除表格数据
    $('.btn-all-del').click(function () {
        var checked = $('input[name="is_delete[]"]');
        var ids = [];
        checked.each(function (index, element) {
            if (element.checked) {
                ids.push($(element).val())
            }
        });

        if (ids.length == 0) {
            parent.layer.msg('请选择要删除的数据！', {
                offset: '150px'
            });
        } else {
            parent.layer.confirm('确定要删除这些数据吗？', {icon: 3, title: '提示', offset: '150px'}, function (index) {
                $.post('ajax', $.extend({
                    ids: ids,
                    type:'btn-all-del',
                    status : 1
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
    });

    //删除表格数据
    $('.btn-one-del').click(function () {
        var id = $(this).data('id');
        parent.layer.confirm('确定要删除该数据吗？', {icon: 3, title: '提示', offset: '150px'}, function (index) {
            $.post('ajax', $.extend({
                id: id,
                type: 'btn-one-del',
                status : 1
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
</script>
