<?php
/**
 * service-hall.
 * User: ligang
 * Date: 2017/8/24 下午3:16
 */
namespace system\assets;

use yii\web\AssetBundle;
use yii\web\View;

class LayuiAsset extends AssetBundle
{
    public $basePath = '@webroot/static/';
    public $baseUrl = '@web/static/';

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];

    public $css = [
        'lib/layui/css/layui.css',
    ];

    public $js = [
        'lib/layui/layui.all.js',
    ];

    public $depends = [
        //'yii\web\JqueryAsset'
    ];

}