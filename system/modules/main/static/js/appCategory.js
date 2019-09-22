
    //全选
form.on('checkbox(allChoose)', function (data) {
    var child = $(data.elem).parents('table').find('tbody input[type="checkbox"]');
    child.each(function (index, item) {
        item.checked = data.elem.checked;
    });
    form.render('checkbox');
});

//删除
$('.question-delete').on('click', function () {

    var category = $('.question-delete').index($(this));//得到当前的索引
    var category_id = $('.category_id').eq(category).val();
    parent.layer.confirm('确认删除此记录？', {
        btn: ['删除', '取消'] //按钮
    }, function () {
        $.get(url_delete + '?' + 'id=' + category_id, function (data) {
            parent.layer.msg(data.message, {offset: '150px'});
            if (data.code == 0) {
                parent.location.href = url_list;//刷新页面
            }
        }, 'json');
    }, function () {

    });
});

//删除所选
$("#batch-delete-btn").on('click', function () {
    var batchid = '';
    $(".category_id:checkbox:checked").each(function () { // 遍历多选框
        batchid += $(this).val() + ',';
    });
    parent.layer.confirm('确认删除此记录？', {
        btn: ['删除', '取消'] //按钮
    }, function () {
        $.get(url, {id: batchid}, function (data) {
            //console.log(data);
            parent.location.href = url_list;//刷新页面
            if (data.code == 0) {
                parent.layer.msg(data.message, {offset: '150px'});
            }
        }, 'json')

    }, function () {

    });

});


//自适应ifram高度
$("#categoryFrame").load(function () {
    var mainheight = $(this).contents().find("body").height() + 30;
    $(this).height(mainheight);
});

//返回按钮
$('#return').click(function () {
    $('#goHome').remove();
    parent.location.href =list;
})




