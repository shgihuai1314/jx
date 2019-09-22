<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/6/12
 * Time: 下午6:01
 */
namespace system\assets;

use yii\web\AssetBundle;

/**
 * 单独引入layer弹框插件，注意需要引入jquery 1.8+
 * @package system\assets
 */
class LayerAsset extends AssetBundle
{
    public $basePath = '@webroot/static/';
    public $baseUrl = '@web/static/';
    public $js = [
        'lib/layer/layer.js',
    ];

    public $depends = [
        'system\assets\JqueryAsset'
    ];
}