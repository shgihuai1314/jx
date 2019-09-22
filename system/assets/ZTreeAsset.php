<?php
/**
 * Created by PhpStorm.
 * User: luobo
 * Date: 2016-11-2
 * Time: 15:53
 */

namespace system\assets;

use yii\web\AssetBundle;

class ZTreeAsset extends AssetBundle
{
	public $basePath = '@webroot/static/';
	public $baseUrl = '@web/static/';
	
	public $js = [
		'/static/lib/ztree/js/jquery.ztree.core-3.5.js',
		'/static/lib/ztree/js/jquery.ztree.excheck-3.5.js',
		'/static/lib/ztree/js/jquery.ztree.exedit-3.5.js',
		'/static/lib/ztree/js/jquery.ztree.exhide-3.5.js'
	];
	public $css = [
		'/static/lib/ztree/css/zTreeStyle/zTreeStyle.css',
		'css/ztree-custom.css',
	];
    public $depends = [
	    'system\assets\MainAsset',
    ];
}