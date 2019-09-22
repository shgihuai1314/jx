<?php
/**
 * Created by PhpStorm.
 * User: THINKPAD
 * Date: 2017/8/16
 * Time: 11:53
 */

namespace system\modules\main\assets;


use yii\web\AssetBundle;

class BatchOperationAsset extends AssetBundle
{
    public $sourcePath = '@system/modules/main/static';
    public $css = [
    ];
    public $js = [
        'js/batch-operation.js',
    ];
    public $depends = [
    ];
}