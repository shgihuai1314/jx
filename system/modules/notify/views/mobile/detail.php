<?php
system\modules\mobile\assets\MessageAsset::register($this);
$this->title = $title;
?>
<div class="yd-content">
    <div class="msg-info-wrap">
        <?php foreach($list as $item):?>
        <div class="msg-info-detail">
            <p class="msg-time"><?=\system\core\utils\Tool::showTime($item['created_at'])?></p>
            <a class="msg-item" href="javascript:;">
                <p class="msg-content"><?= $item['content']?></p>
                <span class="msg-link" id="message-detail" data-url="<?=yii\helpers\Url::toRoute($item['url'])?>" data-id="<?=$item['message_id']?>">查看详情</span>
                <span class="color-red" id="del_message" data-id="<?=$item['message_id']?>">删除</span>
            </a>
        </div>
        <?php endforeach;?>
    </div>
</div>
<script>
    $(document).on('click', '#message-detail', function () {
        var id = $(this).data('id');
        var location_url = $(this).data('url');
        $.get('<?= yii\helpers\Url::to('')?>','message_id='+id, function (msg) {
            window.location=location_url;
        })
    });

    $(document).on('click', '#del_message', function () {
        var id = $(this).data('id');
        $.get('<?= yii\helpers\Url::toRoute('/notify/mobile/del')?>','message_id='+id, function (msg) {
            if(msg.code == 0){
                $.toptip('操作成功', 'success');
                function reload(){
                    window.location.reload();
                }
                setInterval(reload,1500);
            }else {
                $.toptip('操作失败', 'error');
            }
        })
    });
</script>