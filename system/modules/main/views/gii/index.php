<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2018-3-21
 * Time: 11:45
 */

/** @var \yii\web\View $this */
/** @var array $tables */
/** @var array $modules */

use yii\helpers\Html;
use yii\helpers\Url;

\system\assets\Select2Asset::register($this);
\system\modules\main\assets\GiiAsset::register($this);

$this->title = "代码生成器";
?>
<style>
    .layui-elem-field {padding: 15px 0;}
</style>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title clearfix">
        <li class="layui-this">代码生成器</li>
    </ul>
</div>

<div class="layui-col-lg10 layui-col-lg-offset1">
    <form class="layui-form custom-form" method="post">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
        <!--基本设置-->
        <fieldset class="layui-elem-field" id="base-content" style="display: block;">
            <legend><i class="fa fa-star-o"></i>&nbsp;&nbsp;基本设置</legend>
            <div class="layui-col-xs11">
                <div class="layui-form-item">
                    <label class="layui-form-label">数据表</label>
                    <div class="layui-input-block">
                        <select class="select2" id="table_name" name="table_name" lay-ignore="">
                            <option value="">请选择</option>
                            <?php foreach ($tables as $table) : ?>
                                <option value="<?= $table ?>"><?= $table ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">模型类名</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" id="model_class" name="model_class"
                               value="" required lay-verify="required"/>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">所属模块</label>
                    <div class="layui-input-block ">
                        <?= Html::dropDownList('module', '', $modules, [
                            'id' => 'module_id',
                            'class' => 'select2',
                            'prompt' => '请选择',
                            'lay-ignore' => true,
                            'lay-verify' => "required",
                        ]) ?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">字段标签</label>
                    <div class="layui-input-block" id="table_label"></div>
                </div>
                <div class="layui-form-item submit-box" style="display: none">
                    <div class="layui-input-block">
                        <button type="button" class="layui-btn btn-next">确定</button>
                        <a class="layui-btn layui-btn-primary" href="javascript:history.go(-1)">返回</a>
                    </div>
                </div>
            </div>
        </fieldset>
        <!--详细设置-->
        <fieldset class="layui-elem-field" id="detail-content" style="display: none;">
            <legend><i class="fa fa-star-o"></i>&nbsp;&nbsp;详细设置</legend>
            <!--操作日志设置-->
            <fieldset class="layui-elem-field" style="margin: 10px 50px;">
                <legend>操作日志设置</legend>
                <div class="layui-form-item">
                    <label class="layui-form-label">是否记录操作日志</label>
                    <div class="layui-input-block">
                        <input type="radio" lay-filter="log_flag" name="log_flag" value="0" title="否" checked/>
                        <input type="radio" lay-filter="log_flag" name="log_flag" value="1" title="是"/>
                    </div>
                </div>
                <div class="layui-col-xs11 log_flag_box" style="display: none;">
                    <input type="hidden" id="primaryKey" name="primaryKey" value=""/>
                    <div class="layui-col-lg5">
                        <div class="layui-form-item">
                            <label class="layui-form-label">模型名称</label>
                            <div class="layui-input-block">
                                <input type="text" class="layui-input input-item width-180" id="model_name"
                                       name="model_name" value=""/>
                                <div class="help-block">
                                    model模型的中文名称，用于在日志中显示
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-col-lg7">
                        <div class="layui-form-item">
                            <label class="layui-form-label">日志目标字段</label>
                            <div class="layui-input-block width-180">
                                <select class="item" id="target_name" name="target_name"></select>
                            </div>
                            <div class="help-block" style="margin-left: 150px;">
                                日志记录的对象名称字段，如用户姓名，对象名称，文章标题等字段
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">记录的字段</label>
                        <div class="layui-input-block item" id="normal_field"></div>
                        <div class="help-block" style="margin-left: 150px; float: none;">
                            勾选的字段内容变更时会记录到操作日志，不勾选则记录所有字段
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">忽略的字段</label>
                        <div class="layui-input-block item" id="except_field"></div>
                        <div class="help-block" style="margin-left: 150px; float: none;">
                            勾选的字段内容变更时不会记录到操作日志
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="separate-10"></div>

            <!--字段规则设置-->
            <fieldset class="layui-elem-field" style="margin: 10px 50px;">
                <legend>字段规则设置</legend>
                <div class="layui-col-xs11">
                    <div class="layui-form-item">
                        <label class="layui-form-label">必填(required)</label>
                        <div class="layui-input-block">
                            <select class="select2-multiple item" id="required_rule" name="required_rule[]"
                                    lay-ignore=""
                                    multiple></select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">整型(integer)</label>
                        <div class="layui-input-block">
                            <select class="select2-multiple item" id="integer_rule" name="integer_rule[]" lay-ignore=""
                                    multiple></select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">字符串(string)</label>
                        <div class="layui-input-block">
                            <select class="select2-multiple item" id="string_rule" name="string_rule[]" lay-ignore=""
                                    multiple></select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">安全(safe)</label>
                        <div class="layui-input-block">
                            <select class="select2-multiple item" id="safe_rule" name="safe_rule[]" lay-ignore=""
                                    multiple></select>
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="separate-10"></div>

            <!--字段属性设置-->
            <fieldset class="layui-elem-field attributes-list" style="margin: 10px 50px;">
                <legend>字段属性设置</legend>
                <div class="layui-form-item">
                    <label class="layui-form-label">选择字段</label>
                    <div class="layui-input-block item" id="attribute_fields"></div>
                    <div class="help-block" style="margin-left: 150px; float: none;">
                        选择带有属性的字段（字段值作为属性id，代表其他内容的字段。如：is_show, status, group_id等）
                    </div>
                </div>
                <div class="layui-col-xs11" id="attribute_options"></div>
            </fieldset>

            <!--控制器设置-->
            <fieldset class="layui-elem-field" style="margin: 10px 50px;">
                <legend>控制器设置</legend>
                <div class="layui-form-item">
                    <label class="layui-form-label">是否生成控制器</label>
                    <div class="layui-input-block">
                        <input type="radio" lay-filter="controller_flag" name="controller_flag" value="0" title="否" checked/>
                        <input type="radio" lay-filter="controller_flag" name="controller_flag" value="1" title="是"/>
                    </div>
                </div>
                <div class="layui-col-xs11 controller_flag_box" style="display: none;">
                    <div class="layui-form-item">
                        <label class="layui-form-label">控制器名称</label>
                        <div class="layui-input-block">
                            <input type="text" class="layui-input" id="controller_name"
                                   name="controller_name" value="default"/>
                            <div class="help-block">
                                名称为default会自动生成DefaultController控制器
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">控制器方法</label>
                        <div class="layui-input-block">
                            <input type="checkbox" name="controller_action[]" value="index" title="列表" checked/>
                            <input type="checkbox" name="controller_action[]" value="form" title="添加" checked/>
                            <input type="checkbox" name="controller_action[]" value="form" title="编辑" checked/>
                            <input type="checkbox" name="controller_action[]" value="del" title="删除" checked/>
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="layui-form-item submit-box">
                <div class="layui-input-block" style="margin-left: 120px;">
                    <button type="button" class="layui-btn  btn-preview">预览</button>
                    <button type="button" class="layui-btn layui-btn-primary btn-back">返回</button>
                </div>
            </div>
        </fieldset>
        <!--代码预览-->
        <fieldset class="layui-elem-field" id="preview-content" style="display: none;">
            <legend><i class="fa fa-star-o"></i>&nbsp;&nbsp;代码预览生成</legend>
            <div class="layui-col-xs10 layui-col-xs-offset1">
                <div class="layui-form-item">
                    <table class="layui-table">
                        <thead>
                        <tr>
                            <th width="5%"><input type="checkbox" lay-skin="primary" lay-filter="checkAll" checked/></th>
                            <th width="10%">类型</th>
                            <th width="10%">文件名</th>
                            <th width="70%" style="text-align: left;">类名</th>
                            <th width="5%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <div class="layui-form-item submit-box">
                    <button type="button" class="layui-btn btn-generate">提交</button>
                    <button type="button" class="layui-btn layui-btn-primary btn-back">返回</button>
                </div>
            </div>
        </fieldset>

        <div id="previewCode" style="display: none"></div>

    </form>
</div>