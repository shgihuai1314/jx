<?php
system\modules\mobile\assets\MessageAsset::register($this);

$this->title = '我的消息';
?>

    <div class="yd-content">
        <div class="weui-cells message-list line-l60 mt0">
            <?php foreach($message as $type => $msg):?>
                <?php
                $count = count(\system\core\utils\Tool::get_array_by_condition($msg, ['is_read' => 0]));
                $one = reset($msg);
                ?>
            <a class="weui-cell weui-cell_access" href="<?=yii\helpers\Url::toRoute(['detail', 'type' => $type])?>">
                <div class="weui-cell__hd">
                    <img src="<?= $one['moduleinfo']['icon'] ?: '/static/images/icon.jpg'?>">
                </div>
                <div class="weui-cell__bd yd-text-overflow">
                    <div class="message-hd weui-flex">
                        <p class="weui-flex__item yd-textoverflow"><?=$one['moduleinfo']['name']?></p>
                        <span><?=\system\core\utils\Tool::showTime($one['created_at'])?></span>
                    </div>
                    <div class="message-desc weui-flex">
                        <span class="weui-flex__item yd-text-overflow"><?= \yii\helpers\StringHelper::truncate($one['content'], '18'); ?></span>
                        <?= empty($count) ? '' : '<span class="weui-badge color-success">' . $count . '</span>'; ?>
                    </div>
                </div>
            </a>
            <?php endforeach;?>
        </div>
    </div>


