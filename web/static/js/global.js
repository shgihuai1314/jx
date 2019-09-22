/** global.js  */

var $ = jQuery = layui.jquery,
    layer = layui.layer, //获取当前窗口的layer对象
    form = layui.form,
    laydate = layui.laydate,
    element = layui.element,
    table = layui.table;

var userAgent = 'pc'; // 平台： pc

var param = [];
var csrf = $('meta[name="csrf-param"]').attr("content");
var token = $('meta[name="csrf-token"]').attr("content");
param[csrf] = token;//post提交时的参数用$.extend({...}, param)可以提交csrf验证参数

// 获取入口脚本 如:admin.php,oa.php,mobile.php
var pathname = window.location.pathname;
if (pathname.indexOf('.php') > -1) {
    indexScript = pathname.substring(0, pathname.indexOf('.php') + 4);
} else {
    indexScript = '';
}

var host = location.protocol + '//' + location.hostname;
if (location.port) {
    host += ':' + location.port;
}
indexScript = host + indexScript;

/**
 * 判断浏览器版本
 * @returns {*}
 * @constructor
 */
function IEVersion() {
    var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
    var isIE = userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1; //判断是否IE<11浏览器
    var isEdge = userAgent.indexOf("Edge") > -1 && !isIE; //判断是否IE的Edge浏览器
    var isIE11 = userAgent.indexOf('Trident') > -1 && userAgent.indexOf("rv:11.0") > -1;
    if(isIE) {
        var reIE = new RegExp("MSIE (\\d+\\.\\d+);");
        reIE.test(userAgent);
        var fIEVersion = parseFloat(RegExp["$1"]);
        if(fIEVersion == 7) {
            return 7;
        } else if(fIEVersion == 8) {
            return 8;
        } else if(fIEVersion == 9) {
            return 9;
        } else if(fIEVersion == 10) {
            return 10;
        } else {
            return 6;//IE版本<=7
        }
    } else if(isEdge) {
        return 12;//edge
    } else if(isIE11) {
        return 11; //IE11
    }else{
        return 99;//不是ie浏览器
    }
}
// IE 8 以下跳转至浏览器升级页
if (IEVersion() < 8) {
    window.location.href = indexScript + "/main/default/unsupportedBrowser";
}

if (!Array.indexOf) {
    Array.prototype.indexOf = function (el) {
        for (var i = 0, n = this.length; i < n; i++) {
            if (this[i] === el) {
                return i;
            }
        }
        return -1;
    }
}

layui.define(['layer'], function (exports) {
    "use strict";

    var common = {
        /**
         * 抛出一个异常错误信息
         * @param {String} msg
         */
        throwError: function (msg) {
            throw new Error(msg);
            return;
        },
        /**
         * 弹出一个错误提示
         * @param {String} msg
         */
        msgError: function (msg) {
            layer.msg(msg, {
                icon: 5
            });
            return;
        }
    };

    exports('common', common);
});

//如果当前页面是iframe子页面,layerObj为父页面的layer;否则为当前页面的layer(self !== top 判断当前页面是否是最上级页面)
var layerObj = self !== top ? parent.layer : layer;

var groups = null;
var groupIds = null;
var positions = null;
var positionIds = null;
var users = null;
var userIds = null;
if (typeof cacheVersion == 'undefined') {
    cacheVersion = 1;
}
$.ajax({
    type: 'get',
    url: indexScript + '/site/user-group-select',
    data: {action: 'save-json-data'},
    async: false,
    dataType: 'json',
    success: function (res) {
        if (res.code == 0) {
            // console.log('数据加载成功');
        }
    }
});

/**
 * 用户选择器初始化
 * @param input
 */
