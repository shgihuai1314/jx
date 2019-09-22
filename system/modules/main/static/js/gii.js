
// 选中表的字段信息
var columns = null;
// 选中表的主键
var primaryKey = 'id';

// 数据库选中
$("#table_name").on('change', function () {
    // 详细信息内容隐藏
    $("#detail-content").hide();
    var tableName = $(this).val().replace('tab_', '');

    // 自动根据驼峰命名法生成模型名
    var modelClass = '';
    $.each(tableName.split('_'), function () {
        if (this.length > 0)
            modelClass += this.substring(0, 1).toUpperCase() + this.substring(1);
    });
    $('#model_class').val(modelClass).blur();

    // 自定义字段标签
    $("#table_label").html('<blockquote class="layui-elem-quote custom-quote"></blockquote>');
    if (table.length != '') {
        // 获取表格字段信息
        $.get('ajax', {action: 'tableInfo', table: $(this).val()}, function (res) {
            primaryKey = res.data.primaryKey;// 主键
            columns = res.data.columns;// 字段列表

            $.each(columns, function (index, column) {
                $("#table_label blockquote").append('' +
                    '<div class="layui-col">' +
                    '    <div class="layui-form-item">' +
                    '        <label class="layui-form-label">' + index + '</label>' +
                    '        <div class="layui-input-block width-180">' +
                    '            <input type="text" class="layui-input" name="label[' + index + ']" ' +
                    '               value="' + column.comment + '" required lay-verify="required"/>' +
                    '        </div>' +
                    '    </div>' +
                    '</div>');
            });
            form.render();
        }, 'json');
    } else {
        // 显示确认按钮
        $("#base-content .submit-box").show();
    }
});

