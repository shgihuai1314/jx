<?php
/**
 * service-hall.
 * User: ligang
 * Date: 2017/8/28 下午2:28
 */
namespace system\assets;

use yii\web\AssetBundle;
use yii\web\View;

class JqueryAsset extends AssetBundle
{
    public $basePath = '@webroot/static/';
    public $baseUrl = '@web/static/';

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
    public $js = [
        'lib/jquery/jquery-1.11.3.min.js',
    ];
}