function userSelectInit(input) {
    // var selectArr = [];
    //用户选择器初始化
    $.each(input, function (index, item) {
        if (groups == null) {
            $.ajax({
                type: 'get',
                url: '/data/group.json?v=' + cacheVersion,
                async: false,
                dataType: 'json',
                success: function (data) {
                    groups = data;
                }
            });
        }
        if (positions == null) {
            $.ajax({
                type: 'get',
                url: '/data/position.json?v=' + cacheVersion,
                async: false,
                dataType: 'json',
                success: function (data) {
                    positions = data;
                }
            });
        }
        if (users == null) {
            $.ajax({
                type: 'get',
                url: '/data/user.json?v=' + cacheVersion,
                async: false,
                dataType: 'json',
                success: function (data) {
                    users = data;
                }
            });
        }
        if (groupIds == null) {
            $.ajax({
                type: 'get',
                url: '/data/groupIds.json?v=' + cacheVersion,
                async: false,
                dataType: 'json',
                success: function (data) {
                    groupIds = data;
                }
            });
        }
        if (positionIds == null) {
            $.ajax({
                type: 'get',
                url: '/data/positionIds.json?v=' + cacheVersion,
                async: false,
                dataType: 'json',
                success: function (data) {
                    positionIds = data;
                }
            });

        }
        if (userIds == null) {
            $.ajax({
                type: 'get',
                url: '/data/userIds.json?v=' + cacheVersion,
                async: false,
                dataType: 'json',
                success: function (data) {
                    userIds = data;
                }
            });

        }

        var selectId = item.id == '' ? 'user-group-select-' + index : item.id + '-' + index;
        $(this).addClass('hide');
        $(this).after('<select class="layui-input user-select" id="' + selectId + '" multiple lay-ignore></select>' +
            '<span class="user-group-select-btn" data-select="#' + selectId + '"><i class="fa fa-user"></i></span>');

        // 选择类型
        var select_max = $(this).data('select_max') == undefined ? 0 : $(this).data('select_max');
        var range_type = $(this).data('range_type') == undefined ? 0 : $(this).data('range_type');
        var show_range = $(this).data('show_range') == undefined ? '' : $(this).data('show_range');
        var select_type = $(this).data('select_type') == undefined ? 'department,position,user' : $(this).data('select_type');
        // ID前缀
        var prefix = select_type == 'department' ? 'G' : (select_type == 'position' ? 'P' : 'U');

        var arr = ($(this).val() == '' || $(this).val() == 0) ? [] : $(this).val().split(',');

        for (var i in arr) {
            if (typeof arr[i] != 'string') {
                continue;
            }
            if (!isNaN(arr[i])) {//如果初始值是纯数字ID，则自动根据选择类型补上前缀
                arr[i] = prefix + arr[i];
            }

            var name = '';
            if (arr[i].slice(0, 1) == 'G') {
                if (groupIds[arr[i].slice(1)] == undefined) {
                    name = '';
                    arr.splice(i, 1);
                } else {
                    name = groupIds[arr[i].slice(1)]['name'];
                }
            } else if (arr[i].slice(0, 1) == 'P') {
                if (positionIds[arr[i].slice(1)] == undefined) {
                    name = '';
                    arr.splice(i, 1);
                } else {
                    name = positionIds[arr[i].slice(1)]['pos_name'];
                }
            } else {
                if (userIds[arr[i].slice(1)] == undefined) {
                    name = '';
                    arr.splice(i, 1);
                } else {
                    name = userIds[arr[i].slice(1)]['realname'];
                }
            }

            if (name != '') {
                $("#" + selectId).append("<option value='" + arr[i] + "' selected>" + name + "</option>")
            }
        }

        $(this).val(arr.join(','));

        $("#" + selectId).select2({
            ajax: {
                url: indexScript + '/site/user-group-select?action=search-items',
                data: function (params) {
                    return {
                        search: params.term, // search term
                        range_type: range_type,
                        show_range: show_range,
                        select_type: select_type,
                        page: params.page
                    };
                },
                dataType: 'json',
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    var val = $("#" + selectId).val();
                    if (select_max > 0 && val != null && val.length >= select_max) {
                        data = [];
                    }
                    return {
                        results: data,//itemList
                        pagination: {
                            more: (params.page * 30) < data.length
                        }
                    };
                },
                cache: true,
            },
            placeholder: '请选择',
            escapeMarkup: function (markup) {
                return markup;
            }, // let our custom formatter work
            templateResult: function (repo) {
                if (repo.loading) {
                    return repo.text;
                }
                return '<span><img src="' + repo.avatar + '" class="img-flag" /> ' + repo.text + '</span>';
            },
            templateSelection: function (repo) {
                return repo.full_name || repo.text;
            },
        });
    });

    $('body').off('click', '.user-group-select-btn').on('click', '.user-group-select-btn', function () {
        var obj = $(this).parent().find('.user-group-select');
        var title = $(obj).data('title');
        title = title == undefined ? '选择用户' : title;

        var select = $(this).data('select');
        var val = $(select).val() == null ? '' : $(select).val();

        var options = {
            show_user: $(obj).data('show_user') == undefined ? 1 : $(obj).data('show_user'),
            select_max: $(obj).data('select_max') == undefined ? 0 : $(obj).data('select_max'),
            range_type: $(obj).data('range_type') == undefined ? 0 : $(obj).data('range_type'),
            show_page: $(obj).data('show_page') == undefined ? 'department,position' : $(obj).data('show_page'),
            show_range: $(obj).data('show_range') == undefined ? '' : $(obj).data('show_range'),
            select_type: $(obj).data('select_type') == undefined ? 'department,position,user' : $(obj).data('select_type'),
        };

        layerObj.open({
            type: 2,                                    //iframe弹窗
            title: [title,'background-color:#1988fa;color:#fff'],                               //弹窗标题
            skin: 'layui-layer-lan layui-layer-custom', //窗口皮肤，可自定义
            area: ['720px', '560px'],                   //窗口大小
            btn: ['确认', '取消'],                      //按钮
            content: indexScript + '/site/user-group-select?value=' + val + '&options=' + encodeURI(JSON.stringify(options)),
            scrollbar: false,
            resize: false,                              //窗口是否允许拉伸
            yes: function (index, layero) {               //点击确定按钮回调
                var body = layerObj.getChildFrame('body', index);
                var items = body.find('.select-box-acheive li.selected-item');
                $(select).empty();
                var ids = [];
                $.each(items, function () {
                    var id = $(this).data('id');
                    var name = $(this).data('name');
                    ids.push(id);
                    $(select).append("<option value='" + id + "'>&nbsp;" + name + "</option>")
                });
                $(select).val(ids).trigger("change");
                $(obj).val(ids.join(','));
                layerObj.close(index);                     //如果设定了yes回调，需进行手工关闭
            }
        })
    });

    //选择用户下拉框内容改变同步修改input.user-group-select的内容
    input.parent().find('.user-select').change(function () {
        var obj = $(this).parent().find('input.user-group-select');
        var val = $(this).val();
        $(obj).val((val == null || val == '') ? null : val.join(','));
    });
}

