<?php

use yii\helpers\Url;

$this->title = '部门管理';

// 更新部门的权限
$canUpdate = Yii::$app->user->can('user/group/update');
$canEdit = Yii::$app->user->can('user/group/edit');
?>

<div class="layui-row">
    <div style="float: left; width: 270px; margin-right: -270px; position: relative;">
	    <script>
            var onSelect = function (event, treeId, treeNode) {
                <?php if ($canEdit): ?>
	                $("#groupForm").show();
	                $("#iframe").attr('src', '<?= Url::toRoute(['edit', 'id' => ''])?>' + treeNode.id);
                <?php endif; ?>
            }
	    </script>
        <?= \system\widgets\ZTreeWidget::widget([
            'divId' => 'group',
            'getUrl' => ['/user/group/ajax'],
            'updateUrl' => ['/user/group/update'],
            'divOption' => 'style="padding: 0 10px;"',
            'onSelect' => 'onSelect',
            'permission' => [
                'add' => $canUpdate,
                'edit' => $canUpdate,
                'del' => $canUpdate,
            ],
            'isExpand' => false,
        ])
        ?>
    </div>
    <div id="groupForm" style="display: none; float: right; width: 100%">
        <div style="margin-left: 270px;">
            <iframe id='iframe' scrolling='no' width=99% frameborder="0" src=""></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
    //自适应ifram高度
    $("#iframe").load(function () {
        var mainheight = $(this).contents().find("body").height() + 30;
        $(this).height(mainheight);
    });
    // 树自适应高度
    $("#treeStruct").css({maxHeight: (document.documentElement.clientHeight - 20) + 'px'});
</script>