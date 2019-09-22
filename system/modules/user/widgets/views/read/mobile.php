<?php
/**
 * yd-service.
 * User: ligang
 * Date: 2018/1/10 下午10:03
 */
use system\modules\user\models\User;

?>

<div class="weui-tab comment-box mt10">
    <div class="weui-navbar">
        <a class="weui-navbar__item weui-bar__item--on" href="#read">
            已读 (<?= count($readData['yes'])?>人)
        </a>
        <?php if (count($readData['no']) > 0): ?>
        <a class="weui-navbar__item" href="#no_read">
            未读 (<?= count($readData['no'])?>人)
        </a>
        <?php endif;?>
    </div>
    <div class="weui-tab__bd page-bgf">
        <div id="read" class="weui-tab__bd-item weui-tab__bd-item--active">
            <div class="weui-panel weui-panel_access select-user box-pd before-none">
                <div class="weui-media-box">
                    <ul class="user-item-box">
                        <?php foreach ($readData['yes'] as $val): ?>
                            <?php
                            $real_name = User::getInfo($val['user_id']);
                            if (!$real_name) {
                                continue;
                            }
                            $avatar = User::getInfo($val['user_id'], 'avatar');
                            ?>
                            <li>
                                <img src="<?= empty($avatar) ? Yii::$app->request->hostInfo . '/static/images/avatar/default/10.jpg' : $avatar; ?>">
                                <p><?= $real_name ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div id="no_read" class="weui-tab__bd-item">
            <div class="weui-panel weui-panel_access select-user before-none">
                <div class="weui-media-box">
                    <ul class="user-item-box">
                        <?php foreach ($readData['no'] as $val): ?>
                            <?php
                            $real_name = User::getInfo($val['user_id']);
                            if (!$real_name) {
                                continue;
                            }
                            $avatar = User::getInfo($val['user_id'], 'avatar');
                            ?>
                            <li>
                                <img src="<?= empty($avatar) ? '/static/images/avatar/default/10.jpg' : $avatar; ?>">
                                <p><?= $real_name ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
