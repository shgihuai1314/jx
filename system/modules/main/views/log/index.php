<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/7
 * Time: 上午10:13
 */
$log_types = Yii::$app->systemConfig->getValue('LOG_TYPE_LIST', []);

// 搜索关键字
$keyword = Yii::$app->request->get('keyword');
// 当前组
$type = Yii::$app->request->get('type');

?>
<form class="mb20" action="" method="get">
    <div class="layui-input-inline" style="width: 300px;">
        <input type="text" name="keyword" required lay-verify="required" placeholder="请输入" autocomplete="off" class="layui-input" value="<?= $keyword?>">
    </div>
    <button class="layui-btn ">搜索</button>

    <span class="layui-breadcrumb" lay-separator="|" style="margin-left: 30px;">
        <a href="<?= \yii\helpers\Url::toRoute([''])?>" <?php if ('' == $type) echo 'class="layui-this"';?> >全部</a>
        <?php foreach ($log_types as $key => $value): ?>
            <a href="<?= \yii\helpers\Url::toRoute(['', 'keyword' => $keyword, 'type'=>$key])?>" <?php if ($key == $type) echo 'class="layui-this"'; ?> ><?= $value?></a>
        <?php endforeach;?>
    </span>
</form>

<div class="ibox float-e-margins">
    <div class="ibox-title">
        <h5>操作日志</h5>
    </div>
    <div class="ibox-content" style="padding-top: 0">
        <ul class="feed-activity-list">
            <?php foreach ($logs as $log):?>
                <li class="feed-element clearfix">
                    <div class="avatar">
                        <div class="avatar-box-left">
                            <?php
                            if ($log['user']) {
                                echo "<img class='avatar-mini img-circle' src='{$log['user']['avatar']}' /> ";
                            } else {
                                echo "<img class='avatar-mini img-circle' src='/static/images/avatar/default/system.png' /> ";
                            }
                            ?>
                        </div>
                        <div class='avatar-box-right'>
                            <div class="comment-box">
                                <p class="name"><?= $log['user']['realname'] ?: '系统'?></p>
                                <span><?= date('Y-m-d H:i:s', $log['add_time'])?></span>
                                <span><?= $log['ip']?></span>
                                <div class="comment word-break">
                                    {<?php
                                    if (isset($log_types[$log['type']])) {
                                        echo $log_types[$log['type']];
                                    } else {
                                        echo \yii\helpers\Html::encode($log['type']);
                                    }
                                    ?>}

                                    <?= \yii\helpers\Html::encode($log['content'])?>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endforeach;?>
        </ul>
    </div>
</div>

<?= \system\widgets\MyPaginationWidget::widget([
    'pagination' => $pagination,
]) ?>

<style>
	.layui-layer-tips {width: inherit!important;}
	.content-tip th {font-weight: bold;}
	.content-tip th, .content-tip td {padding: 5px 10px; border: 1px solid #e2e2e2; text-align: left;}
	.content-tip th.label-tip, .content-tip td.label-tip {text-align: center;}
</style>
