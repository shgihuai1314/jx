<?php

namespace system\modules\article\assets;

use yii\web\AssetBundle;

class MobileAsset extends AssetBundle
{
    public $sourcePath = '@system/modules/article/static';
    public $css = [
        "css/mobile.css",
    ];
    public $js = [
        'js/mobile.js',
    ];
    public $depends = [
        'system\modules\mobile\assets\MainMobileAsset',
    ];
}