$(document).ready(function () {
    form.render(); // 动态解析表单
    element.render();//动态解析
    // placeholderShow();//placeholder 兼容
    dateRefresh();//初始化日期控件

    table.init('parse-table'); //转化静态表格
    $('table[lay-filter="parse-table"]').remove();//删除原表格

    /*
     * 日期插件可以使用如下方式：<input type="text" class="selectDateTime" data-format="yyyy-mm-dd">
     *     data-format 参数可以省略，默认显示日期时间选择框，具体format请参照官网
     */
    $('.selectDateTime').each(function () {
        var format = $(this).data('format');
        if (format == 'yyyy') {
            laydate.render({
                elem: this,
                type: 'year',
                format: 'yyyy'
            })
        } else if (format == 'yyyy-MM') {
            laydate.render({
                elem: this,
                type: 'month',
                format: 'yyyy-MM'
            })
        } else if (format == 'yyyy-MM-dd') {
            laydate.render({
                elem: this,
                type: 'date',
                format: 'yyyy-MM-dd'
            })
        } else if (format == 'HH:mm:ss') {
            laydate.render({
                elem: this,
                type: 'time',
                format: 'HH:mm:ss'
            })
        } else if (format != '') {
            laydate.render({
                elem: this,
                type: 'datetime',
                format: format
            })
        } else {
            laydate.render({
                elem: this,
                type: 'datetime'
            })
        }
    });

    // tip小提示
    $('.system-tip').on('mouseenter', function () {
        //console.log(this);
        if ($(this).data('tip') != '') {
            var flag = $(this).data('flag');
            layer.tips($(this).attr('data-tip'), this, {
                tips: [flag == undefined ? 2 : flag, '#555'],
                time: 30000
            });
        }
    }).on('mouseleave', function () {
        layer.closeAll('tips');
    });

    // 闪屏消息
    if (typeof flashMsg != 'undefined') {
        $.each(flashMsg, function (type, value) {
            if (type == 'error') {
                layerObj.msg(value, {icon: 5, anim: 6, offset: '150px'});
            } else if (type == 'ok') { // icon: 6, anim: 5,
                layerObj.msg(value, {offset: '150px'});
            } else {
                layerObj.msg(value, {offset: '150px'});
            }
        });
    }

    // 显示成功失败消息
    var success = delCookie('success');
    if (success != null) {//显示操作成功信息
        layerObj.msg(decodeURI(success), {offset: '150px'});
    }
    var error = delCookie('error');
    if (error != null) {//显示操作失败信息
        layerObj.msg(decodeURI(error), {icon: 2, anim: 6, offset: '150px'});
    }
    // 公共方法：删除项目
    $('body').on('click', '.delete-item', function () {
        var url = deleteUrl != undefined ? deleteUrl : '';
        var id = $(this).data('id');
        layerObj.confirm('确认删除此记录？', {
            btn: ['删除', '取消'] //按钮
        }, function () {
            $.get(url + id, function (data) {
                layerObj.msg(data.message);
                if (data.code == 0) {
                    window.location.reload(); //刷新当前页面
                }
            }, 'json');
        }, function () {

        });
    });

    //表格搜索内下拉框选择触发表单提交
    form.on('select(search)', function (data) {
        var f = $(data.elem).parents('form.layui-form');
        f.submit();
    });
    //表格搜索内单选选择触发表单提交
    form.on('radio(search)', function (data) {
        var f = $(data.elem).parents('form.layui-form');
        f.submit();
    });
    //表格内switch开关改变后通过ajax提交
    form.on('switch', function (data) {
        if ($(data.elem).data('filter') != undefined) {
            var checked = $(this).data('checked');
            var unchecked = $(this).data('unchecked');
            $.post('edit?id=' + $(data.elem).data('id'), $.extend({
                field: $(data.elem).data('filter'),
                val: this.checked ? checked : unchecked
            }, param), function (res) {
                if (res.code == 0) {
                    layerObj.msg('修改成功', {offset: '150px'});
                    // window.location.reload();
                } else {
                    layerObj.msg('修改失败，error：' + res.msg, {offset: '150px', icon: 2, anim: 6});
                }
            }, 'json');
        }
    });

    /**
     * 编辑表格单元格内容
     */
    table.on('edit(parse-table)', function (obj) {
        $.post('edit?id=' + obj.data.id, $.extend({
            type: 'ajax',
            field: obj.field,
            val: obj.value
        }, param), function (res) {
            if (res.code == 0) {
                layerObj.msg('修改成功！', {offset: '150px'});
                location.reload();
            } else {
                layerObj.msg(JSON.stringify(res.msg), {offset: '150px', icon: 2, anim: 6});
            }
        }, 'json');
    });
    /**
     * 删除表格数据
     */
    $('body').on('click', '.btn-del', function () {
        var id = $(this).data('id');
        layerObj.confirm('确定要删除该数据吗？', {icon: 3, title: '提示', offset: '150px'}, function (index) {
            $.post('del', $.extend({
                id: id
            }, param), function (res) {
                if (res.code == 0) {
                    layerObj.msg('删除成功', {offset: '150px'});
                    window.location.reload();
                } else {
                    layerObj.msg('删除失败，error：' + res.msg, {offset: '150px', icon: 2, anim: 6});
                }
            }, 'json');
        });
    });
    /**
     * 批量删除表格数据
     */
    $('body').on('click', '.btn-batch-del', function () {
        var ids = [];
        //layui解析表格
        var checkStatus = table.checkStatus('parse-table');
        var checked = checkStatus.data;
        $.each(checked, function (index, data) {
            ids.push(data.id)
        });

        if (ids.length == 0) {
            layerObj.msg('请选择要删除的数据！', {
                offset: '150px'
            });
        } else {
            layerObj.confirm('确定要删除这些数据吗？', {icon: 3, title: '提示', offset: '150px'}, function (index) {
                $.post('del', $.extend({
                    id: ids
                }, param), function (res) {
                    if (res.code == 0) {
                        layerObj.msg('删除成功', {offset: '150px'});
                        window.location.reload();
                    } else {
                        layerObj.msg('删除失败，error：' + res.msg, {offset: '150px', icon: 2, anim: 6});
                    }
                }, 'json');
            });
        }
    });
    /**
     * 分页跳转按钮
     */
    $('.table-page').on('click', '.goto button', function () {
        var href = window.location.href;
        var page = $('#goto').val();
        var min = $('#goto').attr('min');
        var max = $('#goto').attr('max');
        page = page > max ? max : (page < min ? min : page);
        if (page != undefined) {
            href = changeURLPar(href, 'page', page);
        }

        var pageSize = $('#pageSize').val();
        if (pageSize != '' && pageSize > 0) {
            href = changeURLPar(href, 'pageSize', pageSize);
        }
        window.location.href = href;
    });

    /**
     * 用户选择器初始化
     */
    userSelectInit($("input.user-group-select"));

    //图片相册点击显示相册层
    $('.layer-photos-list').on('click', 'img', function () {
        if (self !== top) {// 如果当前页面是iframe页面，将整个图片集html拷贝到父页面的body中隐藏，然后在父页面中调用弹窗显示图片相册
            var id = $(this).attr('id');
            var html = $(this).parent().parent().html();
            var body = $('body', window.parent.document);
            body.find('ul.layer-photos-list').remove();
            body.append('<ul class="layer-photos-list" style="display: none;">' + html + '</ul>');
            layerObj.photos({
                photos: '.layer-photos-list',
                anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机
            });
            $('#' + id, window.parent.document).click();
        } else {// 当前页面为独立页面，直接调用图片相册弹窗
            var body = $('body');
            layer.photos({
                photos: '.layer-photos-list',
                anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机
            });
        }

        // 给图片集弹窗绑定鼠标滚轮事件
        body.off('mousewheel', '#layui-layer-photos').on('mousewheel', '#layui-layer-photos', function(event, delta) {
            // 缩放图片相册
            resizeImg(this, delta);
        })
    });

    /**
     * 对layui图片集中的图片进行放大缩小
     * @param obj
     * @param delta
     */
    function resizeImg(obj, delta) {
        var width = $(obj).parent().width();
        var height = $(obj).parent().height();
        var left = $(obj).parent()[0].offsetLeft;
        var top = $(obj).parent()[0].offsetTop;

        if (delta > 0) {
            left = parseInt(left - width * 1/20);
            top = parseInt(top - height * 1/20);
            width = parseInt(width * 1.1);
            height = parseInt(height * 1.1);
        } else {
            left = parseInt(left + width * 1/20);
            top = parseInt(top + height * 1/20);
            width = parseInt(width * 0.9);
            height = parseInt(height * 0.9);
        }

        $(obj).parent().width(width);
        $(obj).parent().height(height);
        $(obj).height(height);
        $(obj).parent().css('left', left + 'px');
        $(obj).parent().css('top', top + 'px');
    }

    //oa页面内容最小宽度
    function panelMinHight() {
        var height = $(window).height();
        $(".panel").css("min-height", height + "px")
    }

    panelMinHight();

    if (typeof jQuery.fn.select2 != 'undefined') {
        //select2插件初始化
        $('select.select2').select2();
    }

    //  点击查看大图 无遮罩层
    $(".img-view").on('click', function (event) {
        event.stopPropagation();
        var imgShowWidth = 600;//定义图片想显示的宽度
        $(".imgbig-box").remove();
        var imgbox = '<div class="imgbig-box"><img src="" alt="查看大图" style="max-width: ' + imgShowWidth + 'px;"></div>';
        $(this).parents('body').append(imgbox);
        $('.imgbig-box').css({
            'width': '' + imgShowWidth + 'px',
            'background-color': '#fff',
            'position': 'fixed',
            'left': '50%',
            'top': '50%',
            'margin-left': '' - (imgShowWidth) / 2 + 'px',
            'padding': '3px',
            'box-shadow': '0 0 6px rgba(0, 0, 0, .5)',
            'z-index': '99999',
            'text-align': 'center'
        });
        if ($(this).attr('src') != '') {
            $(".imgbig-box img").attr('src', $(this).attr('src'));
            if ($(this).attr('width') >= imgShowWidth) {
                $(".imgbig-box").css({
                    'height': $(".imgbig-box img").height() + 'px',
                    'margin-top': -($(".imgbig-box img").height() / 2) + 'px'
                });
            } else {
                $(".imgbig-box").css({
                    'height': $(".imgbig-box img").height() + 'px',
                    'margin-top': -($(".imgbig-box img").height() / 2) + 'px',
                    'width': $(".imgbig-box img").width() + 'px',
                    'margin-left': -($(".imgbig-box img").width() / 2) + 'px'
                });
            }
            $(document).click(function () {
                $(".imgbig-box").remove();
            });
        }
    });
});

