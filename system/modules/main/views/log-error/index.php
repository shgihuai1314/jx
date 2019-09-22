<?php
$log_types = Yii::$app->systemConfig->getValue('LOG_ERROR_TYPE_LIST', []);

// 搜索关键字
$keyword = Yii::$app->request->get('keyword');
// 当前组
$type = Yii::$app->request->get('type');

?>
<form class="mb20" action="" method="get">
    <div class="layui-input-inline" style="width: 300px;">
        <input type="text" name="keyword" required lay-verify="required" placeholder="请输入" autocomplete="off"
               class="layui-input" value="<?= $keyword ?>">
    </div>
    <button class="layui-btn ">搜索</button>

    <span class="layui-breadcrumb" lay-separator="|" style="margin-left: 30px;">
        <a href="<?= \yii\helpers\Url::toRoute(['']) ?>" <?php if ('' == $type) echo 'class="layui-this"'; ?> >全部</a>
        <?php foreach ($log_types as $key => $value): ?>
            <a href="<?= \yii\helpers\Url::toRoute(['', 'keyword' => $keyword, 'type' => $key]) ?>" <?php if ($key == $type) echo 'class="layui-this"'; ?> ><?= $value ?></a>
        <?php endforeach; ?>
    </span>
</form>

<div class="ibox float-e-margins">
    <div class="ibox-title">
        <h5>错误日志</h5>
    </div>
    <div class="ibox-content" style="padding-top: 0">
        <ul class="feed-activity-list">
            <?php foreach ($logs as $log):?>
                <li class="feed-element clearfix">
                    <div class="avatar">
                        <div class="avatar-box-left">
                            <img class='avatar-mini img-circle' src='/static/images/avatar/default/system.png' />
                        </div>
                        <div class="avatar-box-right">
                            <div class="comment-box">
                                <p class="error-name"><?= $log['level']==1?'错误':'警告' ?></p>
                                <span><?= date('Y-m-d H:i:s', $log['log_time'])?></span>
                                <span><?= $log['category']?></span>
                                <div class="comment word-break">
                                    <?= $log['prefix']?><br />
                                    <?= \yii\helpers\Html::encode($log['message'])?>
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

