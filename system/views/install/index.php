<?php

/** @var \yii\web\View $this */
/** @var array $data */

use yii\helpers\Html;

\system\assets\IconFontAsset::register($this);
\system\assets\MainAsset::register($this);

$this->title = '安装引导';
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>
    <style>
        body, html {
            height: 100%;
            background-color: #f5f5f5;
        }

        .main {
            width: 960px;
            margin: 30px auto;
            background-color: #fff;
        }

        .main-top {
            width: 100%;
            height: 180px;
            background: url("/static/images/banner.png") no-repeat;
            background-size: 100% 100%
        }

        .main-content {
            padding: 40px 80px;
        }

        .main-content .layui-elem-field {
            padding: 20px 0;
        }

        #upload-logo .layui-upload .layui-btn {
            float: left;
        }

        #upload-logo .layui-upload-list {
            float: left;
            margin-left: 20px;
        }

        .main-content i.icon-excel {
            display: inline-block;
            width: 30px;
            height: 32px;
            font-size: 32px;
            color: #1aa094;
            vertical-align: middle
        }

        .main-content .custom-quote {
            padding: 0 20px 10px;
            margin: 0;
            overflow: hidden;
        }

        #progress .msg {
            line-height: 32px;
            padding-left: 5px;
        }

        #layer-loading {
            display: block;
            float: left;
            width: 32px;
            height: 32px;
            background: url('/static/lib/layer/skin/default/loading-2.gif') no-repeat;
        }
    </style>
    <div class="main">
        <div class="main-top"></div>
        <div class="main-content">
            <form class="layui-form custom-form" id="install-form" method="post">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>"
                       value="<?= Yii::$app->request->csrfToken ?>">
                <!--基本信息-->
                <div id="base-content" style="display: block;">
                    <!--管理员账号-->
                    <fieldset class="layui-elem-field">
                        <legend><i class="fa fa-star-o"></i>&nbsp;&nbsp;管理员账号</legend>
                        <div class="layui-col-xs11">
                            <div class="layui-form-item">
                                <label class="layui-form-label">
                                    用户名<span class="text-red required-tip">*</span>
                                </label>
                                <div class="layui-input-block">
                                    <input type="text" class="layui-input" name="username" value="admin"
                                           lay-verfity="required" placeholder="请输入用户名" required/>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">
                                    姓名<span class="text-red required-tip">*</span>
                                </label>
                                <div class="layui-input-block">
                                    <input type="text" class="layui-input" name="realname" value="超级管理员"
                                           lay-verfity="required" placeholder="请输入姓名" required/>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">
                                    密码<span class="text-red required-tip">*</span>
                                </label>
                                <div class="layui-input-block">
                                    <input type="password" class="layui-input" name="password" value=""
                                           lay-verfity="required" placeholder="请输入用户名密码" required/>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <!--企业信息-->
                    <fieldset class="layui-elem-field">
                        <legend><i class="fa fa-star-o"></i>&nbsp;&nbsp;企业信息</legend>
                        <div class="layui-col-xs11">
                            <div class="layui-form-item">
                                <label class="layui-form-label">
                                    企业名称<span class="text-red required-tip">*</span>
                                </label>
                                <div class="layui-input-block">
                                    <input type="text" class="layui-input" name="company_name" value="驾校考试"
                                           lay-verfity="required" placeholder="请输入企业的单位名称" required/>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">
                                    系统名称<span class="text-red required-tip">*</span>
                                </label>
                                <div class="layui-input-block">
                                    <input type="text" class="layui-input" name="system_name" value="驾校后台管理"
                                           lay-verfity="required" placeholder="请输入自定义的系统名称" required/>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">
                                    管理平台LOGO<span class="text-red required-tip">*</span>
                                </label>
                                <div class="layui-input-block" id="upload-logo">
                                    <?= \system\modules\main\widgets\FileUploadWidget::widget([
                                        'inputName' => 'system_logo',
                                        'files' => Yii::$app->request->hostInfo . '/static/images/logo1.png',
                                        'flag' => 1,
                                        'permission' => ['upload', 'download']
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <!--数据库信息-->
                    <fieldset class="layui-elem-field">
                        <legend><i class="fa fa-star-o"></i>&nbsp;&nbsp;数据库信息</legend>
                        <div class="layui-col-xs11">
                            <div class="layui-form-item">
                                <label class="layui-form-label">
                                    数据库用户名<span class="text-red required-tip">*</span>
                                </label>
                                <div class="layui-input-block">
                                    <input type="text" class="layui-input" name="db_user" value=""
                                           lay-verfity="required" placeholder="请输入数据库用户名" required/>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">
                                    数据库密码<span class="text-red required-tip">*</span>
                                </label>
                                <div class="layui-input-block">
                                    <input type="text" class="layui-input" name="db_password" value=""
                                           lay-verfity="required" placeholder="请输入数据库密码" required/>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">
                                    数据库名<span class="text-red required-tip">*</span>
                                </label>
                                <div class="layui-input-block">
                                    <input type="text" class="layui-input" name="db_name" value=""
                                           lay-verfity="required" placeholder="请输入数据库名" required/>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!--其他配置-->
                    <fieldset class="layui-elem-field">
                        <legend><i class="fa fa-star-o"></i>&nbsp;&nbsp;其他配置</legend>
                        <div class="layui-col-xs11">
                            <div class="layui-form-item">
                                <label class="layui-form-label">
                                    前端服务器地址<span class="text-red required-tip">*</span>
                                </label>
                                <div class="layui-input-block">
                                    <input type="text" class="layui-input" name="web_server" value="*"
                                           lay-verfity="required" placeholder="请输入前端服务器地址" required/>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">
                                    文件服务器地址<span class="text-red required-tip">*</span>
                                </label>
                                <div class="layui-input-block">
                                    <input type="text" class="layui-input" name="file_server" value=""
                                           lay-verfity="required" placeholder="请输入文件服务器地址" required/>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="layui-form-item" style="margin-top: 20px;">
                        <a type="button" class="layui-btn width-120 setup-btn" style="float: right">开始安装</a>
                    </div>
                </div>
                <!--进度条-->
                <div id="progress" style="display: none;">
                    <div class="layui-progress layui-progress-big" lay-filter="progress">
                        <div class="layui-progress-bar layui-bg-blue" lay-percent="0%"></div>
                    </div>
                    <p class="msg">开始安装</p>
                    <div class="layui-form-item" style="margin-top: 10px;">
                        <button type="button" class="layui-btn layui-btn-disabled width-120" style="float: right;">正在安装
                            . . .
                        </button>
                        <a href="/admin.php" class="layui-btn layui-btn-warm width-120" style="display: none">进入系统</a>
                    </div>
                </div>
                <!--导入用户-->
                <div id="import-user" style="display: none;">
                    <!--导入用户信息-->
                    <fieldset class="layui-elem-field">
                        <legend><i class="fa fa-star-o"></i>&nbsp;&nbsp;导入用户信息</legend>
                        <div class="layui-col-xs11">
                            <div class="layui-form-item">
                                <label class="layui-form-label">点击下载模板</label>
                                <div class="layui-input-block">
                                    <a href="/static/files/users-import.xls" download="用户信息导入模板.xls"
                                       style="line-height: 38px; color: #0c80fe;">
                                        <i class="iconfont icon-excel"></i>用户信息导入模板.xls
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">上传数据</label>
                            <div class="layui-input-block">
                                <?= \system\modules\main\widgets\FileUploadWidget::widget([
                                    'inputName' => 'userinfo',
                                    'resetName' => true,
                                    'item' => [
                                        'title' => '上传excel表格',
                                        'accept' => 'file',
                                        'exts' => 'xls',
                                        'btnId' => 'upload-excel'
                                    ],
                                    'flag' => 1
                                ]) ?>
                            </div>
                        </div>
                    </fieldset>
                    <div class="layui-form-item submit-box" style="margin-top: 20px;">
                        <button type="button" class="layui-btn width-120" id="import-btn" style="float: left">开始导入
                        </button>
                        <a href="/admin.php" class="layui-btn layui-btn-warm width-120" style="display: none">进入系统</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>

        var $ = jQuery = layui.jquery,
            layer = layui.layer, //获取当前窗口的layer对象
            element = layui.element,
            form = layui.form;

        var count = 4;
        var complete = 0;
        // 开始安装
        $('#base-content').on('click', '.setup-btn', function () {
            $('#progress').show().siblings().hide();
            var data = $('#install-form').serializeArray();

            var stepArr = [];
            stepArr.push({url: 'setup?action=db-setup', data: data, msg: '正在创建数据库 . . .'});

            $.ajax('get-modules', {
                type: 'get',
                dataType: 'json',
                success: function (res) {
                    if (res.code == 0) {
                        // 安装模块
                        var modules = res.data;
                        $.each(modules, function (index, item) {
                            stepArr.push({
                                url: 'setup?action=module-install',
                                data: $.extend({module_id: index}, param),
                                msg: '正在安装 ' + item + '模块 请稍等 . . .'
                            });
                            count++;
                        });

                        stepArr.push({url: 'setup?action=group', data: data, msg: '正在创建部门 . . .'});
                        stepArr.push({url: 'setup?action=user', data: data, msg: '正在创建管理员 . . .'});
                        stepArr.push({url: 'setup?action=config', data: data, msg: '正在添加配置信息 . . .'});

                        setTimeout(function () {
                            setup(stepArr, 0)
                        }, 500);
                    }
                }
            });

        });

        function setup(stepArr, index) {
            if (index < stepArr.length) {
                var msg = stepArr[index]['msg'];
                var url = stepArr[index]['url'];
                var data = stepArr[index]['data'];

                $('#progress .msg').html(msg);
                $.ajax(url, {
                    type: 'post',
                    data: data,
                    success: function (res) {
                        complete += 1;
                        element.progress('progress', parseInt(complete * 100 / count) + '%');
                        setup(stepArr, index + 1);
                    },
                })
            } else {
                $('#progress .msg').html('安装完成');
                $('#progress button').removeClass('layui-btn-disabled').html('导入用户');
                $('#progress a').show();

                $('#progress').on('click', 'button', function () {
                    $('#import-user').show().siblings().hide();
                })
            }
        }

        $('#import-btn').on('click', function () {
            var excel = $('input[name="userinfo"]').val();
            if (excel == '') {
                layer.msg('请上传excel表格数据！', {icon: 2, offset: '250px', anim: 6});
                return false;
            }

            layer.msg('<span id="layer-loading"></span><span id="layer-msg">正在导入，请等待……</span>', {
                offset: '250px',
                time: 0,
                shade: 0.1
            });
            $.ajax('import-users', {
                type: 'post',
                dataType: 'json',
                url: 'setup',
                data: $.extend({excel: excel}, param),
                success: function (res) {
                    layer.closeAll();
                    if (res.code == 0) {
                        layer.closeAll();
                        layer.msg(res.msg, {offset: '250px', time: 1000}, function () {
                            window.location.href = '/admin.php';
                        });
                    } else {
                        layer.msg(res.msg, {icon: 2, offset: '250px', anim: 6});
                    }
                }
            })
        })
    </script>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>