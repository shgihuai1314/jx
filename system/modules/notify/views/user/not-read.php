<?php

use system\modules\notify\models\NotifyMessage;
use system\modules\main\models\Modules;
use yii\helpers\Url;

$message = NotifyMessage::messageInfo();
$moduleMap = Modules::getModuleMap();
?>
<div class="msg-ift">
    <h3>消息通知</h3>
    <div class="msg-block">
        <ul class="reminder-list clearfix">
            <?php foreach ($message as $key => $val): ?>
                <li>
                    <span class="text-yellow"><?= count($val) ?></span>条 <?= $moduleMap[$key] ?>,
                    <a class="text-blue moduleNotifyList" data-module_id="<?= $key ?>" style="cursor: pointer;"
                       data-url="<?= Url::toRoute(['/notify/user/view', 'module' => $key]) ?>">查看详情 </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
