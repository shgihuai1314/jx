<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2018-3-27
 * Time: 16:09
 */

namespace system\modules\main\assets;

use yii\web\AssetBundle;

class GiiAsset extends AssetBundle
{
    public $sourcePath = '@system/modules/main/static';
    public $css = [
        'css/gii.css'
    ];
    public $js = [
        'js/gii.js'
    ];
    public $depends = [
        'system\assets\MainAsset'
    ];
}