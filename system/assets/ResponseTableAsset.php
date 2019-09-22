<?php
/**
 * service-hall.
 * User: ligang
 * Date: 2017/9/7 下午3:41
 */
namespace system\assets;

use yii\web\AssetBundle;

/**
 * Class ResponseTableAsset 响应式表格资源
 * @package system\assets
 */
class ResponseTableAsset extends AssetBundle
{
    public $basePath = '@webroot/static/';
    public $baseUrl = '@web/static/';

    public $js = [
        'lib/jquery.basictable/jquery.basictable.js',
    ];

    public $css = [
        'lib/jquery.basictable/basictable.css',
    ];

    public $depends = [
        //'system\assets\JqueryAsset',
    ];

}