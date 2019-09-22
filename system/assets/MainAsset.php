<?php
/**
 * service-hall.
 * User: ligang
 * Date: 2017/8/2 下午8:29
 */
namespace system\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Class MainAsset 主框架资源包,这个要加载在最上面，因为页面内部直接引用了layui里面的form对象等；
 * @package system\assets
 */
class MainAsset extends AssetBundle
{
    public $sourcePath = '@webroot/static/';
    public $basePath = '@web/static/';

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
	
    public $css = [
	    'css/global.css',
    ];

    public $js = [
    	'js/global.js',
    	'js/json2.js',
    ];

    public $depends = [
        '\system\assets\LayuiAsset',
    ];
}