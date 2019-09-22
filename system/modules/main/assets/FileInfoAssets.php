<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/19
 * Time: 13:47
 */

namespace system\modules\main\assets;

use system\core\utils\Tool;
use yii\web\AssetBundle;

class FileInfoAssets extends AssetBundle
{
	public $sourcePath = '@system/modules/main/static';
	public $css = [
		'css/fileinfo.css'
	];
	public $js = [
	    'js/fileinfo.js'
    ];
	public $depends = [];
}