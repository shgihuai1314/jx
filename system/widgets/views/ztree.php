<?php
/**
 * Created by PhpStorm.
 * User: luobo
 * Date: 17/5/5
 * Time: 上午11:50
 */

/* @var $this yii\web\View */
/* @var $id string */
/* @var $note_id string */
/* @var $inputName string */
/* @var $isMulti bool */
/* @var $getUrl array */
/* @var $updateUrl array */
/* @var $divOption string */
/* @var $onAsyncSuccess string */
/* @var $dataFilter string */
/* @var $onSelect string */
/* @var $isExpand string */
/* @var $permission array */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use system\assets\ZTreeAsset;

ZTreeAsset::register($this);

$canAdd = ArrayHelper::getValue($permission, 'add', false);
$canEdit = ArrayHelper::getValue($permission, 'edit', false);
$canDel = ArrayHelper::getValue($permission, 'del', false);

$canAdd = $canAdd === false ? 'false' : $canAdd;
$canEdit = $canEdit === false ? 'false' : $canEdit;
$canDel = $canDel === false ? 'false' : $canDel;

if ($onSelect) {//如果自定义了回调函数
    $zTreeOnSelect = "var zTreeOnSelect = ".$onSelect;
} else {
    if (!$isMulti) {//单选回调
        $zTreeOnSelect = "var zTreeOnSelect = function (event, treeId, treeNode) {
            if (treeNode != null) {
                $('#zTreeSelect".$id."') . text(treeNode.id + ':' + treeNode.name);
                $('#zTreeId".$id."').val(treeNode.id);
            }
        }";
    } else {//多选回调
        $zTreeOnSelect = "
        //已经选中的tid
        var selectNodes = [];
        var zTreeOnSelect = function (event, treeId, treeNode) {
            //如果点击的treeId已经存在，那么取消选中
            var idIndex = $.inArray(treeNode.id, selectNodes);
            if (idIndex > -1) {
                selectNodes.splice(idIndex, 1);
            }
            //如果不存在，那么选中
            else {
                selectNodes.push(treeNode.id);
            }
            //保存已选中的名称
            var selectNameOfNode = [];
            if (selectNodes.length > 0) {//进行选择操作
                $.each(selectNodes, function (n, id) {
                    var node = zTree<?= $id?>.getNodeByParam('id', id, null);
                    //将数据保存在名称数组中
                    selectNameOfNode.push(node.id + ':' + node.name);
                    if (n == 0) {
                        zTree<?= $id?>.selectNode(node);
                    } else {
                        zTree<?= $id?>.selectNode(node, true);
                    }
                });
            } else {//取消选择
                selectNameOfNode = [];
                zTree<?= $id?>.cancelSelectedNode(treeNode);
            }
            //数据更新到input和页面中
            $('#zTreeId".$id."').val(selectNodes);
            $('#zTreeSelect".$id."').html(selectNameOfNode.join('，'));
        };";
    }
}

