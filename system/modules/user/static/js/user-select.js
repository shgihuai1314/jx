/**
 * Created by admin on 2017/10/15.
 */

var groups;
var groupIds;
var positions;
var positionIds;
var users;
var userIds;

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

//用户选择器弹窗对象
var popup = $('#weui-popup-select_user');

//已选择的数据
var checked;
//最大选择数量,默认为0不限制;
var select_max;
//选择范围交集或并集,默认为0(0:交集;1:并集;)
var range_type;
//选择范围,如G1,G2,G3,P1,P2,U1,U2,U3
var show_range;
//选择的类型,department(部门),position(岗位),user(用户)
var select_type;
//显示tab页类型,默认为department,position,user(department:按部门;position:按岗位;user:按用户);
var show_page;
//选择范围分类
var range = {
    department: [],
    position: [],
    user: []
};

//当前路径(部门拥有层级关系,要考虑子部门情况,所以要保存路径)
var path = [];
//当前父ID(岗位没有上下级关系,只需要保存岗位ID)
var parent_id = 0;
//选择显示类型
var type;

var that;
//weui-navbar__item点击事件
popup.find('.weui-navbar .weui-navbar__item').click(function () {
    $(this).addClass('weui-bar__item--on').siblings().removeClass('weui-bar__item--on');
    type = $(this).data('type');

    path = [];
    parent_id = 0;
    $.showLoading();
    setTimeout(function() {getListData(); $.hideLoading();}, 100)
});

var addMembers = function () {
    that = $(this);

    select_max = that.data('select_max') == undefined ? 0 : that.data('select_max');
    range_type = that.data('range_type') == undefined ? 0 : that.data('range_type');
    show_range = that.data('show_range') == undefined ? '' : that.data('show_range');
    select_type = that.data('select_type') == undefined ? 'department,position,user' : that.data('select_type');
    show_page = that.data('show_page') == undefined ? 'department,position' : that.data('show_page'),

    show_range = show_range == '' ? [] : show_range.split(',');
    //显示范围分类
    $.each(show_range, function (index, item) {
        if (item.slice(0, 1) == 'G') {//部门范围
            range.department.push(item.slice(1))
        } else if (item.slice(0, 1) == 'P') {//岗位范围
            range.position.push(item.slice(1))
        } else if (item.slice(0, 1) == 'U') {//用户范围
            range.user.push(item.slice(1))
        }
    });

    select_type = select_type == '' ? [] : select_type.split(',');
    show_page = show_page == '' ? [] : show_page.split(',');
    if (show_page.length > 1) {//可多个tab,显示weui-navbar中对应的weui-navbar__item
        popup.find('.weui-tab__bd').attr('style', 'padding-top: 44px;');
        popup.find('.weui-navbar .weui-navbar__item').hide();
        $.each(show_page, function (index) {
            $('#navbar-' + show_page[index]).show();
        })
    } else {//只有一个显示类型,则不显示weui-navbar
        popup.find('.weui-navbar').hide();
        popup.find('.weui-tab__bd').attr('style', 'padding-top: 0;');
    }

    checked = [];
    // ID前缀(G|P|U) 如果默认值没有前缀,根据select_type类型自动补上前缀
    var prefix = that.data('select_type') == 'department' ? 'G' : (that.data('select_type') == 'position' ? 'P' : 'U');
    val = that.parent().find('.user-group-select').val();
    var arr = val == '' ? [] : val.split(',');
    $.each(arr, function(index, item) {//把默认值push到checked中
        checked.push(isNaN(item) ? item : prefix + item)
    });

    //初始自动点击第一个
    $('#navbar-' + show_page[0]).click();

    popup.find('.yd-title').html(that.data('title'));
    popup.popup();
}
//搜索框内容
var search = '';
//搜索内容
popup.find(".weui-search-bar__input").keyup(function () {
    search = $(this).val();
    getListData();
});
//搜索框清除
popup.find(".weui-icon-clear").click(function () {
    //搜索内容清空
    search = '';
    getListData();
});
//搜索框清除
popup.find(".weui-search-bar__cancel-btn").click(function () {
    //搜索内容清空
    search = '';
    getListData();
});
//取消选择
popup.find(".cancel-btn").on("click", closePopup);

