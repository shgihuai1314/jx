/* 数据库备份 */
var CheckedVal = $('#system_type input:radio:checked').val();

if (CheckedVal == 'custom') {
    $("#database").show()
} else {
    $("#database").hide()
}

form.on('radio(radioChoose)', function (data) {
    var CheckedVal = data.value;
    if (CheckedVal == 'custom') {
        $("#database").show()
    } else {
        $("#database").hide()
    }
});

//备份里面的全选
form.on('checkbox(allChoose1)', function (data) {
    var child = $(data.elem).parents('#system_database').find('input[name="table_name[]"]');
    child.each(function (index, item) {
        item.checked = data.elem.checked;
    });
    form.render('checkbox');
});

//恢复里面的全选
form.on('checkbox(allChoose2)', function (data) {
    var child = $(data.elem).parents('table').find('tbody input[type="checkbox"]');
    child.each(function (index, item) {
        item.checked = data.elem.checked;
    });
    form.render('checkbox');
});
