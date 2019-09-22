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
/** @var array $wordOperate */

/** @var array $exts */

use system\modules\main\models\Fileinfo;
use system\core\utils\Tool;
use yii\helpers\Html;

\system\modules\main\assets\FileInfoAssets::register($this);

// 附件显示样式(0: 附件图标展示；1：图片展示)
$show_type = $params['accept'] == 'images' ? 1 : 0;
// 是否是IE8浏览器
$isIE8 = strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0') != false;
if (!$isIE8) {// 如果不是IE8浏览器，注册/static/lib/jquery/jquery.form.js
    $this->registerJsFile('/static/lib/jquery/jquery.form.js', ['position' => \yii\web\View::POS_BEGIN]);
}
$params['url'] = $params['url'] . (strpos($params['url'], '?') === false ? '?' : '&') .
    'field=' . $params['field'] . '&flag=' . ($resetName ? 1 : 0) . '&isIe8=' . ($isIE8 ? 1 : 0);
foreach ($params['data'] as $key => $val) {
    $params['url'] .= '&' . $key . '=' . $val;
}

if ($params['multiple']) {//多个附件
    $files = is_array($files) ? $files : explode(',', $files);
} else {// 单个附件
    $files = [$files];
}
?>
<div class='layui-upload'>
    <?php if (in_array('upload', $permission)) : ?><!--如果有上传权限，显示上传按钮-->
        <?php if ($isIE8) : ?>
            <!--如果是IE8浏览器，用layui原生的上传插件-->
            <button type='button' class='layui-btn <?= $button['class'] ?>' id="<?= $button['id'] ?>" style="padding: 0 10px">
                <i class='fa fa-<?= $button['icon'] ?>'></i>&nbsp;<?= $button['title'] ?>
            </button>
        <?php else: ?>
            <!--非IE8浏览器，用jquery.form.js的ajax提交处理（ie8不支持）-->
            <button type='button' class='layui-btn <?= $button['class'] ?>' style="padding: 0 10px">
                <i class='fa fa-<?= $button['icon'] ?>'></i>&nbsp;<?= $button['title'] ?>
            </button>
            <?php if ($params['multiple']) : ?><!--多个上传-->
                <input type="file" id="<?= $button['id'] ?>" name="<?= $params['field'] ?>[]" value="" multiple>
            <?php else: ?><!--单个上传-->
                <input type="file" id="<?= $button['id'] ?>" name="<?= $params['field'] ?>" value="">
            <?php endif; ?>
        <?php endif; ?>
    <?php elseif (count($files) > 0) : ?><!--没有上传权限，显示附件个数-->
        <h3><i class="fa fa-paperclip"></i> <span>附件</span> (<?= count($files) ?>个)</h3>'
    <?php endif; ?>
    <input type="hidden" name="<?= $inputName ?>" value=""/>
    <!--已上传的附件展示-->
    <ul id="<?= $button['id'] ?>-list" class='layui-upload-list
	    <?= $show_type && in_array('download', $permission) ? 'layer-photos-list' : '';//show_type=1表示用图片相册展示 ?>'
    >
        <?php
        if (!empty($files)) {
            $arr = [];// 附件信息列表
            if ($flag == 0) {// $files为附件ID数组，从附件表中取出附件信息
                $list = Fileinfo::find()->asArray()->where(['is_del' => 0, 'file_id' => $files])->all();
                foreach ($list as $file) {
                    $arr[] = [
                        'src' => $file['src'], //文件路径
                        'name' => $file['name'], //文件名
                        'type' => $file['file_type'], //文件类型
                        'size' => $file['size'], //文件大小
                    ];
                }
            } else {// $files为附件路径数组
                foreach ($files as $file) {
                    if (!empty($file)) {
                        $arr[] = [
                            'src' => $file, //文件路径
                            'name' => basename($file), //文件名
                            'type' => Tool::getFileType(substr($file, strrpos($file, '.') + 1)), //文件类型
                            'size' => file_exists(Yii::getAlias('@webroot') . @iconv('utf-8', 'gb2312//IGNORE', $file))
                                ? Tool::bytes_format(filesize(Yii::getAlias('@webroot') . @iconv('utf-8', 'gb2312//IGNORE', $file))) : '0b', //文件大小
                        ];
                    }
                }
            }

            if ($show_type == 0) {//附件图标展示
                foreach ($arr as $key => $val) {
                    // 下载链接
                    $download = in_array('download', $permission) ? Html::a('下载', $val['src'], ['class' => 'download-btn', 'download' => $val['name']]) : '';
                    // 查看链接
                    $view = '';
                    if (in_array('download', $permission)) {//有下载权限才允许查看
                        // 在线预览word、excel、ppt
                        if (in_array($val['type'], ['word', 'excel', 'ppt']) && in_array('view', $wordOperate)) {
                            $view = Html::tag('a', '查看', [
                                'class' => 'view-btn',
                                'data-filename' => $val['name'],
                                'data-src' => $val['src'],
                                'data-edittype' => in_array('edit', $wordOperate) ? 2 : 0,
                                'data-writing' => in_array('writing', $wordOperate) ? 1 : 0,
                                'data-template' => in_array('useTemplate', $wordOperate) ? 1 : 0,
                                'data-print' => in_array('print', $wordOperate) ? 1 : 0,
                            ]);
                        } elseif ($val['type'] == 'pdf') {// 在线预览pdf
                            $view = Html::a('查看', (!$isIE8 ? '/pdf/viewer.html?file=' : '') . $val['src'], ['target' => '_blank', 'class' => 'text-blue']);
                        }
                    }
                    // 删除链接
                    $del = in_array('delete', $permission) ? Html::tag('a', '删除', ['class' => 'del-btn', 'onclick' => 'javascript:$(this).parent().parent().remove()']) : '';

                    echo Html::tag('li', Html::hiddenInput($params['multiple'] ? $inputName . '[a' . $key . ']' : $inputName, $flag ? $val['src'] : json_encode($val)) .
                        Html::img('/static/images/filetype/' . $val['type'] . '_lt.png') .
                        Html::tag('div',
                            Html::tag('p', $val['name'] . Html::tag('span', ' （' . $val['size'] . '）'), ['class' => 'file-name']) .
                            $download . $view . $del,
                            ['class' => 'file-info']),
                        ['class' => 'file-item']);
                }
            } else {// 图片展示
                foreach ($arr as $n => $val) {
                    // 多选则给inputName后面加上[]
                    echo Html::tag('li', Html::hiddenInput($params['multiple'] ? $inputName . '[a' . $n . ']' : $inputName, $flag ? $val['src'] : json_encode($val)) .
                        Html::img($val['type'] == 'image' ? $val['src'] : '/static/images/filetype/' . $val['type'] . '.png', ['id' => 'layer-photo-' . $n]) .
                        ($params['multiple'] ? Html::tag('span', $val['name'], ['class' => 'filename', 'title' => $val['name']]) : '') .
                        (in_array('delete', $permission) ?
                            Html::tag('i', '', ['class' => 'fa fa-times-circle', 'onclick' => 'javascript:$(this).parent().remove()']) : ''),
                        ['class' => 'image-item']);
                }
            }
        }
        ?>
    </ul>
