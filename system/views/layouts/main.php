<?php
use yii\helpers\Html;

\system\assets\IconFontAsset::register($this);
\system\assets\Select2Asset::register($this);
\system\assets\MainAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
	<meta charset="<?= Yii::$app->charset ?>">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <script type="text/javascript">
        // 闪屏消息
        var flashMsg = <?= \system\widgets\FlashMsg::widget()?>;
        // 全局默认的删除url
        var deleteUrl = '<?= \yii\helpers\Url::toRoute(['delete', 'id' => ''])?>';
        var cacheVersion = <?= Yii::$app->systemConfig->getValue('CACHE_VERSION', 1)?>; // 缓存版本
    </script>
	<?php $this->head() ?>
	<?= Yii::$app->systemConfig->getValue('MAIN_LAYOUT_HEADER_EXTEND') ?>
</head>

<body style="overflow-x:hidden;">
    <?php $this->beginBody() ?>
    <div class="admin-main page-bgf clearfix">
        <?= $content?>
    </div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>