// 获取url中的参数
function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}

// 替换url中的参数
function changeURLPar(destiny, par, par_value) {
    var pattern = par + '=([^&]*)';
    var replaceText = par + '=' + par_value;
    var res = destiny.match(pattern);
    if (res) {
        return destiny.replace(res[0], replaceText);
    }
    else {
        if (destiny.match('[\?]')) {
            return destiny + '&' + replaceText;
        }
        else {
            return destiny + '?' + replaceText;
        }
    }
    return destiny + '\n' + par + '\n' + par_value;
}

//格局化日期：yyyy-MM-dd
function formatDate(date) {
    var myyear = date.getFullYear();
    var mymonth = date.getMonth() + 1;
    var myweekday = date.getDate();
    if (mymonth < 10) {
        mymonth = "0" + mymonth;
    }
    if (myweekday < 10) {
        myweekday = "0" + myweekday;
    }
    return (myyear + "-" + mymonth + "-" + myweekday);
}

//获得某月的天数
function getMonthDays(nowYear, myMonth) {
    var monthStartDate = new Date(nowYear, myMonth, 1);
    var monthEndDate = new Date(nowYear, myMonth + 1, 1);
    var days = (monthEndDate - monthStartDate) / (1000 * 60 * 60 * 24);
    return days;
}