</div>
<script>
    var fileIndex = 0; // 初始化name数组中的索引，因为ajax处理时必须要处理不同的name
    <?php if ($isIE8) : ?>
    // IE8浏览器用layui原生上传组件，因为IE8不支持jquery.form.js插件
    layui.use('upload', function () {
        var $ = layui.jquery, upload = layui.upload;

        upload.render({
            elem: '#<?= $button['id'] ?>',
            url: '<?= $params['url']?>',
            accept: '<?= $params['accept']?>', //允许上传的文件类型
            exts: '<?= $params['exts']?>',
            field: '<?= $params['field']?>',

            before: function (obj) {
                layerObj.msg('<span id="layer-loading"></span><span id="layer-msg">上传中……</span>', {offset: '150px'});
            },
            done: function (res) {
                layerObj.closeAll();
                if (res.code != 0) {
                    layerObj.msg("上传失败：" + res.msg, {icon: 2, anim: 6, offset: '150px'});
                } else {
                    <?php if ($params['done'] == null) : ?>
                    $('input[name="<?= $inputName ?>"]').remove();
                    for (var i in res.data) {
                        fileIndex++;
                        var file = res.data[i];
                        var value = <?= $flag ?> ? file.src : JSON.stringify(file);

                        if (typeof value == 'undefined') {
                            return false;
                        }
                        var inputName = <?= $params['multiple'] ? 'true' : 'false' ?> ? '<?= $inputName ?>' + '[' + fileIndex + ']' : '<?= $inputName ?>';
                        <?php if ($show_type == 0) : ?>
                        $('#<?= $button['id'] ?>-list').<?=$params['multiple'] ? 'append' : 'html'?>(
                            '<li class="file-item">' +
                            '<input type="hidden" name="' + inputName + '" value=\'' + value + '\'/>' +
                            '<img src="/static/images/filetype/' + file.type + '_lt.png' + '">' +
                            '<div class="file-info">' +
                            '<p class="file-name">' + file.name + '<span>（' + file.size + '）</span></p>' +
                            '<a class="download-btn" href="' + file.src + '" download="' + file.name + '">下载</a>' +
                            '<a class="del-btn" onclick="javascript:$(this).parent().parent().remove()">删除</a>' +
                            '</div>' +
                            '</li>'
                        );
                        <?php else : ?>
                        $('#<?= $button['id'] ?>-list').<?=$params['multiple'] ? 'append' : 'html'?>(
                            '<li class="image-item">' +
                            '<input type="hidden" name="' + inputName + '" value=\'' + value + '\'/>' +
                            '<img src="' + file.src + '">' +
                            <?php if ($params['multiple']):?>
                            '<span class="filename" title="' + file.name + '">' + file.name + '</span>' +
                            <?php endif; ?>
                            '<?=in_array('delete', $permission) ? '<i class="fa fa-times-circle" onclick="javascript:$(this).parent().remove()"></i></li>' : ''?>'
                        );
                        <?php endif; ?>
                    }
                    <?php else : ?>
                    <?= $params['done'] ?>
                    <?php endif; ?>
                }
            },
            error: function () {
                layerObj.msg('error', {offset: '150px'});
                layerObj.closeAll();
            }
        });
    })
    <?php else: ?>
    //非IE8用jquery.form.js上传
    $('#<?= $button['id'] ?>').wrap('<form class="myFileUpload" enctype="multipart/form-data"></form>');
    $('body').off('change', '#<?= $button['id'] ?>').on('change', '#<?= $button['id'] ?>', function () {
        var files = $(this).prop('files');
        var exts = '<?= $params['exts']; ?>';
        var accept = '<?= $params['accept']; ?>';

        if (files.length == 0) {
            return;
        }

        for (var i in files) {
            if (typeof files[i] == 'object') {
                var name = files[i].name;
                if (!checkExt(name, exts, accept)) {
                    layerObj.msg("选择的文件中包含不支持的格式", {icon: 2, anim: 6, offset: '150px'});
                    return;
                }
            }
        }

        $(this).parent().ajaxSubmit({
            dataType: 'json',
            type: 'post',
            url: '<?= $params['url']?>',
            beforeSend: function () {
                var percentVal = '0%';
                layerObj.msg('<span id="layer-loading"></span><span id="layer-msg">上传中…… ' + percentVal + '</span>', {
                    offset: '150px',
                    time: 0
                });
            },
            uploadProgress: function (event, position, total, percentComplete) {
                var percentVal = percentComplete + '%';
                $('#layer-msg', window.parent.document).html('上传中…… ' + percentVal);
            },
            success: function (res) {
                if (res.code != 0) {
                    layerObj.msg("上传失败：" + res.msg, {icon: 2, anim: 6, offset: '150px'});
                } else {
                    <?php if ($params['done'] == null) : ?>
                    $('input[name="<?= $inputName ?>"]').remove();
                    for (var i in res.data) {
                        fileIndex++;
                        var file = res.data[i];
                        var value = <?= $flag ?> ? file.src : JSON.stringify(file);

                        var inputName = <?= $params['multiple'] ? 'true' : 'false' ?> ? '<?= $inputName ?>' + '[' + fileIndex + ']' : '<?= $inputName ?>';
                        <?php if ($show_type == 0) : ?>
                        $('#<?= $button['id'] ?>-list').<?=$params['multiple'] ? 'append' : 'html'?>(
                            '<li class="file-item">' +
                            '<input type="hidden" name="' + inputName + '" value=\'' + value + '\'/>' +
                            '<img src="/static/images/filetype/' + file.type + '_lt.png' + '">' +
                            '<div class="file-info">' +
                            '<p class="file-name">' + file.name + '<span>（' + file.size + '）</span></p>' +
                            '<a class="download-btn" href="' + file.src + '" download="' + file.name + '">下载</a>' +
                            '<a class="del-btn" onclick="javascript:$(this).parent().parent().remove()">删除</a>' +
                            '</div>' +
                            '</li>'
                        );
                        <?php else : ?>
                        $('#<?= $button['id'] ?>-list').<?=$params['multiple'] ? 'append' : 'html'?>(
                            '<li class="image-item">' +
                            '<input type="hidden" name="' + inputName + '" value=\'' + value + '\'/>' +
                            '<img src="' + file.src + '">' +
                            <?php if ($params['multiple']):?>
                            '<span class="filename" title="' + file.name + '">' + file.name + '</span>' +
                            <?php endif; ?>
                            '<?=in_array('delete', $permission) ? '<i class="fa fa-times-circle" onclick="javascript:$(this).parent().remove()"></i></li>' : ''?>'
                        );
                        <?php endif; ?>
                        layerObj.closeAll();
                    }
                    <?php else : ?>
                    <?= $params['done'] ?>
                    <?php endif; ?>
                }
            },
            error: function (xhr) {
                //console.log('error:' + xhr);
                layerObj.msg("上传失败", {icon: 2, anim: 6, offset: '150px'});
            }
        });
    });
    <?php endif; ?>
</script>