$("#module_id").on('change', function () {
    if ($(this).val() == '') {
        layerObj.msg('请选择所属模块！', {icon: 5, offset: '180px', anim: 6});
        $("#detail-content").hide();
        $("#base-content .submit-box").hide();
    } else {
        var modelClass = 'system\\modules\\' + $("#module_id").val() + '\\models\\' + $("#model_class").val();
        // 判断模型类是否存在
        $.ajax({
            url: 'ajax',
            data: {action: 'check-model', modelClass: modelClass},
            dataType: 'json',
            success: function (res) {
                if (res.code == 0) {
                    $("#base-content .submit-box").show();
                } else {
                    layerObj.msg('该模型已存在！', {icon: 5, offset: '180px', anim: 6});
                    $("#detail-content").hide();
                    $("#base-content .submit-box").hide();
                }
            }
        })
    }
});
// 进入详细信息设置
$(".btn-next").on('click', function () {
    layerObj.load(2);
    // 初始化详细设置内容
    $(".item").html('');
    $(".input-item").val('');


    $.each(columns, function (index, column) {
        var label = $("input[name='label[" + index + "]']").val();
        label = label.length == 0 ? index : label;
        // 日志目标字段
        $("#target_name").append("<option value='" + index + "'" + (index == 'name' ? ' selected' : '') + ">" + label + "</option>");
        // 日志记录字段
        $("#normal_field").append("<input type='checkbox' class='layui-input' name='normal_field[]' " +
            "lay-skin='primary' value='" + index + "' title='" + label + "'>");
        // 日志忽略字段
        $("#except_field").append("<input type='checkbox' class='layui-input' name='except_field[]' " +
            "lay-skin='primary' value='" + index + "' title='" + label + "'>");


        var isRequired = !column.allowNull && column.defaultValue == null && !column.autoIncrement;
        var isInteger = column.phpType == 'integer' && !column.autoIncrement;
        var isString = column.phpType == 'string' && !column.autoIncrement;
        // 必填规则
        $("#required_rule").append("<option value='" + index + "'" + (isRequired ? ' selected' : '') + ">" + index + "</option>");
        // 整型规则
        $("#integer_rule").append("<option value='" + index + "'" + (isInteger ? ' selected' : '') + ">" + index + "</option>");
        // 字符串规则
        $("#string_rule").append("<option value='" + index + "'" + (isString ? ' selected' : '') + ">" + index + "</option>");
        // 安全规则
        $("#safe_rule").append("<option value='" + index + "'" +
            (!isRequired && !isInteger && !isString && !column.autoIncrement ? ' selected' : '') + ">" + index + "</option>");

        // 属性字段
        $("#attribute_fields").append("<input type='checkbox' class='layui-input' name='attribute_field[]' " +
            "lay-skin='primary' lay-filter='attribute_field' value='" + index + "' title='" + index + "'>");
    });

    $('#model_name').val($("#table_name").val());
    $('#primaryKey').val(primaryKey[0]);
    $(".select2-multiple").select2({multiple: true});
    form.render();
    layerObj.closeAll('loading');

    $("#detail-content").show().siblings().hide();
    layerObj.closeAll('loading');
});
// 返回到基本信息设置
$(".btn-back").on('click', function () {
    $("#preview-content table tbody").html('');
    $("#base-content").show().siblings().hide();
    $("#attribute_options").html('');
    form.render();
});
// 预览
$(".btn-preview").on('click', function () {
    var modelName = 'system\\modules\\' + $("#module_id").val() + '\\models\\' + $("#model_class").val() + '.php';
    var className = $("#model_class").val() + '.php';
    $("#preview-content table tbody").append('' +
        '<tr>' +
        '    <td>' +
        '        <input type="checkbox" lay-skin="primary" name="generate[]" value="model" checked/>' +
        '    </td>' +
        '    <td>Model</td>' +
        '    <td>' + className + '</td>' +
        '    <td style="text-align: left;">' + modelName + '</td>' +
        '    <td>' +
        '        <a class="layui-btn btn-view layui-btn-xs layui-btn-normal btn-view" data-generate="model">查看</a>' +
        '    </td>' +
        '</tr>');

    // 是否生成控制器
    var controllerFlag = $("input[name='controller_flag']:checked").val();
    if (controllerFlag == 1) {
        var controllerName = $("#controller_name").val();
        // 首字母大写
        controllerName = controllerName.substring(0, 1).toUpperCase() + controllerName.substring(1) + 'Controller.php';
        var controllerClass = 'system\\modules\\' + $("#module_id").val() + '\\controllers\\' + controllerName;
        $("#preview-content table tbody").append('' +
            '<tr>' +
            '    <td>' +
            '        <input type="checkbox" lay-skin="primary" name="generate[]" value="controller" checked/>' +
            '    </td>' +
            '    <td>Controller</td>' +
            '    <td>' + controllerName + '</td>' +
            '    <td style="text-align: left;">' + controllerClass + '</td>' +
            '    <td>' +
            '        <a class="layui-btn btn-view layui-btn-xs layui-btn-normal btn-view" data-generate="controller">查看</a>' +
            '    </td>' +
            '</tr>');

        var actionIndex = false;
        var actionform = false;
        $.each($("input[name='controller_action[]']:checked"), function () {
            if ($(this).val() == 'index') {
                actionIndex = true;
            }
            if ($(this).val() == 'form') {
                actionform = true;
            }
        });
        if (actionIndex) {
            $("#preview-content table tbody").append('' +
                '<tr>' +
                '    <td>' +
                '        <input type="checkbox" lay-skin="primary" name="generate[]" value="index" checked/>' +
                '    </td>' +
                '    <td>View</td>' +
                '    <td>index.php</td>' +
                '    <td style="text-align: left;">system\\modules\\' + $("#module_id").val() + '\\views\\' + $("#controller_name").val() + '\\index.php</td>' +
                '    <td>' +
                '        <a class="layui-btn btn-view layui-btn-xs layui-btn-normal btn-view" data-generate="index">查看</a>' +
                '    </td>' +
                '</tr>');
        }
        if (actionform) {
            $("#preview-content table tbody").append('' +
                '<tr>' +
                '    <td>' +
                '        <input type="checkbox" lay-skin="primary" name="generate[]" value="form" checked/>' +
                '    </td>' +
                '    <td>View</td>' +
                '    <td>form.php</td>' +
                '    <td style="text-align: left;">system\\modules\\' + $("#module_id").val() + '\\views\\' + $("#controller_name").val() + '\\form.php</td>' +
                '    <td>' +
                '        <a class="layui-btn btn-view layui-btn-xs layui-btn-normal btn-view" data-generate="form">查看</a>' +
                '    </td>' +
                '</tr>');
        }
    }
    form.render();
    $("#preview-content").show().siblings().hide();
});
// 查看代码
$("#preview-content").on('click', ".btn-view", function () {
    var generate = $(this).data('generate');
    var fileName = $(this).parents('tr').find('td:eq(3)').text();
    // 获取代码内容
    $.ajax({
        type: "POST",
        url: indexScript + '/main/gii/preview',
        data: 'type=' + generate + '&' + $("form").serialize(),
        success: function (res) {
            $("#previewCode").html('<pre class="layui-code">' + res + '</pre>');
            layui.use('code', function () {
                layui.code({
                    title: fileName,
                    skin: 'notepad'
                });
            });

            $("#previewCode").find('.layui-code-h3 a').remove();
            var previewCode = $("#previewCode").html();
            layerObj.open({
                type: 1,
                title: '',
                content: previewCode,
                area: ['880px', '800px'],
                shadeClose: true
            })
        }
    });
});
// 生成文件
$(".btn-generate").on('click', function () {
    var generate = $("input[name='generate[]']:checked");
    if (generate.length == 0) {
        layerObj.msg("请选择要生成的文件！", {icon: 5, offset: '180px', anim: 6});
        return false;
    }

    files = [];
    generate.each(function () {
        files.push($(this).val());
    });

    $.ajax({
        type: "POST",
        url: indexScript + '/main/gii/generate',
        data: 'files=' + files.join(",") + '&' + $("form").serialize(),
        dataType: 'json',
        success: function (res) {
            if (res.code == 0) {
                layerObj.msg("操作成功", {offset: '180px'});
                window.location.reload();
            } else {
                layerObj.msg("文件生成失败！", {icon: 5, offset: '180px', anim: 6});
            }
        }
    });
});