//获取文件名称
function getFileName(path) {
    var pos1 = path.lastIndexOf('/');
    var pos2 = path.lastIndexOf('\\');
    var pos = Math.max(pos1, pos2);
    if (pos < 0) {
        return path;
    }
    else {
        return path.substring(pos + 1);
    }
}

/**
 * 检查文件类型是否支持
 * @param name 要检查的文件名
 * @param exts 支持的后缀名 如jpg|gif|bmp|png
 * @param accept 接收的文件类型 如images、file、video、audio
 * @returns {boolean}
 */
function checkExt(name, exts, accept) {
    var ext = name.substring(name.lastIndexOf(".") + 1, name.length).toLowerCase();
    if (exts != '' && exts.indexOf(ext) == -1) {
        return false;
    } else {
        switch (accept) {
            case"images":
                if ("jpg|png|gif|bmp|jpeg".indexOf(ext) == -1) {
                    return false;
                }
                break;
            case"video":
                if ("avi|mp4|wma|rmvb|rm|flash|3gp|flv".indexOf(ext) == -1) {
                    return false;
                }
                break;
            case"audio":
                if ("mp3|wav|mid".indexOf(ext) == -1) {
                    return false;
                }
                break;
        }
    }
    return true;
}

/**
 * 生成随机字符串
 * @param randomFlag 是否是固定长度
 * @param min 最小长度（randomFlag为true则生成固定长度min的字符串）
 * @param max 最大长度（randomFlag为false时有效）
 * @param arr 随机字符串包含字符的数组，如果不填则是包含0-9|a-z|-_的随机字符串
 * @returns {string}
 */
