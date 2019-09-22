<?php
/**
 * service-hall.
 * User: ligang
 * Date: 2017/8/24 下午3:47
 */
namespace system\modules\main\assets;

use yii\web\AssetBundle;

class MainFrameAsset extends AssetBundle
{
    public $sourcePath = '@system/modules/main/static/';

	public $css = [
	    '/static/css/global.css',
		'css/frame.css',
        'css/statistics.css',
	];

	public $js = [
        'js/frame.js',
	];

	public $depends = [
		'\system\assets\LayuiAsset',
	];
}