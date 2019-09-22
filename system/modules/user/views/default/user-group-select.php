<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/29
 * Time: 22:52
 */

/** @var \yii\web\View $this */
/** @var string $value */
/** @var object $options */

use yii\helpers\ArrayHelper;

\system\assets\ZTreeAsset::register($this);

$options = ArrayHelper::toArray($options);

$show_user = ArrayHelper::getValue($options, 'show_user', 1);
$show_page = ArrayHelper::getValue($options, 'show_page', 'department,position');
$select_max = ArrayHelper::getValue($options, 'select_max', 0);
$select_type = ArrayHelper::getValue($options, 'select_type', 'department,position,user');
$show_range = ArrayHelper::getValue($options, 'show_range', '');
$range_type = ArrayHelper::getValue($options, 'range_type', 0);

$tab = explode(',', $show_page);
?>
<script>
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
</script>
<style>
    .select-box { height: 100%; overflow: hidden }
    .select-box-mainer {width: 55%; height: 473px; float: left }
    .select-box .layui-tab { margin: 0; height: 100%; border-bottom: none }
    .select-box .layui-tab .layui-tab-title li { width: <?=100/count($tab)?>%; margin: 0; padding: 0 }
    .select-box .layui-tab .layui-tab-content .layui-form-item { position: relative; margin-bottom: 8px }
    .select-box .layui-tab .layui-tab-content .layui-form-item .search-members { padding-left: 30px; }
    .select-box .layui-tab .layui-tab-content .layui-form-item i { position: absolute; top: 10px; left: 10px; font-size: 16px; color: #757575; }
    .select-box .layui-tab .layui-tab-content .tree-div { max-height: 360px; overflow: auto }
    .select-box-res { width: 45%; height: 100%; float: left }
    .select-box-res .select-box-ctrl { padding: 10px 0 }
    .select-box-res .select-box-ctrl p { margin: 0 20px; color: #757575; }
    .select-box-res .select-box-ctrl p .clear { cursor: pointer }
    .select-box-res .select-box-acheive { margin-top: 5px; overflow: auto; height: 420px }
    .select-box-res .select-box-acheive .selected-item { position: relative; padding: 3px 25px;}
    .select-box-res .select-box-acheive .selected-item i { width: 24px; height: 24px; text-align: center; font-size: 18px; margin-right: 6px;color: #666;cursor: pointer }
    .select-box-res .select-box-acheive .selected-item i.fa-times-circle { font-size: 20px; cursor: pointer; color: #aa8833 }
</style>

<div class="select-box">
    <div class="select-box-mainer bgcolor-f2">
        <div class="layui-tab layui-tab-card" lay-filter="tab-type">
            <ul class="layui-tab-title">
                <?= in_array('department', $tab) ? '<li data-tab="department">按部门</li>' : '' ?>
                <?= in_array('position', $tab) ? '<li data-tab="position">按职位</li>' : '' ?>
                <?= in_array('user', $tab) ? '<li data-tab="user">按用户</li>' : '' ?>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-form-item">
                    <input type="text" id="search-members" class="layui-input search-members" placeholder="搜索部门或成员" value=""/><i class="fa fa-search"></i>
                </div>

                <div class="tree-div" style="padding: 0 10px;">
                    <ul class="ztree" id="treeSelect"></ul>
                </div>
                <script>
                    //删除已选择对象
                    function delItem(obj) {
                        var parent = $(obj).parent();
                        var id = parent.data('id');
                        var node = zTree.getNodeByParam('id', id, null);
                        if (node != null) {
                            zTreeOnSelect('', '', node);
                        } else {
                            var idIndex = $.inArray(id, selectNodes);
                            selectNodes.splice(idIndex, 1);//从已选中节点中去掉该ID
                            $('#selected-item-' + id).remove();//取消右侧列表
                            $('.select-box-ctrl .num').html(selectNodes.length);
                        }
                    }
                    //清除所有已选择对象
                    function clearAll() {
                        if (selectNodes.length > 0) {//进行选择操作
                            $('.select-box-ctrl .num').html(0)
                            $('.select-box-acheive').html('');

                            $.each(selectNodes, function (n, id) {
                                var node = zTree.getNodeByParam('id', id, null);
                                zTree.cancelSelectedNode(node);
                            });
                            selectNodes = [];
                        }
                    }
                    //根据父节点类型和父节点ID获取包含的用户
                    function getUsers(parent_type, pid) {
                        var user_list = [];
                        var range_type = <?= $range_type ?>;
                        $.each(users, function (index, item) {
                            if (parent_type == 'department' && item.group_id == pid) {//父节点为部门
                                if (range_type == 1 ||
                                    (
                                        (range.position.length == 0 || $.inArray(item.position_id, range.position) != -1) &&
                                        (range.user.length == 0 || $.inArray(item.user_id, range.user) != -1)
                                    )
                                ) {
                                    user_list.push(item)
                                }
                            } else if (parent_type == 'position' && item.position_id == pid) {//父节点为职位
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
                    //从数据中获取指定ID的子数据
                    function getRecurData(arr, pid) {
                        arr.push(pid);
                        $.each(groups, function (id, one) {
                            if (one.pid == pid) {
                                arr = getRecurData(arr, one.id);
                            }
                        });

                        return arr;
                    }
                    var currentTab;
                    var show_range = '<?= $show_range ?>'.split(',');
                    var select_type = '<?= $select_type ?>'.split(',');
                    //选择范围分类
                    var range = {
                        department: [],
                        position: [],
                        user: []
                    };
                    //显示范围分类
                    $.each(show_range, function (index, item) {
                        item = $.trim(item);
                        if (item.slice(0, 1) == 'G') {//部门范围
                            range.department = getRecurData(range.department, item.slice(1));
                        } else if (item.slice(0, 1) == 'P') {//职位范围
                            range.position.push(item.slice(1))
                        } else if (item.slice(0, 1) == 'U') {//用户范围
                            range.user.push(item.slice(1))
                        }
                    });
                    //已选中的节点id
                    var selectItems = [];
                    var selectNodes = '<?=$value?>';
                    selectNodes = selectNodes == '' ? [] : selectNodes.split(',');
                    //节点点击事件
                    var zTreeOnSelect = function (event, treeId, treeNode) {
                        if (treeNode != null) {
                            var idIndex = $.inArray(treeNode.id, selectNodes);
                            if (idIndex > -1) {//如果点击的treeId已经存在，那么取消选中
                                selectNodes.splice(idIndex, 1);//从已选中节点中去掉该ID
                                zTree.cancelSelectedNode(treeNode);
                                $('#selected-item-' + treeNode.id).remove();//取消右侧列表
                            } else {//如果不存在，那么选中
                                // 判断是否超过最大允许选择数量
                                if (<?=$select_max?> == 0 || selectNodes.length < <?=$select_max?>) {
                                    selectNodes.push(treeNode.id);
                                    $('.select-box-acheive').append('<li class="selected-item" id="selected-item-' + treeNode.id + '" data-id="' + treeNode.id + '" data-name="' + treeNode.name + '"><i class="' + treeNode.iconSkin + '"></i>' + treeNode.name + '<i class="fa fa-close fr" onclick="delItem(this)"></i></li>')
                                } else {
                                    layer.msg('最多只能选择<?=$select_max?>个对象');
                                }
                            }

                            selectItems = selectNodes;
                            $.each(selectNodes, function (i, item) {
                                var node = zTree.getNodeByParam("id", item, null);
                                if (node != null && select_type.indexOf(node.type) != -1) {
                                    zTree.selectNode(node, i != 0, true);
                                }
                            });

                            $('.select-box-ctrl .num').html(selectNodes.length);
                            $('#selected-items').val(selectNodes.join(','));
                        }
                    };
                    //节点点击前事件，过滤非用户节点点击处理
                    var beforeClick = function (treeId, treeNode) {
                        if (select_type.indexOf(treeNode.type) == -1) {
                            zTree.expandNode(treeNode, true, false, true, true);
                        }
                        return select_type.indexOf(treeNode.type) != -1;
                    };
                    //展开节点后操作
                    var zTreeOnExpand = function (event, treeId, treeNode) {
                        if (treeNode.isExpand == false) {
                            if (select_type.indexOf('user') != -1) {
                                var user_list = getUsers(currentTab, treeNode.id.slice(1));
                                var arr = [];
                                $.each(user_list, function (i, u) {
                                    arr.push({
                                        type: 'user',
                                        id: 'U' + u.user_id,
                                        name: u.realname,
                                        iconSkin: 'fa fa-user'
                                    });
                                });
                                zTree.addNodes(treeNode, arr);
                            }
                        }
                        treeNode.isExpand = true;
                    };
                    //配置参数
                    var setting = {
                        view: {
                            showLine: true
                        },
                        // 数据使用JSON数据
                        data: {
                            simpleData: {
                                enable: true,
                                idKey: 'id',
                                pidKey: 'pid'
                            }
                        },
                        callback: {
                            onClick: zTreeOnSelect,
                            beforeClick: beforeClick,
                            onExpand: zTreeOnExpand
                        }
                    };

                    var zNodes = [];
                    //获取ztree对象
                    var zTree = null;
                    function createTree(domId) {
                        $.fn.zTree.init($("#" + domId), setting, zNodes);
                        zTree = $.fn.zTree.getZTreeObj(domId);

                        //调用默认展开第一个结点
                        var nodes = zTree.getNodes();
                        zTreeOnExpand('', '', nodes[0]);

                        $.each(selectNodes, function (i, item) {
                            var user = userIds[item.slice(1)];
                            if (select_type.indexOf('user') != -1 && item.slice(0,1) == 'U' && user != undefined) {
                                var node = null;
                                if (currentTab == 'department') {
                                    node = zTree.getNodeByParam("id", 'G' + user['group_id'], null);
                                } else if (currentTab == 'position') {
                                    node = zTree.getNodeByParam("id", 'P' + user['position_id'], null);
                                }
                                if (node != null) {
                                    zTreeOnExpand('', '', node);
                                }
                            }
                            var currentNode = zTree.getNodeByParam("id", item, null);
                            if (currentNode != null && select_type.indexOf(currentNode.type) != -1) {
                                zTree.selectNode(currentNode, true, false);
                                if (selectItems.indexOf(item) == -1) {
                                    $('.select-box-acheive').append('<li class="selected-item" id="selected-item-' + currentNode.id + '" data-id="' + currentNode.id + '" data-name="' + currentNode.name + '"><i class="' + currentNode.iconSkin + '"></i>' + currentNode.name + '<i class="fa fa-close fr" onclick="delItem(this)"></i></li>');
                                }
                            }
                        });

                        selectItems = selectNodes;
                        $('.select-box-ctrl .num').html(selectNodes.length);
                        $('#selected-items').val(selectNodes.join(','));
                    }

                    $(document).ready(function () {
                        layui.use('element', function () {
                            var element = layui.element;

                            //一些事件监听
                            element.on('tab(tab-type)', function (data) {
                                currentTab = $(this).data('tab');
                                if (zTree != null) {
                                    zTree.destroy();
                                }
                                zNodes = getNodes(currentTab);
                                createTree('treeSelect');
                            });
                        });
                        var tabs = $('.layui-tab-title li');
                        currentTab = tabs.eq(0).data('tab');
                        tabs.eq(0).click();
                        //搜索成员
                        $('#search-members').keyup(function () {
                            var tree = $('#treeSelect');
                            var value = $(this).val();
                            tree.find("li").removeClass('show').removeClass('hide');
                            tree.find("ul").removeClass('show').removeClass('hide');

                            if (value != '') {
                                if (select_type.indexOf('user') != -1) {
                                    var n = 0;
                                    $.each(users, function (index, one) {
                                        if (n >= 20) {
                                            return false;
                                        }
                                        if (one.realname.indexOf(value) != -1) {
                                            var node = null;
                                            if (currentTab == 'department') {
                                                node = zTree.getNodeByParam("id", 'G' + one['group_id'], null);
                                            } else if (currentTab == 'position') {
                                                node = zTree.getNodeByParam("id", 'P' + one['position_id'], null);
                                            }
                                            if (node != null ) {
                                                zTreeOnExpand('', '', node);
                                            }
                                            n++;
                                        }
                                    });
                                }
                                tree.find("li").addClass('hide').filter(":contains('" + ( value ) + "')").addClass('show');
                                tree.find("ul").addClass('hide').filter(":contains('" + ( value ) + "')").addClass('show');
                                tree.find("li").find("a:contains('" + ( value ) + "')").parent().find('.hide').removeClass('hide');
                            }
                        });

                        //根据tab获取树节点数据
                        function getNodes(tab) {
                            var nodes = [];
                            if (tab == 'department') {
                                $.each(groups, function (index, one) {
                                    if ((range.department.length == 0 || $.inArray(one.id, range.department) != -1)) {
                                        nodes.push({
                                            type: 'department',
                                            id: 'G' + one.id,
                                            pId: 'G' + one.pid,
                                            name: one.name,
                                            iconSkin: one.pid == 0 ? 'fa fa-bank' : 'fa fa-group',
                                            isParent: true,
                                            isExpand: false
                                        });
                                    }
                                });
                            } else if (tab == 'position') {
                                $.each(positions, function (index, one) {
                                    if ((range.position.length == 0 || $.inArray(one.id, range.position) != -1)) {
                                        nodes.push({
                                            type: 'position',
                                            id: 'P' + one.id,
                                            pId: 0,
                                            name: one.name,
                                            iconSkin: 'fa fa-user-plus',
                                            isParent: true,
                                            isExpand: false
                                        });
                                    }
                                })
                            } else {
                                $.each(users, function (index, one) {
                                    if (($.inArray(one.user_id, range.user) != -1)) {
                                        nodes.push({
                                            type: 'user',
                                            id: 'U' + one.user_id,
                                            pId: 0,
                                            name: one.realname,
                                            iconSkin: 'fa fa-user',
                                        })
                                    }
                                })
                            }
                            return nodes;
                        }
                    });
                </script>
            </div>
        </div>
    </div>
    <div class="select-box-res">
        <div class="select-box-ctrl">
            <input type="hidden" id="selected-items" value=""/>
            <p>已选择：<span class="num">0</span> 个<span class="clear right" onclick="clearAll()">清空</span></p>
        </div>
        <div class="select-box-acheive"></div>
    </div>
</div>