<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/5/15
 * Time: 下午5:09
 */

namespace system\assets;


use yii\web\AssetBundle;
use yii\web\View;

/**
 * select2 组件
 * @package system\modules\user\assets
 */
class Select2Asset extends AssetBundle
{
    public $basePath = '@webroot/static/';
    public $baseUrl = '@web/static/';

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];

    public $css = [
        'lib/select2/css/select2.min.css',
    ];
    public $js = [
        'lib/select2/js/select2.full.js',
        'lib/select2/js/i18n/zh-CN.js',
    ];

    public $depends = [
        'system\assets\MainAsset'
    ];
}