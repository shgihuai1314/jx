<?php
echo "<?php\n";
?>

namespace system\modules\<?= $module_id ?>\assets;

use yii\web\AssetBundle;

class <?= ucfirst($type) ?>Asset extends AssetBundle
{
    public $sourcePath = '@system/modules/<?= $module_id ?>/static';
    public $css = [
        "css/<?= $type ?>.css",
    ];
    public $js = [
        'js/<?= $type ?>.js',
    ];
    public $depends = [
        '<?= $type == 'mobile' ? 'system\modules\mobile\assets\MainMobileAsset' : 'system\assets\MainAsset'?>',
    ];
}