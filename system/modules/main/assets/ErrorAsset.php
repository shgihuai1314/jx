<?php
/**
 * service-hall.
 * User: ligang
 * Date: 2017/8/21 上午11:27
 */
namespace system\modules\main\assets;

use yii\web\AssetBundle;

class ErrorAsset extends AssetBundle
{
    public $sourcePath = '@system/modules/main/static';
    public $css = [
        'css/error.css',
    ];

    public $depends = [
        'system\assets\IconFontAsset',
    ];

}