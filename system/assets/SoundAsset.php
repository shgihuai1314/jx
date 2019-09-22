<?php
/**
 * service-hall.
 * User: ligang
 * Date: 2017/8/24 下午3:50
 */
namespace system\assets;

use yii\web\AssetBundle;

/**
 * Class SoundAsset 播放提示音
 * @package system\assets
 */
class SoundAsset extends AssetBundle
{
    public $basePath = '@webroot/static/';
    public $baseUrl = '@web/static/';
    public $js = [
        'lib/sound/swfobject.js', // 提示音
    ];
}