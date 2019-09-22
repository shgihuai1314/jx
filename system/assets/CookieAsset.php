<?php
/**
 * service-hall.
 * User: ligang
 * Date: 2017/8/24 下午3:52
 */
namespace system\assets;

use yii\web\AssetBundle;

class CookieAsset extends AssetBundle
{
    public $basePath = '@webroot/static/';
    public $baseUrl = '@web/static/';

    public $js = [
        'lib/jquery/js.cookie.js', // 提示音
    ];
}