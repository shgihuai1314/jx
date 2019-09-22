<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/19
 * Time: 13:47
 */

namespace system\modules\main\assets;

use yii\web\View;
use yii\web\AssetBundle;

class WebOfficeAssets extends AssetBundle
{
    public $basePath = '@webroot/static/lib/weboffice/';
    public $baseUrl = '@web/static/lib/weboffice/';
    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
    
    public $css = [
		'css/WebOffice.css'
	];
	public $js = [
//	    'js/jquery-1.4.2.min.js',
	    'js/WebOffice.js'
    ];
	public $depends = [];
}