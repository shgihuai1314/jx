<?php
/**
 * Created by PhpStorm.
 * User: THINKPAD
 * Date: 2017/10/31
 * Time: 15:36
 */
namespace system\modules\notify\assets;


use yii\web\AssetBundle;

class notifyAsset extends AssetBundle
{
    public $sourcePath = '@system/modules/notify/static';
    public $css = [
        'css/notify.css'
    ];
    public $js = [

    ];
    public $depends = [
        'system\assets\MainAsset',
    ];
}