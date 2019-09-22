<?php

namespace system\modules\article\assets;

use yii\web\AssetBundle;

class ArticleAsset extends AssetBundle
{
    public $sourcePath = '@system/modules/article/static';
    public $css = [
        "css/article.css",
    ];
    public $js = [
        'js/article.js',
    ];
    public $depends = [
        'system\assets\MainAsset',
    ];
}