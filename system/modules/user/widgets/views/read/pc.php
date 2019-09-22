<?php
use system\modules\user\models\User;

?>
<style>
    .apply-title h3{
        font-size: 14px;
        line-height: 34px;
    }
    .detail-box ul{

    }
    .user-item-li{
        display: inline-block;
        margin-right: 18px;
        margin-bottom: 10px;
        text-align: center;
    }
    .user-item-li p{
        font-size: 14px;
        color: #888;
        margin-top: 6px;
    }
    .read-num{
        display: inline-block;
        padding:0 1px;
        font-size: 16px;
        font-weight: bold;
        color: #808080;
    }
</style>
<div class="layui-row">
    <div class="layui-tab layui-tab-brief">
        <ul class="layui-tab-title">
            <li class="layui-this read-num" id="read-1">已读<?= count($readData['yes'])?>人</li>
            <li class="read-num" id="read-0">未读<?= count($readData['no'])?>人</li>
        </ul>
    </div>
    <div class="fly-panel detail-box mb20" id="comment-box" style="padding-top: 0;">
        <ul class="mb10" id="read1">
            <?php foreach($readData['yes'] as $val): ?>
                <li class="user-item-li">
                    <img class="img-circle" src="<?= User::getInfo($val['user_id'], 'avatar') ?>" width="40" height="40">
                    <p><?= User::getInfo($val['user_id']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>

        <ul class="mb10" id="read0" style="display: none;">
            <?php foreach($readData['no'] as $val): ?>
                <li class="user-item-li">
                    <img class="img-circle" src="<?= User::getInfo($val['user_id'], 'avatar') ?>" width="40" height="40">
                    <p><?= User::getInfo($val['user_id']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<script>
    $("#read-0").on('click', function(){
        $("#read1").css("display","none");
        $("#read0").css("display","block");
    })
    $("#read-1").on('click', function(){
        $("#read0").css("display","none");
        $("#read1").css("display","block");
    })
</script>