//点击tab样式切换与更换div显示
$(".user-batch").eq(0).show().siblings().hide();
$(".dialoge-top-ul li").click(function () {
    var index = $(this).index();//得到当前点击的那个li的索引
    $(".user-batch").eq(index).show().siblings().hide();
    $('#type').html(index);
    $(this).addClass("layui-this");
    $(this).siblings().removeClass("layui-this")
});

//触发弹窗
$("#batch").on('click', function () {
    var batchid = '';
    $(".user_id:checkbox:checked").each(function () { // 遍历多选框
        batchid += $(this).val() + ',';
    });
    if (batchid == false) {
        layer.msg('请选择用户', {
            offset: '63px',
            time: 2000 //2秒关闭（如果不配置，默认是3秒）
        });
    } else {
        layer.open({
            type: 1
            , offset: 't'
            , title: ['请选择', 'font-size:14px; background-color:#1988fa;color:#fff;']
            , area: ['60%', '400px']//设置宽跟高
            , shadeClose: true//是否点击遮罩关闭
            , scrollbar: false//是否允许浏览器显示滚动条
            , shade: 0.01//遮罩层的透明度
            , moveOut: true//是否允许拖拽到窗口外
            , moveType: 1 //拖拽模式，0或者1
            , content: $("#dialoge")
            , closeBtn: false
            , btn: ['确定', '取消']
            , yes: function (index, layero) {
                var type = $('#type').html();
                if (type == 0) {
                    var newId = $("#status").val()
                } else if (type == 1) {
                    var newId = $("#jobs").val()
                } else {
                    var newId = $("#zTreeIdgroup").val()
                }
                var batchType = ['status', 'position', 'group'];
                $.get('batch', {type: batchType[type], newId: newId, batchid: batchid,}, function (data) {
                    if (data.code == 0) {
                        layer.msg(data.message, {
                            offset: '170px',
                            time: 2000 //2秒关闭（如果不配置，默认是3秒）
                        }, function () {
                            window.location.reload();//刷新页面
                        });
                    } else {
                        layer.msg(data.message,{
                            offset: '170px',
                            time: 2000 //2秒关闭（如果不配置，默认是3秒）
                        });
                    }
                });
            }
        });

    }

    var contentHeight = $(".layui-layer-content").height();//框架弹窗内容部分的高度
    var dCHeight = contentHeight - 41;
    $(".dialoge-content").css("height", dCHeight);//自定义内容部分的高度

    //监听状态单选
    form.on('radio(status)', function (data) {
        //console.log(data);
        $('#status').val(data.value);
    });

    //监听岗位单选
    form.on('radio(jobs)', function (data) {
        //console.log(data);
        $('#jobs').val(data.value);
    });
});