function randomWord(randomFlag, min, max, arr) {
    var str = "", range = min;
    if (arr == undefined) {
        arr = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '_', '-'];
    }

    // 随机产生
    if (randomFlag) {
        range = Math.round(Math.random() * (max - min)) + min;
    }
    for (var i = 0; i < range; i++) {
        pos = Math.round(Math.random() * (arr.length - 1));
        str += arr[pos];
    }
    return str;
}

//写cookies
function setCookie(name, value) {
    var Days = 30;
    var exp = new Date();
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}

//读cookies
function getCookie(name) {
    var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
    if (arr = document.cookie.match(reg))
        return unescape(arr[2]);
    else
        return null;
}

//删除cookies
function delCookie(name) {
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = getCookie(name);
    if (cval != null)
        document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
    return cval;
}

//ie8下兼容placeholder
function placeholderShow() {
    if (!('placeholder' in document.createElement('input'))) {
        $('input[placeholder],textarea[placeholder]').each(function () {
            var that = $(this),
                text = that.attr('placeholder');
            if (that.val() === "") {
                that.val(text).addClass('placeholder');
            }
            that.focus(function () {
                if (that.val() === text) {
                    that.val("").removeClass('placeholder');
                }
            })
                .blur(function () {
                    if (that.val() === "") {
                        that.val(text).addClass('placeholder');
                    }
                })
                .closest('form').submit(function () {
                if (that.val() === text) {
                    that.val('');
                }
            });
        });
    }
}

function dateRefresh() {
    $('input.date').each(function () {
        var option = {};
        if ($(this).data('type') != undefined) {//类型
            option['type'] = $(this).data('type');
        }
        if ($(this).data('format') != undefined) {//格式
            option['format'] = $(this).data('format');
        }
        if ($(this).data('max') != undefined) {//最大值
            option['max'] = $(this).data('max');
        }
        if ($(this).data('min') != undefined) {//最小值
            option['min'] = $(this).data('min');
        }
        if ($(this).data('range') != undefined) {//是否选择区间
            option['range'] = $(this).data('range');
        }
        laydate.render($.extend({elem: this}, option));
    });
}