//全选
form.on('checkbox(allChoose)', function (data) {
    //console.log(data);
    var child = $(data.elem).parents('table').find('tbody input[type="checkbox"]');
    child.each(function (index, item) {
        item.checked = data.elem.checked;
    });
    form.render('checkbox');
});
//删除
$('.question-delete').on('click', function (data) {
    layer.confirm('确定删除此记录？', {
        btn: ['删除', '取消'] //按钮
        , success: function (layero) {
            var btn = layero.find('.layui-layer-btn0');
            btn.attr('href', data.toElement.href);
        }
    });
    return false;
});
//批量删除
$('#batch-delete-btn').on('click', function () {
    layer.confirm('确定删除此记录？', {
        btn: ['删除', '取消'] //按钮
        , btn1: function (layero) {
            $('#batch-delete').submit();
        }
    });
});
//ifram自适应高度
$("#articleFrame").load(function () {
    var mainheight = $(this).contents().find("body").height() + 30;
    $(this).height(mainheight);
});

function ChangeStatus(id, field) {
    if (isNaN(id)) {
        return false;
    }
    $.post('edit', {id: id, field: field, action: 'ChangeStatus'}, function (data) {
        //console.log(data)
        if (data.status == 0) {
            var html = data.val ? '<i class=\"fa fa-check text-success\"></i>' : '<i class=\"fa fa-times text-danger\"></i>';
            $("#span-" + field + id).html(html);
        }
        else if (data.status == 1) {
            alert(data.message);
        }
    }, 'json')
}