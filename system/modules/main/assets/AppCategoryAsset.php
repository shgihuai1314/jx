<?php
/**
 * Created by PhpStorm.
 * User: THINKPAD
 * Date: 2017/6/13
 * Time: 15:12
 */

namespace system\modules\main\assets;


use yii\web\AssetBundle;

class AppCategoryAsset extends AssetBundle
{
    public $sourcePath = '@system/modules/main/static';
    public $css = [
    ];
    public $js = [
        'js/appCategory.js',
    ];
    public $depends = [
    ];
}