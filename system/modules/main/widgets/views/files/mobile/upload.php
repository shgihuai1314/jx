<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/28
 * Time: 13:23
 */

/** @var \yii\web\View $this */
/** @var array $button */
/** @var array $params */
/** @var string $inputName */
/** @var array|string|integer $files */
/** @var integer $flag */
/** @var bool $resetName */
/** @var array $permission */
/** @var array $exts */

use system\modules\main\models\Fileinfo;
use system\core\utils\Tool;
use yii\helpers\Html;

\system\modules\main\assets\FileInfoAssets::register($this);

// 附件显示样式(0: 附件图标展示；1：图片展示)
$show_type = $params['accept'] == 'images' ? 1 : 0;

$this->registerJsFile('/static/lib/jquery/jquery.form.js', ['position' => \yii\web\View::POS_BEGIN]);

$params['url'] = $params['url'] . '?field=' . $params['field'] . '&flag=' . ($resetName ? 1 : 0);
foreach ($params['data'] as $key => $val) {
	$params['url'] .= '&'.$key.'='.$val;
}
?>
<div class="weui-cells weui-cells_form mt10">
	<div class="weui-cell">
		<div class="weui-cell__bd">
			<div class="weui-uploader">
				<div class="weui-uploader__hd" style="padding-bottom: 6px">
                    <?php if (in_array('upload', $permission)) : ?>
                    <?= Html::input('file', $params['field'] . ($params['multiple'] ? '[]' : ''), '', [
                        'id' => $button['id'],
                        'class' => 'weui-uploader__input',
                        'multiple' => $params['multiple'],
	                    'accept' => $params['accept'] == 'images' ? 'image/*' : '',
                    ])
                    ?>
                    <?php endif;?>
					<p class="weui-uploader__title"><?= $button['title'] ?></p>
                    <?php if (in_array('upload', $permission)) : ?>
					<div class="weui-uploader__info">
						<i class='fa fa-<?= $button['icon'] ?>'></i>
					</div>
                    <?php endif;?>
				</div>
				<div class="weui-uploader__bd">
                    <input type="hidden" name="<?=$inputName?>" value=""/>
					<ul class="weui-uploader__files" id="<?= $button['id'] ?>-list">
                        <?php
                        if (!empty($files)) {
                            if ($params['multiple']) {//多个附件
                                $arr = [];
                                $files = is_array($files) ? $files : explode(',', $files);
                                if ($flag == 0) {// $files为附件ID数组
                                    $list = Fileinfo::find()->asArray()->where(['is_del' => 0, 'file_id' => $files])->all();
                                    if (!empty($list)) {
                                        foreach ($list as $file) {
                                            $arr[] = [
                                                'src' => $file['src'],
                                                'name' => $file['name'],
                                                'type' => $file['file_type'],
                                                'size' => $file['size'],
                                            ];
                                        }
                                    }
                                } else {// $files为附件路径数组
                                    foreach ($files as $file) {
                                        if (!empty($file)) {
                                            $arr[] = [
                                                'src' => $file,
                                                'name' => basename($file),
                                                'type' => Tool::getFileType(substr($file, strrpos($file, '.')+1)),
                                                'size' => file_exists(Yii::getAlias('@webroot') . @iconv('utf-8', 'gb2312//IGNORE', $file))
                                                    ? Tool::bytes_format(filesize(Yii::getAlias('@webroot') . @iconv('utf-8', 'gb2312//IGNORE', $file))) : '0b',
                                            ];
                                        }
                                    }
                                }
                                
                                if ($show_type == 0) {//附件图标展示
                                    foreach ($arr as $key => $val) {
                                        echo Html::tag('li', Html::hiddenInput($inputName . '[a'.$key.']', $flag ? $val['src'] : json_encode($val)) .
                                            Html::img('/static/images/filetype/' . $val['type'] . '_lt.png') .
                                            Html::tag('div',
                                                Html::tag('p', $val['name'] . Html::tag('span', ' （' . $val['size'] . '）'), ['class' => 'file-name']) .
                                                (in_array('download', $permission) ?
                                                    Html::a('下载', $val['src'], ['class' => 'download-btn', 'download' => $val['name']]) . "&nbsp;&nbsp;" : '') .
                                                (in_array('delete', $permission) ?
                                                    Html::tag('a', '删除', ['class' => 'del-btn', 'onclick' => 'javascript:$(this).parent().parent().remove()']) : ''),
                                                ['class' => 'file-info']),
                                            ['class' => 'file-item']);
                                    }
                                } else {// 图片展示
                                    foreach ($arr as $n => $val) {
                                        echo Html::tag('li', Html::hiddenInput($inputName . '[a'.$n.']', $flag ? $val['src'] : json_encode($val)) .
                                            Html::img($val['type'] == 'image' ? $val['src'] : '/static/images/filetype/' . $val['type'] . '.png', ['id' => 'layer-photo-' . $n]) .
                                            (in_array('delete', $permission) ?
                                                Html::tag('i', '', ['class' => 'fa fa-times-circle', 'onclick' => 'javascript:$(this).parent().remove()']) : ''),
                                            ['class' => 'image-item']);
                                    }
                                }
                                
                            } else {// 单个附件
                                if ($flag == 0) {// $files为附件ID
                                    $file = Fileinfo::find()->asArray()->where(['is_del' => 0, 'file_id' => $files])->one();
                                    if (!empty($file)) {
                                        $val = [
                                            'src' => $file['src'],
                                            'name' => $file['name'],
                                            'type' => $file['file_type'],
                                            'size' => $file['size'],
                                        ];
                                    }
                                } else {// $files为附件路径
                                    $val = [
                                        'src' => $files,
                                        'name' => basename($files),
                                        'type' => Tool::getFileType(substr($files, strrpos($files, '.')+1)),
                                        'size' => file_exists(Yii::getAlias('@webroot') . @iconv('utf-8', 'gb2312//IGNORE', $file))
                                            ? Tool::bytes_format(filesize(Yii::getAlias('@webroot') . @iconv('utf-8', 'gb2312//IGNORE', $file))) : '',
                                    ];
                                }
                                
                                if (!empty($val)) {
                                    if ($show_type == 0) {//附件图标展示
                                        echo Html::tag('li', Html::hiddenInput($inputName, $flag ? $val['src'] : json_encode($val)) .
                                            Html::img('/static/images/filetype/' . $val['type'] . '_lt.png') .
                                            Html::tag('div',
                                                Html::tag('p', $val['name'] . Html::tag('span', ' （' . $val['size'] . '）'), ['class' => 'file-name']) .
                                                (in_array('download', $permission) ?
                                                    Html::a('下载', $val['src'], ['class' => 'download-btn', 'download' => $val['name']]) . "&nbsp;&nbsp;" : '') .
                                                (in_array('delete', $permission) ?
                                                    Html::tag('a', '删除', ['class' => 'del-btn', 'onclick' => 'javascript:$(this).parent().parent().remove()']) : ''),
                                                ['class' => 'file-info']),
                                            ['class' => 'file-item']);
                                    } else {// 图片展示
                                        echo Html::tag('li', Html::hiddenInput($inputName, $flag ? $val['src'] : json_encode($val)) .
                                            Html::img($val['type'] == 'image' ? $val['src'] : '/static/images/filetype/' . $val['type'] . '.png') .
                                            (in_array('delete', $permission) ?
                                                Html::tag('i', '', ['class' => 'fa fa-times-circle', 'onclick' => 'javascript:$(this).parent().remove()']) : ''),
                                            ['class' => 'image-item']);
                                    }
                                }
                            }
                        }
                        ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
    var fileIndex = 0; // 初始化name数组中的索引，因为ajax处理时必须要处理不同的name
    $('#<?= $button['id'] ?>').wrap("<form class='myFileUpload' enctype='multipart/form-data'></form>");
    $('#<?= $button['id'] ?>').change(function () {
        var files = $(this).prop('files');
        var exts = '<?= $params['exts']; ?>';
        var accept = '<?= $params['accept']; ?>';

        for (var i in files) {
            if (typeof files[i] == 'object') {
                var name = files[i].name;
                if (!checkExt(name, exts, accept)) {
                    parent.layer.msg("选择的文件中包含不支持的格式", {icon: 2, anim: 6, offset: '150px'});
                    return;
                }
            }
        }

        $(this).parent().ajaxSubmit({
            dataType: 'json',
            type: 'post',
            url: '<?= $params['url']?>',
            data: $.extend({field: '<?= $params['field'] ?>', flag: <?= $resetName ? 1 : 0 ?>,
                <?php
                $list = [];
                foreach ($params['data'] as $key => $val) {
                    $list[] = $key.': "'.$val.'"';
                }
                echo implode(',', $list);
                ?>
            }, param),
            beforeSend: function () {
                $('body').append('<div class="weui-toast weui-toast--text weui-toast--visible"><div class="weui_loading"><i class="weui-loading weui-icon_toast"></i></div><p class="weui-toast_content">上传中…… 0%</p></div>')
            },
            uploadProgress: function (event, position, total, percentComplete) {
                var percentVal = percentComplete + '%';
                $('.weui-toast .weui-toast_content').html('上传中…… ' + percentVal);
            },
            success: function (res) {
                $('.weui-toast').remove();
                if (res.code != 0) {
                    $.toast(res.msg, 'cancel');
                } else {
                    <?php if ($params['done'] == null) : ?>
                    for (var i in res.data) {
                        fileIndex ++;
                        var file = res.data[i];
                        var value = <?= $flag ?> ? file.src : JSON.stringify(file);

                        var inputName = <?= $params['multiple'] ? 'true' : 'false' ?> ? '<?= $inputName ?>' + '[' + fileIndex + ']' : '<?= $inputName ?>';
                        <?php if ($show_type == 0) : ?>
                        $('#<?= $button['id'] ?>-list').<?=$params['multiple']?'append':'html'?>(
                            '<li class="file-item">' +
                            '<input type="hidden" name="' + inputName + '" value=\'' + value + '\'/>' +
                            '<img src="/static/images/filetype/' + file.type + '_lt.png' + '">' +
                            '<div class="file-info">' +
                            '<p class="file-name">' + file.name + '<span>（' + file.size + '）</span></p>' +
                            '<a class="download-btn" href="' + file.src + '" download="' + file.name + '">下载</a>&nbsp;&nbsp;' +
                            '<a class="del-btn" onclick="javascript:$(this).parent().parent().remove()">删除</a>' +
                            '</div>' +
                            '</li>'
                        );
                        <?php else : ?>
                        $('#<?= $button['id'] ?>-list').<?=$params['multiple']?'append':'html'?>(
                            '<li class="image-item">' +
                            '<input type="hidden" name="' + inputName + '" value=\'' + value + '\'/>' +
                            '<img src="' + (file.type == 'image' ? file.src : '/static/images/filetype/' + file.type + '.png') + '">' +
                            '<i class="fa fa-times-circle" onclick="javascript:$(this).parent().remove()"></i></li>'
                        );
                        <?php endif; ?>
                    }
                    <?php else : ?>
                    <?= $params['done'] ?>
                    <?php endif; ?>
                }
            },
            error: function (xhr) {
                $('.weui-toast').remove();
                //console.log(xhr);
                $.toast('上传失败', 'cancel');
            }
        });
    })

    /**
     * 检查文件类型是否支持
     * @param name 要检查的文件名
     * @param exts 支持的后缀名 如jpg|gif|bmp|png
     * @param accept 接收的文件类型 如images、file、video、audio
     * @returns {boolean}
     */
    function checkExt(name, exts, accept) {
        var ext = name.substring(name.lastIndexOf(".") + 1, name.length).toLowerCase();
        if (exts != '' && exts.indexOf(ext) == -1) {
            return false;
        } else {
            switch (accept) {
                case"images":
                    if ("jpg|png|gif|bmp|jpeg".indexOf(ext) == -1) {
                        return false;
                    }
                    break;
                case"video":
                    if ("avi|mp4|wma|rmvb|rm|flash|3gp|flv".indexOf(ext) == -1) {
                        return false;
                    }
                    break;
                case"audio":
                    if ("mp3|wav|mid".indexOf(ext) == -1) {
                        return false;
                    }
                    break;
            }
        }
        return true;
    }
</script>