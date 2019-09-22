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
 * Class PartialAsset
 * @package system\assets
 */
class FrameAsset extends AssetBundle
{
    public $sourcePath = '@webroot/static/';
    public $basePath = '@web/static/';

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
	
    public $css = [
	    'css/frame.css',
    ];

    public $js = [
    	'js/frame.js',
    ];

    public $depends = [
        '\system\assets\MainAsset',
    ];
}