/**
 * 退出用户选择器
 */
function closePopup() {
    search = '';
    popup.find('.weui-search-bar').removeClass('weui-search-bar_focusing');
    popup.find('.weui-search-bar__input').val('');
    $.closePopup();
}
/**
 * 从数据中获取指定ID的子数据
 * @param data
 * @param pid
 * @returns {Array}
 */
function getChildren(data, pid) {
    var arr = [];
    $.each(data, function (id, one) {
        if (one.pid == pid) {
            arr.push(one);
        }
    });

    return arr;
}
/**
 * 根据父节点类型和父节点ID获取包含的用户
 * @param parent_type
 * @param pid
 * @param incluseChild
 * @returns {Array}
 */
function getUsers(parent_type, pid, incluseChild) {
    var user_list = [];
    if (parent_type == 'department') {
        var pidList = [pid.toString()];
        var parent;
        if (incluseChild) {
            parent = groupIds[pid];
            if (parent != undefined) {
                var path = parent['path'] + pid + '-';
                $.each(groups, function (index, item) {
                    if (item.path.indexOf(path) != -1) {
                        pidList.push(item.id);
                    }
                });
            }
            // console.log(pid + '的子部门ID：' + pidList);
        }
    }
    $.each(users, function (index, item) {
        if (parent_type == 'department' && $.inArray(item.group_id, pidList) != -1) {//父节点为部门
            if (range_type == 1 ||
                (
                    (range.position.length == 0 || $.inArray(item.position_id, range.position) != -1) &&
                    (range.user.length == 0 || $.inArray(item.user_id, range.user) != -1)
                )
            ) {
                user_list.push(item)
            }
        } else if (parent_type == 'position' && item.position_id == pid) {//父节点为岗位
            if (range_type == 1 ||
                (
                    (range.department.length == 0 || $.inArray(item.group_id, range.department) != -1) &&
                    (range.user.length == 0 || $.inArray(item.user_id, range.user) != -1)
                )
            ) {
                user_list.push(item)
            }
        } else if (parent_type == 'user' && $.inArray(item.user_id, range.user) != -1) {//类型为用户
            if (range_type == 1 ||
                (
                    (range.department.length == 0 || $.inArray(item.group_id, range.department) != -1) &&
                    (range.position.length == 0 || $.inArray(item.position_id, range.position) != -1)
                )
            ) {
                user_list.push(item)
            }
        }
    });

    return user_list;
}
//点击头像删除
function delItem(obj) {
    var input = $(obj).parent().find('.user-group-select');
    var value = input.val();
    var id = $(obj).data('id');
    value = value.split(',');
    value.splice($.inArray(id, value), 1);
    input.val(value.join(','));
    $(obj).remove();
}
/**
 * 获取列表数据
 */
