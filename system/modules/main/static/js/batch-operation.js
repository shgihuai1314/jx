/* 批量处理 */
layui.upload.render({
    elem: '#upload-template',
    url: 'upload-template',
    exts: 'xls'
    , done: function (res) {
        if (res.code == 0) {
            $('#upload-template').html(res.data);
            $('#template-value').val(res.data);
            $('#model-name').val($('#table-name-select option:checked').val());
            $('#field_constraint').val($('#field-constraint-select option:checked').val());
        }
        parent.layer.msg(res.message, {offset: '150px'});
    }
});


//选择模型勾选要操作的数据
form.on('select(table-name)', function (data) {
    var model_name = data.value;
    var url = 'get-field';
    $.get(url, {model_name: model_name}, function (data) {
        if (data.code == 0) {
            $('.field_name').html('');
            var html = '';
            var html2 = '';
            var fields = data.data;
            $.each(fields, function (key, value) {
                html += '<input type=\"checkbox\" name=\"' + key + '\" lay-skin=\"primary\" title=\"' + value + '\">';
                html2 += '<option value=\"' + key + '\">' + value + '<\/option>';
            });
            $('.field_name').eq(0).append(html);
            $('.field_name').eq(1).append(html2);
            form.render();
        } else {
            $('.field_name').html('');
            parent.layer.msg(data.message, {offset: '150px'});
        }
    }, 'json');
});

//默认
/*var model_name = $('#table-name-select option:selected') .val();
 var url ='get-field';
 $.get(url,{model_name:model_name},function(data){
 if(data.code==0){
 $('.field_name').html('');
 var html = '';
 var html2 = '';
 var fields = data.data;
 $.each(fields, function (key, value) {
 html +='<input type=\"checkbox\" name=\"'+key+'\" lay-skin=\"primary\" title=\"'+value+'\">';
 html2 +='<option value=\"'+key+'\">'+value+'<\/option>';
 });
 $('.field_name').eq(0).append(html);
 $('.field_name').eq(1).append(html2);
 form.render();
 }else{
 $('.field_name').html('');
 layer.msg(data.message);
 }
 }, 'json');*/

//选择要约束的字段
form.on('select(field-constraint)', function (data) {
    var dateVal = data.value;

    var nameval = $('#field_name').find('input[type="checkbox"]');

    nameval.each(function (index, item) {
        if ($(this).attr('name') == dateVal) {
            item.checked = true;
        }
        //console.log($(this).attr('name'))
    });
    form.render('checkbox');
});

//下载模板
$('#down-template').click(function () {
    var field_name = $('.field_names').eq(0).find('input[type="checkbox"]:checked');
    var field_names = [];
    var model_name = $('#table-name-select option:checked').val();
    field_name.each(function (index) {
        field_names.push($(this).attr('title'));
    });
    $.get('down-template', {field_names: field_names, model_name: model_name}, function (data) {
        parent.layer.msg(JSON.parse(data).message, {offset: '150px'});
        if (JSON.parse(data).code == 0) {
            window.open(JSON.parse(data).data.url);
        }
    });
});