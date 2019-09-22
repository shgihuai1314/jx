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
 * 引入字体文件
 * @package system\assets
 */
class IconFontAsset extends AssetBundle
{
    public $basePath = '@webroot/static/';
    public $baseUrl = '@web/static/';
    public $css = [
        'lib/font-awesome/css/font-awesome.min.css',
//        'lib/iconfont/iconfont.css',

//    正在调整样式 暂时先用网址代替图标库 方便更换 调整结束之后换回来
        'https://at.alicdn.com/t/font_329239_d5xem7lhtee.css'
    ];
}