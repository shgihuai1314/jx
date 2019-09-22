<?php

/**
 * Created by PhpStorm.
 * User: THINKPAD
 * Date: 2017/8/2
 * Time: 10:34
 */
namespace system\modules\user\assets;
use yii\web\AssetBundle;


class LoginAsset extends AssetBundle
{
    public $sourcePath = '@system/modules/user/static';
    public $css = [
        'css/login.css',
    ];
}