<?php
/**
 * Created by PhpStorm.
 * User: BixChen
 * Date: 2018/5/10
 * Time: 17:35
 */
?>
<style>
    .steps-user {
    width: 100%;
    overflow: hidden;
    margin-bottom: 10px;
        border-radius: 2px;
        box-sizing: border-box;
    }

    .steps-user ul li {
    display: inline-block;
    vertical-align: top;
    }
    .steps-user ul li a{
    display: block;
    width:100%;
    height: 68px;
    }
    .steps-user ul li .select-flow-name {
    text-align: center;
        padding: 0 6px;
    }

    .steps-user ul li .select-flow-name p {
    line-height: 28px;
    }

    .steps-user ul li .select-flow-name span {
    width: 38px;
        height: 38px;
        line-height: 38px;
        background-color: #00a0e9;
        color: #fff;
        display: inline-block;
        border-radius: 50%;
        text-align: center;
        position: relative;
    }

    .steps-user ul li .select-flow-name span .del-icon {
    width: 20px;
        height: 20px;
        line-height: 20px;
        text-align: center;
        font-weight: bold;
        font-size: 18px;
        color: #999;
        cursor: pointer;
        position: absolute;
        right: -12px;
        top: 0;
        display: none;
    }

    .steps-user ul li a:hover span .del-icon {
    display: block;
}

    .steps-user ul li .line-right {
    width: 20px;
        height: 38px;
        line-height: 38px;
    }

    .steps-user ul li .line-right .fa {
    font-size: 18px;
        color: #999;
        vertical-align: middle;
    }

    /*添加审批人*/
    .add-user {
    width: 46px;
        height: 46px;
        line-height: 46px;
        text-align: center;
    }

    .add-user .layui-icon {
    font-size: 40px;
        vertical-align: bottom;
        color: #999
    }
</style>

<div class="layui-form-item <?= $input_class ?>">
    <input type="hidden" class="select_flow_hidden" value="<?= $input_name ?>">
    <label class="layui-form-label">
        <?= $input_label;?>
    </label>
    <div class="layui-input-block">
        <div class="steps-user">
            <?php if (empty($user_arr[0]['name'])): ?>
                <input type="hidden" lay-verify="required" value="" id="select_flow" class="layui-input">
                <ul class="fl user_select_flow_ul">
                </ul>
            <?php else: ?>
                <ul class="fl user_select_flow_ul">
                    <?php foreach ($user_arr as $k => $v): ?>
                        <?php if(!empty($v['name'])):?>
                            <li class="user_select_flow_li" data-id="U2">
                                <div class="select-flow-name fl">
                                    <span><?=mb_substr($v['name'], -2,2,'utf-8')?><i class="fa fa-close del-icon" onclick="deleteSelectFlow($(this))"></i></span>
                                    <p><?= $v['name'] ?></p>
                                    <input class="user-id" type="hidden" value="U<?= $v['user_id'] ?>"
                                           name="<?=$input_name?>[]">
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

                <div class="add-user fl">
                    <i class="layui-icon">&#xe608;</i>
                </div>

        </div>
    </div>
</div>
<script>


    //选择用户
    $('.add-user').click(function () {
        var select_flow_hidden = $(".select_flow_hidden").val();
        var options = {
            select_max: 1,
        };

        var ids = $(this).data('id');
        if (typeof ids == 'undefined') {
            var input = $('input[name="ids[]"]:checked');
            ids = [];
            input.each(function (i) {
                var obj = input[i];
                ids.push($(obj).val());
            });
        }

        parent.layer.open({
            type: 2,                                    //iframe弹窗
            title: '分享给',                            //弹窗标题
            skin: 'layui-layer-lan layui-layer-custom', //窗口皮肤，可自定义
            area: ['720px', '560px'],                   //窗口大小
            btn: ['确认', '取消'],                      //按钮
            content: indexScript + '/site/user-group-select?options=' + encodeURI(JSON.stringify(options)),
            scrollbar: false,
            resize: false,                              //窗口是否允许拉伸
            success: function (select_flow, index) {
            },
            yes: function (index, select_flow) {               //点击确定按钮回调
                var body = parent.layer.getChildFrame('body', index);
                var items = body.find('.select-box-acheive li.selected-item');
                var selectUserIds = [];//选择用户的id
                var selectUuserName = [];//选择用户的name
                $.each(items, function () {
                    var selectId = $(this).data('id');
                    var selectName = $(this).data('name');
                    selectUserIds.push(selectId);
                    selectUuserName.push(selectName);
                });

                if ($.inArray(selectUserIds[0], haveId) == -1) {
                    var ht = ' <li class="user_select_flow_li" data-id="' + userIds[0] + '">' +
                        '<a href="javascript:;">' +
                        '<div class="select-flow-name fl"> ' +
                        '<span>' + selectUuserName[0].slice(-2) + '<i class="layui-icon del-icon" onclick="deleteSelectFlow($(this))">&#x1006;</i></span> ' +
                        '<p>' + selectUuserName[0] + '</p> ' +
                        '<input class="user-id" type="hidden" value="' + userIds[0] + '" name="'+select_flow_hidden+'[]">' +
                        '</div>' +
                        '</a>' +
                        '</li>';
                    $(".user_select_flow_ul").append(ht);
                    getSelectFlowId();
                } else {
                    //提示
                    parent.layer.msg('该用户已存在', {offset: '150px'});
                }
                parent.layer.close(index);
            }
        });

        $(".apprpval-required").val('1');

    });

    //已选择审核人的id
    var haveId = [];
    function getSelectFlowId() {
        haveId = [];//清空已选择的数组
        $('.user_select_flow_li').each(function (index, el) {
            var id = $(el).attr('data-id');
            haveId.push(id);
        });
        if (haveId.length > 0) {
            $('#select_flow').val('用户已选择');//当用户有选择的时候给隐藏域赋值
        } else {
            $('#select_flow').val('')
        }
    }

    //删除用户
    function deleteSelectFlow(obj) {
        $(obj).parents('.user_select_flow_li').remove();
        getSelectFlowId();

        if($(".user_select_flow_ul li").length == 0){
            $(".apprpval-required").val('');
        }
    }
</script>