function getListData() {
    popup.find('input[name="check-all"]').each(function(index, item) {
        item.checked = false;
    });

    //最大选择数量没有限制则显示全选框，否则隐藏
    select_max == 0 ? popup.find('.yd-user-list .check-all').show() : popup.find('.yd-user-list .check-all').hide();
    popup.find('.yd-user-list .item').remove();//删除当前页所有数据选项

    //搜索内容
    if (search.length > 0) {
        popup.find('.weui-tab__bd').attr('style', 'padding-top: 0;');
        popup.find('.weui-navbar').hide();//隐藏导航栏
        popup.find('.breadcrumb').hide();//隐藏面包屑
        $.each(select_type, function (index, item) {
            if (item == 'department') {
                //载入部门数据
                $.each(groups, function (index, one) {
                    var id = 'G' + one.id;//给部门ID加上前缀
                    //部门选择范围为空,或者部门ID在选择范围内
                    if ((range.department.length == 0 || $.inArray(one.id, range.department) != -1) && one.name.indexOf(search) != -1) {
                        //允许选择该部门
                        if ($.inArray(type, select_type) != -1) {
                            popup.find('.yd-user-list').append(
                                '<div class="weui-cell item">' +
                                '<label class="weui-check__label">' +
                                '<div class="weui-cell__hd">' +
                                '<input type="checkbox" class="weui-check" name="selectItem[]" value="' + id + '" ' +
                                ($.inArray(id, checked) != -1 ? 'checked' : '') +'>' +
                                '<i class="weui-icon-checked"></i>' +
                                '</div>' +
                                '<div class="weui-cell__bd"><p>' + one.name + '</p></div>' +
                                '</label>' +
                                '</div>'
                            );
                        }
                    }
                });
            } else if (item == 'position') {
                //载入岗位数据
                $.each(positions, function (index, one) {
                    var id = 'P' + one.position_id;//给岗位ID加上前缀
                    //岗位选择范围为空,或者岗位ID在选择范围内
                    if ((range.position.length == 0 || $.inArray(one.position_id, range.position) != -1) && one.pos_name.indexOf(search) != -1) {
                        //允许选择该岗位
                        if ($.inArray(type, select_type) != -1) {
                            popup.find('.yd-user-list').append(
                                '<div class="weui-cell item">' +
                                '<label class="weui-check__label">' +
                                '<div class="weui-cell__hd">' +
                                '<input type="checkbox" class="weui-check" name="selectItem[]" value="' + id + '" ' +
                                ($.inArray(id, checked) != -1 ? 'checked' : '') +'>' +
                                '<i class="weui-icon-checked"></i>' +
                                '</div>' +
                                '<div class="weui-cell__bd"><p>' + one.pos_name + '</p></div>' +
                                '</label>' +
                                '</div>'
                            );
                        }
                    }
                });
            } else if (item == 'user') {
                //载入用户数据
                $.each(users, function (index, one) {
                    var id = 'U' + one.user_id;//给用户ID加上前缀
                    //用户选择范围为空,或者用户ID在选择范围内
                    if ((range.user.length == 0 || $.inArray(one.user_id, range.user) != -1) && one.realname.indexOf(search) != -1) {
                        popup.find('.yd-user-list').append(
                            '<div class="weui-cell item">' +
                            '<label class="weui-check__label">' +
                            '<div class="weui-cell__hd">' +
                            '<input type="checkbox" class="weui-check" name="selectItem[]" value="' + id + '" ' +
                            ($.inArray(id, checked) != -1 ? 'checked' : '') +'>' +
                            '<i class="weui-icon-checked"></i>' +
                            '</div>' +
                            '<div class="weui-cell__hd">' +
                            '<img class="weui-media-box__thumb" src="' + one.avatar + '">' +
                            '</div>' +
                            '<div class="weui-cell__bd"><p>' + one.realname + '</p></div>' +
                            '</label>' +
                            '</div>'
                        );
                    }
                });
            }
        });

    } else {
        if (show_page.length > 1) {
            popup.find('.weui-navbar').show();//显示导航栏
            popup.find('.weui-tab__bd').attr('style', 'padding-top: 44px;');
        }

        popup.find('.breadcrumb').show();//显示面包屑

        //导航
        var breadcrumb = popup.find('.yd-user-list .breadcrumb .weui-cell__bd');
        if (type == 'department') {//类型为部门
            var data = [];
            if (path.length == 0) {//第一级部门
                if (range.department.length == 0) {//如果部门范围为空,取pid为0的部门
                    data = getChildren(groups, 0);
                } else {//否则第一级部门为选择范围内的部门
                    $.each(range.department, function (index, id) {
                        data.push(groups[id]);
                    })
                }
                breadcrumb.html('<a class="cite">最上级</a>');
            } else {
                var arr = [];
                breadcrumb.html('<a class="get-parent" data-type="department" data-pid="0" data-path="">最上级');
                $.each(path, function (index, pid) {
                    arr.push(pid);
                    breadcrumb.append('<a class="get-parent" data-type="department" data-pid="' + pid + '" data-path="' + arr.join('-') + '"> > ' + groupIds[pid]['name'] + '</a>')
                });
                data = getChildren(groups, parent_id);
            }

            //载入部门数据
            $.each(data, function (index, one) {
                var id = 'G' + one.id;//给ID加上前缀
                var count = 0;//部门下用户数
                if ($.inArray('user', select_type) != -1) {//如果允许选择用户
                    //获取该部门下所有用户
                    var isChecked = true;
                    l = getUsers('department', one.id, true);
                    if (l.length == 0) {
                        isChecked = false;
                    }
                    $.each(l, function (index, item) {
                        if ($.inArray("U" + item.user_id, checked) == -1) {
                            isChecked = false;
                            return false;
                        }
                    });
                    count = l.length;
                }
                //获取该部门下的子部门
                var children = getChildren(groups, one.id);
                //如果部门下有用户或者子部门,显示下级按钮
                var haschild = count > 0 || children.length > 0;
                if ((range.department.length == 0 || $.inArray(one.id, range.department) != -1)) {
                    var inputHtml = '';
                    if ($.inArray(type, select_type) != -1) {//允许选择该部门
                        inputHtml = '<input type="checkbox" class="weui-check" name="selectItem[]" value="' + id + '" ' +
                            ($.inArray(id, checked) != -1 ? 'checked' : '') +'>'
                    } else {//不允许选择部门
                        if ($.inArray('user', select_type) != -1) {//允许选择用户
                            popup.find('.yd-user-list').find('.check-all').hide();//隐藏全选控件
                            inputHtml = '<input type="checkbox" class="weui-check" name="checkChild[]" data-type="department" ' +
                                'value="' + id + '" ' + (isChecked ? 'checked' : '') +  '>'
                        }
                    }
                    popup.find('.yd-user-list').append(
                        '<div class="weui-cell item">' +
                        '<label class="weui-check__label">' +
                        '<div class="weui-cell__hd">' + inputHtml +
                        '<i class="weui-icon-checked"></i>' +
                        '</div>' +
                        '<div class="weui-cell__bd"><p>' + one.name +
                        (count == 0 ? '' : '<span class="count">(' + count + '人)</span>') + '</p></div>' +
                        '</label>' +
                        (count == 0 ? '' : '<div class="get-child ' + (isChecked ? 'disabled' : '') + '" data-type="' + type + '" data-pid="' + one.id + '">' +
                            '<i class="fa fa-level-down"></i> <span>进入</span>' +
                            '</div>') +
                        '</div>'
                    );
                }
            });
            //如果允许选择用户
            if ($.inArray('user', select_type) != -1) {
                //获取上级部门下的用户
                var list = getUsers('department', parent_id, false);
                //载入上级部门下用户数据
                $.each(list, function (index, one) {
                    id = 'U' + one.user_id;
                    popup.find('.yd-user-list').append(
                        '<div class="weui-cell item">' +
                        '<label class="weui-check__label">' +
                        '<div class="weui-cell__hd">' +
                        '<input type="checkbox" class="weui-check" name="selectItem[]" value="' + id + '" ' +
                        ($.inArray(id, checked) != -1 ? 'checked' : '') +'>' +
                        '<i class="weui-icon-checked"></i>' +
                        '</div>' +
                        '<div class="weui-cell__hd">' +
                        '<img class="weui-media-box__thumb" src="' + one.avatar + '">' +
                        '</div>' +
                        '<div class="weui-cell__bd"><p>' + one.realname + '</p></div>' +
                        '</label>' +
                        '</div>'
                    );
                })
            }
        } else if (type == 'position') {//类型为岗位
            if (parent_id == 0) {//岗位
                breadcrumb.html('<a class="cite">最上级</a>');
                //载入部门数据
                $.each(positions, function (index, one) {
                    var id = 'P' + one.position_id;//给岗位ID加上前缀
                    var count = 0;//部门下用户数
                    if ($.inArray('user', select_type) != -1) {//如果允许选择用户
                        //获取该部门下所有用户
                        var isChecked = true;
                        l = getUsers('position', one.position_id, false);
                        if (l.length == 0) {
                            isChecked = false;
                        }
                        $.each(l, function (index, item) {
                            if ($.inArray("U" + item.user_id, checked) == -1) {
                                isChecked = false;
                                return false;
                            }
                        });
                        count = l.length;
                    }
                    //岗位选择范围为空,或者岗位ID在选择范围内
                    if ((range.position.length == 0 || $.inArray(one.position_id, range.position) != -1)) {
                        var inputHtml = '';
                        if ($.inArray(type, select_type) != -1) {//允许选择该岗位
                            inputHtml = '<input type="checkbox" class="weui-check" name="selectItem[]" value="' + id + '" ' +
                                ($.inArray(id, checked) != -1 ? 'checked' : '') +'>'
                        } else {//不允许选择岗位
                            popup.find('.yd-user-list').find('.check-all').hide();//隐藏全选控件
                            inputHtml = '<input type="checkbox" class="weui-check" name="checkChild[]" data-type="position" value="'
                                + id + '" ' + (isChecked ? 'checked' : '') + '>'
                        }
                        popup.find('.yd-user-list').append(
                            '<div class="weui-cell item">' +
                            '<label class="weui-check__label">' +
                            '<div class="weui-cell__hd">' + inputHtml +
                            '<i class="weui-icon-checked"></i>' +
                            '</div>' +
                            '<div class="weui-cell__bd"><p>' + one.pos_name +
                            (count == 0 ? '' : '<span class="count">(' + count + '人)</span>') + '</p></div>' +
                            '</label>' +
                            (count == 0 ? '' : '<div class="get-child ' + (isChecked ? 'disabled' : '') + '" data-type="' + type + '" data-pid="' + one.position_id + '">' +
                                '<i class="fa fa-level-down"></i> <span>进入</span>' +
                                '</div>') +
                            '</div>'
                        );
                    }
                });
            } else {
                breadcrumb.html('<a class="get-parent" data-type="position" data-pid="0" data-path="">最上级');
                //如果允许选择部门
                if ($.inArray('user', select_type) != -1) {
                    //获取上级部门下的用户
                    var list = getUsers('position', parent_id, false);
                    //载入上级部门下用户数据
                    $.each(list, function (index, one) {
                        id = 'U' + one.user_id;
                        popup.find('.yd-user-list').append(
                            '<div class="weui-cell item">' +
                            '<label class="weui-check__label">' +
                            '<div class="weui-cell__hd">' +
                            '<input type="checkbox" class="weui-check" name="selectItem[]" value="' + id + '" ' +
                            ($.inArray(id, checked) != -1 ? 'checked' : '') +'>' +
                            '<i class="weui-icon-checked"></i>' +
                            '</div>' +
                            '<div class="weui-cell__hd">' +
                            '<img class="weui-media-box__thumb" src="' + one.avatar + '">' +
                            '</div>' +
                            '<div class="weui-cell__bd"><p>' + one.realname + '</p></div>' +
                            '</label>' +
                            '</div>'
                        );
                    })
                }
            }
        } else if (type == 'user') {
            popup.find('.yd-user-list .breadcrumb').hide();
            //如果允许选择用户并且用户选择范围不为空
            if ($.inArray('user', select_type) != -1) {
                var list = getUsers('user', 0, false);
                //载入用户数据
                $.each(list, function (index, one) {
                    id = 'U' + one.user_id;
                    popup.find('.yd-user-list').append(
                        '<div class="weui-cell item">' +
                        '<label class="weui-check__label">' +
                        '<div class="weui-cell__hd">' +
                        '<input type="checkbox" class="weui-check" name="selectItem[]" value="' + id + '" ' +
                        ($.inArray(id, checked) != -1 ? 'checked' : '') +'>' +
                        '<i class="weui-icon-checked"></i>' +
                        '</div>' +
                        '<div class="weui-cell__hd">' +
                        '<img class="weui-media-box__thumb" src="' + one.avatar + '">' +
                        '</div>' +
                        '<div class="weui-cell__bd"><p>' + one.realname + '</p></div>' +
                        '</label>' +
                        '</div>'
                    );
                })
            }
        }
    }

    //进入下一级
    !popup.find('.get-child').click(function () {
        if ($(this).hasClass('disabled')) {
            return;
        }
        parent_id = $(this).data('pid');
        path.push(parent_id);
        var type = $(this).data('type');
        $.showLoading();
        setTimeout(function() {getListData(type); $.hideLoading();}, 100)
    });

    //返回上一级
    popup.find('.get-parent').click(function () {
        path = $(this).data('path');
        parent_id = $(this).data('pid');
        path = path == '' ? [] : (isNaN(path) ? path.split('-') : [path]);
        var type = $(this).data('type');
        $.showLoading();
        setTimeout(function() {getListData(type); $.hideLoading();}, 100)
    });

    //点击checkbox事件(selectItem[])
    popup.find('input[name="selectItem[]"]').click(function () {
        //选中择把val值push到checked,取消择删除checked内该val值
        if (this.checked) {
            if (select_max > 0 && checked.length >= select_max) {
                $.toptip('最多只能选择' + select_max + '个', 'error');
                this.checked = false;
            } else {
                checked.push($(this).val());
            }
        } else {
            checked.splice(checked.indexOf($(this).val()), 1);
        }
    });

    //点击checkbox事件(checkChild[])
    popup.find('input[name="checkChild[]"]').click(function () {
        var type = $(this).data('type');
        var list = getUsers(type, $(this).val().slice(1), true);
        var ids = [];
        $.each(list, function (index, item) {
            ids.push('U' + item.user_id);
        });
        if (this.checked) {
            //超过了允许选择的最大数量
            $.each(checked, function (index, item) {
                if ($.inArray(item, ids) == -1) {
                    ids.push(item);
                }
            });
            if (select_max > 0 && ids.length > select_max) {
                $.toptip('最多只能选择' + select_max + '个', 'error');
                this.checked = false;
            } else {
                checked = ids;
                $($(this).parents('.item').find('.get-child')[0]).addClass('disabled');
            }
        } else {
            $.each(ids, function (index, item) {
                if ($.inArray(item, checked) != -1) {
                    checked.splice(checked.indexOf(item), 1);
                }
            });
            $($(this).parents('.item').find('.get-child')[0]).removeClass('disabled');
        }
    });

    //点击全选事件
    popup.find('input[name="check-all"]').click(function () {
        var isChecked = this.checked;//获取全选按钮选中状态
        popup.find('input[name="selectItem[]"]').each(function (index, item) {
            if (this.checked != isChecked) {//选项中和全选按钮状态不一样的触发点击事件
                $(this).click();
            }
        })
    });
}

$(function () {
    //点击添加成员
    $(".add-members").on('click', addMembers);
    //确定按钮
    popup.find(".confirm-btn").on("click", function () {
        that.parent().find('li.selected-item').remove();
        var item;
        $.each(checked, function (index, id) {
            if (id.slice(0, 1) == 'G') {//部门范围
                item = groupIds[id.slice(1)];
                if (item != undefined) {
                    avatar = '/static/images/icon/icon-department.png';
                    name = item.name;
                }
            } else if (id.slice(0, 1) == 'P') {//岗位范围
                item = positionIds[id.slice(1)];
                if (item != undefined) {
                    avatar = '/static/images/icon/icon-position.png';
                    name = item.pos_name;
                }
            } else if (id.slice(0, 1) == 'U') {//用户范围
                item = userIds[id.slice(1)];
                if (item != undefined) {
                    avatar = item.avatar;
                    name = item.realname;
                }
            }

            if (item != undefined) {
                that.before('<li class="selected-item" data-id="' + id + '" onclick="delItem(this)"><img src="' + avatar + '" alt=""><p>' + name + '</p></li>');
            }
        });
        var val = checked.join(',');
        that.parent().find('.user-group-select').val(val);
        closePopup();
    });
});
