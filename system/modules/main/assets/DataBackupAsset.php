<?php
namespace system\modules\main\assets;

use yii\web\AssetBundle;


class DataBackupAsset extends AssetBundle
{
    public $sourcePath = '@system/modules/main/static';
    public $css = [
    ];
    public $js = [
        'js/data-backup.js',
    ];
    public $depends = [
    ];
}