$this->registerJs("
	createTree_$id('$id');
", \yii\web\View::POS_END);
?>
<?= Html::hiddenInput($inputName, $note_id, [
    'id' => 'zTreeId'.$id,
]) ?>

<div <?= $divOption ?>>
    <div style="display: <?= !$onSelect ? 'block' : 'none' ?>">当前选择的是：<span id="zTreeSelect<?= $id ?>" style="color:#1988fa;"></span></div>
    <ul id="<?= $id ?>" class="ztree"></ul>
</div>

<script>
    //声明ztree当前选中的id
    var currentZTreeId = '<?=$note_id?>';
    //获取数据的url
    var ztree_get_url_<?=$id?> = '<?=Url::to($getUrl)?>';
    //更新节点数据的url
    var ztree_update_url = '<?= Url::to($updateUrl); ?>';
    //是否展开节点
    var isExpand = <?= $isExpand ? 'true' : 'false' ?>;
    //点击回调
    <?=$zTreeOnSelect;?>

    var dataFilter = <?= is_null($dataFilter) ? 'null' : $dataFilter;?>

    <?php if ($onAsyncSuccess) : ?>
    <?= $onAsyncSuccess; ?>
    <?php else : ?>
    //异步加载完毕后的回调
    var onAsyncSuccess = function (event, treeId, msg) {
        //展开全部节点
        if (isExpand) {
            zTree<?= $id?>.expandAll(true);
        } else {
            //调用默认展开第一个结点
            var selectedNode = zTree<?= $id?>.getSelectedNodes();
            var nodes = zTree<?= $id?>.getNodes();
            zTree<?= $id?>.expandNode(nodes[0], true);
        }

        // 当前选中的节点，多个节点
        var zTreeIds = currentZTreeId.split(",");
        //console.log(zTreeIds);
        for (var i = 0; i < zTreeIds.length; i++) {
            if (zTreeIds[i] != '') {
                var currentNode = zTree<?= $id?>.getNodeByParam("id", zTreeIds[i], null);
                //console.log(currentNode);
                if (currentNode) {
                    zTree<?= $id?>.selectNode(currentNode);
                    zTreeOnSelect('', '', currentNode);
                }
            }
        }
        $('.ztree li a.curSelectedNode span.button').remove();
    }
    <?php endif; ?>


    //配置参数
    var setting_<?=$id?> = {
        view: {
            addHoverDom: addHoverDom,
            removeHoverDom: removeHoverDom,
            showLine: true
        },
        // 数据使用JSON数据
        data: {
            simpleData: {
                enable: true,
                idKey: 'id',
                pIdKey: 'pid'
            }
        },
        // 强行异步加载父节点的子节点
        async: {
            enable: true,
            type: 'get',
            dataType: "text",
            autoParam: ['id', 'name', 'pid'],
            url: ztree_get_url_<?=$id?>,
            dataFilter: dataFilter
        },
        edit: {
            enable: true,
            editNameSelectAll: true,
            showRemoveBtn: showRemoveBtn,
            showRenameBtn: showRenameBtn,
            drag: {
                prev: <?= $canEdit ?>,
                inner: <?= $canEdit ?>,
                next: <?= $canEdit ?>,
            },
        },
        callback: {
            onAsyncSuccess: onAsyncSuccess,
            //beforeEditName: beforeEditName,
            beforeRemove: beforeRemove,
            beforeDrop: beforeDrop,
            onRemove: onRemove,
            onRename: onRename,
            onDrop: onDrop,
            onCheck: false,
            onClick: zTreeOnSelect,
            beforeClick: typeof beforeClick != 'undefined' ? beforeClick : null,
        }
    };

    var zNodes = null;
    //获取ztree对象
    var zTree<?= $id?>;
    // 创建tree
    var className = "dark";
    function createTree_<?= $id?>(domId) {
        $.fn.zTree.init($("#" + domId), setting_<?=$id?>, zNodes);
        zTree<?= $id?> = $.fn.zTree.getZTreeObj(domId);
    }
    //拖拽
    function beforeDrop(treeId, treeNodes, targetNode, moveType) {
        return !(targetNode == null || (moveType != "inner" && !targetNode.parentTId));
    }
    function onDrop(event, treeId, treeNodes, targetNode, moveType) {
        //console.log(moveType);
        var data;
        if (moveType == 'prev' || moveType == 'next') {
            data = {
                type: 'sort', // 排序
                moveType: moveType, // 移动的方向
                id: treeNodes[0].id, //操作的id
                target_id: targetNode.id //拖拽到目的id
            };
        } else if (moveType == 'inner') {
            data = {
                type: 'drag',
                id: treeNodes[0].id, //操作的id
                target_id: targetNode.id //拖拽到目的id
            };
        }
        saveOne(data);
    }

    function onRename(event, treeId, treeNode, isCancel) {
        //如果编辑已经存在的数据时按了取消键
        if ('path' in treeNode && isCancel) {
            return;
        }
        //类型：编辑或者添加
        var type = 'type' in treeNode && treeNode.type == 'add' ? 'add' : 'edit';
        var one = {
            type: type,
            id: treeNode.id,
            name: treeNode.name,
            pid: treeNode.pid
        };
        saveOne(one);
    }
    // 删除
    function beforeRemove(treeId, treeNode) {
        className = (className === "dark" ? "" : "dark");
        if (treeNode.isParent) {
            alert('其下还有子节点，不能删除');
            return false;
        }
        return confirm("确认要删除节点“" + treeNode.name + "”吗？");
    }
    function onRemove(event, treeId, treeNode) {
        var one = {
            type: 'delete',
            id: treeNode.id
        };
        saveOne(one);
    }
    //保存一条数据
    function saveOne(data) {
        //提交服务器
        $.ajax({
            type: "POST",
            url: ztree_update_url,
            data: JSON.stringify(data),
            success: function (message) {
                zTree<?= $id?>.reAsyncChildNodes(null, "refresh");
            },
            error: function (data) {
                alert('操作失败，请稍后重试');
            }
        });
    }

    //是否显示编辑按钮 canUpdate 是权限
    function showRenameBtn(treeId, treeNode) {
        return <?= $canEdit ?> && treeNode.id != 0;
    };
    //是否显示移除按钮
    function showRemoveBtn(treeId, treeNode) {
        return <?= $canDel ?> && treeNode.id != 0;
    }
    //当鼠标移出节点时，隐藏用户自定义控件
    function removeHoverDom(treeId, treeNode) {
        $("#addBtn_" + treeNode.tId).unbind().remove();
    }

    var newCount = 1;
    //添加分组
    function addHoverDom(treeId, treeNode) {
        //console.log(treeId, treeNode);
        var sObj = $("#" + treeNode.tId + "_span");
        if (treeNode.editNameFlag || $("#addBtn_" + treeNode.tId).length > 0) {
            return;
        }

        if (<?= $canAdd ?>) {
            var addStr = "<span class='button add' id='addBtn_" + treeNode.tId + "' title='添加分类' onfocus='this.blur();'></span>";
            sObj.after(addStr);
            var btn = $("#addBtn_" + treeNode.tId);
            if (btn) {
                btn.bind("click", function () {
                    var newNode = zTree<?= $id?>.addNodes(treeNode, {
                        id: (100 + newCount),
                        pId: treeNode.id,
                        name: "node" + (newCount++),
                        type: 'add'
                    });
                    //console.log(newNode);
                    zTree<?= $id?>.editName(newNode[0]);
                    return false;
                });
            }
        }

    }

</script>

