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
/** @var array $wordOperate */

\system\modules\main\assets\FileInfoAssets::register($this);

use system\core\utils\Tool;
use yii\helpers\Html;

$isIE8 = strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 8.0') != false;

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
        $Filetype = Tool::getFileType(substr($file, strrpos($file, '.') + 1));
        $data[$Filetype == 'image' ? 'images' : 'files'][] = [
            'src' => $file,
            'name' => basename($file),
            'type' => Tool::getFileType(substr($file, strrpos($file, '.') + 1)),
            'size' => Tool::bytes_format(filesize(Yii::getAlias('@webroot') . @iconv('utf-8', 'gb2312//IGNORE', $file))),
        ];
    }
}

if (count($data['images']) > 0) {// 显示图片相册
    echo '<div class="file-view">';
    echo '<h3><i class="fa fa-image"></i> <span>图片</span> (' . count($data['images']) . '个)</h3>';
    echo '<ul class="layer-photos-list clearfix">';
    foreach ($data['images'] as $n => $one) {
        echo Html::tag('li',
            Html::img($one['type'] == 'image' ? $one['src'] : '/static/images/filetype/' . $one['type'] . '.png',
                ['id' => 'layer-photo-' . $n, 'alt' => $one['name']]) .
            Html::tag('span', $one['name'], ['class' => 'filename', 'title' => $one['name']]) .
            ($canDownload ? Html::a($one['size'] . ' <i class="fa fa-download"></i>', $one['src'], [
                'download' => $one['name'],
                'class' => 'file-desc'
            ]) : '')
        );
    }
    echo '</ul></div>';
}

if (count($data['files']) > 0) {// 显示附件列表
    echo '<div class="file-view">';
    echo '<h3><i class="fa fa-paperclip"></i> <span>附件</span> (' . count($data['files']) . '个)</h3>';
    echo '<ul class="file-list clearfix">';
    foreach ($data['files'] as $n => $one) {
        $download = "";
        $view = "";
        if ($canDownload) {
            // 下载
            $download = Html::a('下载', $one['src'], ['download' => $one['name']]);
            // 如果是word,excel,ppt类型并且拥有查看权限
            if (in_array($one['type'], ['word', 'excel', 'ppt']) && in_array('view', $wordOperate)) {
                $view = Html::tag('a', '查看', [//调用金格的在线查看office插件
                    'class' => 'view-btn',
                    'data-filename' => $one['name'],
                    'data-src' => $one['src'],
                    'data-edittype' => in_array('edit', $wordOperate) ? 2 : 0,
                    'data-writing' => in_array('writing', $wordOperate) ? 1 : 0,
                    'data-template' => in_array('useTemplate', $wordOperate) ? 1 : 0,
                    'data-print' => in_array('print', $wordOperate) ? 1 : 0,
                ]);
            } elseif ($one['type'] == 'pdf') {// pdf类型
                $view = Html::a('查看', (!$isIE8 ? '/pdf/viewer.html?file=' : '') . $one['src'], ['target' => '_blank']);
            }
        }

        echo Html::tag('li',
            Html::img('/static/images/filetype/' . $one['type'] . '_lt.png') .
            Html::tag('div',
                Html::tag('p', $one['name'] . Html::tag('span', ' （' . $one['size'] . '）'), ['class' => 'file-name']) .
                $download . $view,
                ['class' => 'file-info'])
        );
    }
    echo '</ul></div>';
}