form.on('radio(log_flag)', function (data) {
    if (this.value == 1) {
        $('.log_flag_box').show();
    } else {
        $('.log_flag_box').hide();
    }
});
form.on('checkbox(attribute_field)', function (data) {
    if (data.elem.checked) {
        $("#attribute_options").append('' +
            '<div class="layui-form-item" id="attribute_' + data.value + '">' +
            '    <label class="layui-form-label">' + data.value + '属性设置</label>' +
            '    <div class="layui-input-block">' +
            '        <blockquote class="layui-elem-quote custom-quote">' +
            '            <div class="layui-form-item">' +
            '                <label class="layui-form-label">属性值类型</label>' +
            '                <div class="layui-input-block">' +
            '                    <input type="radio" class="layui-input" data-attribute="' + data.value + '" lay-filter="attirbute_type"' +
            '                           name="attirbute[' + data.value + '][type]" value="0" title="固定值" checked/>' +
            '                    <input type="radio" class="layui-input" data-attribute="' + data.value + '" lay-filter="attirbute_type"' +
            '                           name="attirbute[' + data.value + '][type]" value="1" title="配置项"/>' +
            '                    <input type="radio" class="layui-input" data-attribute="' + data.value + '" lay-filter="attirbute_type"' +
            '                           name="attirbute[' + data.value + '][type]" value="2" title="模型方法"/>' +
            '                </div>' +
            '            </div>' +
            '            <div class="layui-form-item layui-col-xs11">' +
            '                <label class="layui-form-label">属性值内容</label>' +
            '                <div class="layui-input-block attirbute_value">' +
            '                    <textarea class="layui-textarea" name="attirbute[' + data.value + '][value][0]"></textarea>' +
            '                    <input type="text" class="layui-input" name="attirbute[' + data.value + '][value][1]"' +
            '                           style="display: none;"/>' +
            '                    <input type="text" class="layui-input" name="attirbute[' + data.value + '][value][2]"' +
            '                           style="display: none;"/>' +
            '                </div>' +
            '                <div class="help-block" style="margin-left: 150px; float: none;">' +
            '                    <span id="' + data.value + '_aux0">每行一组数据，格式：属性id=属性值，如：1=是\r\n0=否</span>' +
            '                    <span id="' + data.value + '_aux1" style="display: none;">配置项的值，如：USER_STATUS_LIST</span>' +
            '                    <span id="' + data.value + '_aux2" style="display: none;">模型方法，如：\\system\\modules\\group\\models\\Group::getNameArr()</span>' +
            '                </div>' +
            '            </div>' +
            '        </blockquote>' +
            '    </div>' +
            '</div>');
        form.render();
    } else {
        $("#attribute_" + data.value).remove();
    }
});
form.on('checkbox(checkAll)', function (data) {
    $("input[name='generate[]']").each(function (index, item) {
        item.checked = data.elem.checked;
    });
    form.render();
});
form.on('radio(attirbute_type)', function (data) {
    var attribute = $(this).data("attribute");
    $("[name='attirbute[" + attribute + "][value][" + data.value + "]']").show().siblings().hide();
    $("#" + attribute + "_aux" + data.value).show().siblings().hide();
});
form.on('radio(controller_flag)', function (data) {
    if (this.value == 1) {
        $('.controller_flag_box').show();
    } else {
        $('.controller_flag_box').hide();
    }
});