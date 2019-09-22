<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;

$this->title = $name;
$bundle = \system\modules\main\assets\ErrorAsset::register($this);

$message = $exception->getMessage();
// 如果出现了代码或者路径，那么屏蔽错误
if (strpos($message, '/system/') > -1) {
    //$message = '出现了错误';
    $message = '';
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <?= Html::csrfMetaTags() ?>
    <title>错误提示页面</title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="service-error-block">
    <!--页面弄丢了-->
    <div class="service-error">
        <?php if ($exception->statusCode == '404'): ?>
            <img src="<?= $bundle->baseUrl?>/image/error/error1.png">
            <h3><img src="<?= $bundle->baseUrl?>/image/error/lost1.png"></h3>
        <?php else: ?>
            <img src="<?= $bundle->baseUrl?>/image/error/error.png">
            <h3><img src="<?= $bundle->baseUrl?>/image/error/lost.png"></h3>
            <?php if ($message):?>
            <div class="error-prompting">
                <p><?= nl2br(Html::encode($message)) ?></p>
                <p>如果你认为是系统方面的问题 请联系管理员!</p>
            </div>
            <?php endif;?>
        <?php endif;?>
        <ul class="error-operation">
            <li><a href="javascript:location.reload();"><i class="fa fa-refresh"></i>刷新 <span>网络不给力</span></a></li>
            <li><a href="<?= Yii::$app->getHomeUrl()?>"><i class="fa fa-home"></i>回首页</a></li>
            <li><a href="javascript:history.go(-1)"><i class="fa fa-rotate-left (alias)"></i>后退一步</a></li>
        </ul>
    </div>
</div>

<!--版权信息-->
<div class="service-error-copy">
    <p>Copyright © 2015-<?= date('Y')?> <?= Yii::$app->systemConfig->getValue('COMPANY_NAME')?> 版权所有</p>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>