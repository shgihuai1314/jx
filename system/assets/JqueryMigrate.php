<?php
/**
 * service-hall.
 * User: ligang
 * Date: 2017/7/25 下午4:25
 */
namespace system\assets;

use yii\web\AssetBundle;

/**
 * Class JqueryMigrate 用于兼容jquery 1.9之前的jquery版本
 * @package system\assets
 */
class JqueryMigrate extends AssetBundle
{
    public $basePath = '@webroot/static/';
    public $baseUrl = '@web/static/';
    public $js = [
        'lib/jquery/jquery-migrate-1.1.1.min.js',
    ];
}