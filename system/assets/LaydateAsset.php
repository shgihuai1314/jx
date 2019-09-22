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
 * 单独引入laydate日期插件
 * @package system\assets
 */
class LaydateAsset extends AssetBundle
{
    public $basePath = '@webroot/static/';
    public $baseUrl = '@web/static/';
    public $js = [
        'lib/laydate/laydate.js',
    ];
}