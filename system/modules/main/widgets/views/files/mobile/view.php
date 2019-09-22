<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/9/14
 * Time: 20:42
 */

/** @var \yii\web\View $this */
/** @var array $files */
/** @var integer $flag */
/** @var integer $canDownload */

$asset = \system\modules\main\assets\FileInfoAssets::register($this);
$static = $asset->baseUrl;

use system\core\utils\Tool;
use yii\helpers\Html;

$data = [
    'images' => [],
    'files' => []
];
if ($flag == 0) {// $files为ID数组
    $list = Yii::$app->systemFileInfo->get($files);
    if (!empty($list)) {
        foreach ($list as $file) {
            $data[$file['file_type'] == 'image' ? 'images' : 'files'][] = [
                'src' => $file['src'],
                'name' => $file['name'],
                'type' => $file['file_type'],
                'size' => $file['size'],
            ];
        }
    }
} else {// $files为src数组
    foreach ($files as $file) {
        $Filetype = Tool::getFileType(substr($file, strrpos($file, '.')+1));
        $data[$Filetype == 'image' ? 'images' : 'files'][] = [
            'src' => $file,
            'name' => basename($file),
            'type' => $Filetype,
            'size' => Tool::bytes_format(filesize(Yii::getAlias('@webroot') . @iconv('utf-8', 'gb2312//IGNORE', $file))),
        ];
    }
}

?>
<?php if ($data['images'] || $data['files']): ?>
<div class="weui-cells weui-cells_form">
	<div class="weui-cell">
		<div class="weui-cell__bd">
			<div class="weui-file-view">
				<div class="weui-file-view__bd">
					<?php
                    echo count($data['images']) > 0 ? '<p class="weui-uploader__title"><i class="fa fa-image"></i>&nbsp;<span>图片</span> (' . count($data['images']) . '个)</p>' : '';
                    echo '<ul class="weui-file-view__images clearfix">';
                    foreach ($data['images'] as $n => $one) {
                        echo Html::tag('li', Html::img($one['src'], ['class' => 'img-view', 'data-name' => $one['name']]));
                    }
                    echo '</ul>';

                    echo count($data['files']) > 0 ? '<p class="weui-uploader__title"><i class="iconfont icon-up-down"></i>&nbsp;<span>附件</span> (' . count($data['files']) . '个)</p>' : '';
                    echo '<ul class="weui-file-view__files clearfix">';
                    foreach ($data['files'] as $n => $one) {
                        echo Html::tag('li',
                            Html::img('/static/images/filetype/' . $one['type'] . '_lt.png') .
                            Html::tag('div',
                                Html::tag('p', $one['name'] . Html::tag('span', ' （' . $one['size'] . '）'), ['class' => 'file-name']) .
                                ($canDownload ? Html::a('下载', $one['src'], ['download' => $one['name']]) : ''),
                                ['class' => 'file-info'])
                        );
                    }
                    echo '</ul>';
					?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php endif;?>