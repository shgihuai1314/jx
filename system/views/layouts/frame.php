<?php
use yii\helpers\Html;

\system\assets\IconFontAsset::register($this);
\system\assets\Select2Asset::register($this);
\system\assets\FrameAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
	<meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
    <script>
        var cacheVersion = <?= Yii::$app->systemConfig->getValue('CACHE_VERSION', 1)?>; // 缓存版本
    </script>
	<?php $this->head() ?>
</head>

<body style="overflow-x:hidden;">
    <?php $this->beginBody() ?>
    <?= $content?>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>