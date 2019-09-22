<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/17
 * Time: 19:33
 */

namespace system\widgets;

use system\modules\main\widgets\ExtendedFieldWidget;
use yii\bootstrap\Widget;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

class FormViewWidget extends Widget
{
    //数据对象
    public $model = null;
    //表单提交地址
    public $action = '';
    //表单属性
    public $formOptions = [];
    //字段属性
    public $fieldOptions = [];
    //样式属性
    public $horizontalCssClasses = [];
    //字段列表
    public $fields = [];
    //返回链接
    public $backUrl = '';
    //自定义处理按钮
    public $customBtn = false;
    //表单验证JS
    private $jsVerify = '';

    /**
     * Renders the widget.
     */
    public function run()
    {
        //外框div开始
        echo Html::beginTag('div', ['class' => 'layui-col-xs11']);

        $fields = $this->fields;

        //form内div的class
        $formClass = ArrayHelper::remove($this->formOptions, 'class');

        $form = $this->renderForm();
        $content = $this->renderContent($form, $fields);
        $button = $this->renderButton($formClass);

        $extendFields = ExtendedFieldWidget::widget([
            'model' => $this->model,
        ]);
        if ($formClass) {
            echo Html::tag('div', $content, ['class' => $formClass]);
        } elseif (empty($extendFields)) {
            echo Html::tag('div', $content, ['class' => 'layui-col-lg9']);
        } else {
            echo Html::tag('div', $content, ['class' => 'layui-col-lg7']);
            echo Html::tag('div', ExtendedFieldWidget::widget([
                'model' => $this->model,
            ]), ['class' => 'layui-col-lg5']);
        }
        echo $button;
        ActiveForm::end();
        //外框div结束
        echo Html::endTag('div');
        $this->jsVerify = "
<script>
    form.on('submit(submit)', function(data) {
        $this->jsVerify
    })
</script>";
        echo $this->jsVerify;
    }

    /**
     * 获取ActiveForm对象实例
     * @return \yii\bootstrap\ActiveForm
     */
    private function renderForm()
    {
        $url = empty($this->action) ? '' : $this->action;
        //字段框体class
        $fieldClass = ArrayHelper::remove($this->fieldOptions, 'class', '');

        $labelClass = ArrayHelper::getValue($this->horizontalCssClasses, 'label', '');
        $wrapperClass = ArrayHelper::getValue($this->horizontalCssClasses, 'wrapper', '');
        $hintClass = ArrayHelper::getValue($this->horizontalCssClasses, 'hint', '');

        $form = ActiveForm::begin([
            'action' => $url,
            'options' => ArrayHelper::merge(['class' => "layui-form custom-form layui-col-space30"], $this->formOptions),
            'layout' => 'horizontal',
            'validateOnSubmit' => false,
            'fieldConfig' => [
                'options' => ArrayHelper::merge(['class' => 'layui-form-item ' . $fieldClass], $this->fieldOptions),
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
                'horizontalCssClasses' => [
                    'label' => 'layui-form-label ' . $labelClass,
                    'wrapper' => 'layui-input-block ' . $wrapperClass,
                    'hint' => $hintClass,
                ],
            ],
        ]);

        return $form;
    }

    /**
     * @param $form \yii\bootstrap\ActiveForm
     * @param $fields array
     * @return string
     */
    private function renderContent($form, $fields)
    {
        $list = [];
        $model = $this->model;

        foreach ($fields as $key => $val) {
            if (is_string($key)) {
                if (is_string($val)) {
                    $type = $val;
                    $val = [];
                } else {
                    $type = ArrayHelper::getValue($val, 'type', 'input');
                }
                $fieldOptions = ArrayHelper::getValue($val, 'fieldOptions', []);
                $model->$key = ArrayHelper::getValue($val, 'value', $model->$key);
                $options = ['name' => $key];
                switch ($type) {
                    case 'input':
                        $model->$key = is_array($model->$key) ? implode(',', $model->$key) : $model->$key;
                    case 'number':
                        $options['placeholder'] = ArrayHelper::getValue($val, 'placeholder', '请输入');
                        $options['class'] = 'layui-input ' . ArrayHelper::getValue($val, 'class');
                        if ($type == 'number') {
                            $options['type'] = 'number';
                        }
                        if (ArrayHelper::getValue($val, 'required', false)) {
//							$options['lay-verify'] = 'required';
                            $options['placeholder'] .= '(必填)';
                        }
                        $verify = ArrayHelper::getValue($val, 'verify', false);
                        if ($verify) {
                            $options['lay-verify'] = isset($options['lay-verify']) ? $options['lay-verify'] . '|' . $verify : $verify;
                        }
                        $options = ArrayHelper::merge(ArrayHelper::getValue($val, 'options', []), $options);
                        $field = $form->field($model, $key, $fieldOptions)->textInput($options);
                        break;
                    case 'hidden':
                        $model->$key = is_array($model->$key) ? implode(',', $model->$key) : $model->$key;
                        $field = $form->field($model, $key, ['template' => "{input}"])
                            ->hiddenInput(ArrayHelper::getValue($val, 'options', $options));
                        break;
                    case 'textarea':
                        $options['placeholder'] = ArrayHelper::getValue($val, 'placeholder', '请输入');
                        $options['class'] = 'layui-textarea ' . ArrayHelper::getValue($val, 'class');
                        if (ArrayHelper::getValue($val, 'required', false)) {
//							$options['required'] = true;
                            $options['placeholder'] .= '(必填)';
                        }
                        $options = ArrayHelper::merge(ArrayHelper::getValue($val, 'options', []), $options);

                        $field = $form->field($model, $key, $fieldOptions)->textarea($options);
                        break;
                    case 'radio':
                        $items = ArrayHelper::getValue($val, 'items', $model::getAttributesList($key));
                        $field = $form->field($model, $key, $fieldOptions)->radioList(empty($items) ? [] : $items, [
                            'name' => $key,
                            'item' => function ($index, $label, $name, $checked, $value) {
                                return \yii\helpers\Html::radio($name, $checked, [
                                    'value' => $value,
                                    'title' => $label,
                                    'lay-filter' => $name
                                ]);
                            },
                        ]);
                        break;
                    case 'checkbox':
                        $items = ArrayHelper::getValue($val, 'items', $model::getAttributesList($key));
                        $skin = ArrayHelper::getValue($val, 'skin', 'primary');
                        $model->$key = is_array($model->$key) ? $model->$key : explode(',', $model->$key);
                        $field = $form->field($model, $key, $fieldOptions)->checkboxList(empty($items) ? [] : $items, [
                            'name' => $key,
                            'item' => function ($index, $label, $name, $checked, $value) use ($skin) {
                                return \yii\helpers\Html::checkbox($name, $checked, [
                                    'value' => $value,
                                    'title' => $label,
                                    'lay-skin' => $skin
                                ]);
                            },
                        ]);
                        break;
                    case 'select':
                        if (ArrayHelper::getValue($val, 'required', false)) {
//							$options['required'] = true;
                            $options['lay-verify'] = 'required';
                        }
                        $options['lay-filter'] = $key;
                        $options = ArrayHelper::merge(ArrayHelper::getValue($val, 'options', []), $options);

                        $items = ArrayHelper::getValue($val, 'items', $model::getAttributesList($key));
                        $options['class'] = ArrayHelper::getValue($val, 'class', count($items) > 10 ? 'select2' : '');
                        if ($options['class'] == 'select2') {
                            $options['lay-ignore'] = true;
                        }
                        $field = $form->field($model, $key, $fieldOptions)->dropDownList(empty($items) ? [] : $items, $options);
                        break;
                    case 'date':
                    case 'datetime':
                        $options['autocomplete'] = 'off';
                        $options['data-type'] = $type;
                        $options['placeholder'] = ArrayHelper::getValue($val, 'placeholder', '请输入');
                        $options['class'] = 'layui-input date ' . ArrayHelper::getValue($val, 'class');
                        if (ArrayHelper::getValue($val, 'required', false)) {
//                            $options['required'] = true;
                            $options['placeholder'] .= '(必填)';
                        }
                        $options = ArrayHelper::merge(ArrayHelper::getValue($val, 'options', []), $options);
                        if (is_numeric($model->$key)) {
                            $format = $type == 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';
                            $model->$key = date($format, $model->$key);
                        }
                        $field = $form->field($model, $key, $fieldOptions)->textInput($options);
                        break;
                    case 'date-range':
                        $options['autocomplete'] = 'off';
                        $options['placeholder'] = ArrayHelper::getValue($val, 'placeholder', '请输入');
                        $options['class'] = 'layui-input date ' . ArrayHelper::getValue($val, 'class');
                        $options['date-range'] = 'true';
                        if (ArrayHelper::getValue($val, 'required', false)) {
//							$options['required'] = true;
                            $options['placeholder'] .= '(必填)';
                        }
                        $options = ArrayHelper::merge(ArrayHelper::getValue($val, 'options', []), $options);

                        $field = $form->field($model, $key, $fieldOptions)->textInput($options);
                        break;
                    case 'widget':
                        $class = ArrayHelper::getValue($val, 'class', 'xj\ueditor\Ueditor');
                        if ($class == 'xj\ueditor\Ueditor') {
                            $toolbars = ArrayHelper::getValue($val, 'toolbars');
                            $config = [
                                'style' => ArrayHelper::getValue($val, 'style', 'min-height:160px;'),
                                'name' => $key,
                                'jsOptions' => ArrayHelper::merge([
                                    'serverUrl' => \yii\helpers\Url::to(['editor-upload']),
                                    'filterTxtRules' => false,
                                    'allowDivTransToP' => false,
                                    'iframeCssUrl' => ArrayHelper::getValue($val, 'iframeCssUrl', '/static/css/global.css'),
                                    'fontfamily' => [
                                        ['label' => '', 'name' => 'songti', 'val' => '宋体,SimSun'],
                                        ['label' => '', 'name' => 'kaiti', 'val' => '楷体,楷体_GB2312, SimKai'],
                                        ['label' => '', 'name' => 'yahei', 'val' => '微软雅黑,Microsoft YaHei'],
                                        ['label' => '', 'name' => 'heiti', 'val' => '黑体, SimHei'],
                                        ['label' => '', 'name' => 'lishu', 'val' => '隶书, SimLi'],
                                        ['label' => '仿宋', 'name' => 'fangsong', 'val' => '仿宋, FangSong,FangSong_GB2312'],
                                        ['label' => '', 'name' => 'andaleMono', 'val' => 'andale mono'],
                                        ['label' => '', 'name' => 'arial', 'val' => 'arial, helvetica,sans-serif'],
                                        ['label' => '', 'name' => 'arialBlack', 'val' => 'arial black,avant garde'],
                                        ['label' => '', 'name' => 'comicSansMs', 'val' => 'comic sans ms'],
                                        ['label' => '', 'name' => 'impact', 'val' => 'impact,chicago'],
                                        ['label' => '', 'name' => 'timesNewRoman', 'val' => 'times new roman']
                                    ]
                                ], empty($toolbars) ? [] : [
                                    //定制菜单
                                    'toolbars' => [$toolbars]
                                    /*[
                                        'source', '|', 'undo', 'redo', '|', 'bold', 'italic', 'underline', '|',
                                        'forecolor', 'backcolor', 'lineheight', '|',
                                        'paragraph', 'fontfamily', 'fontsize', '|',
                                        'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify',
                                        'simpleupload', 'insertimage', 'emotion',
                                        'insertvideo', 'music', '|', 'fullscreen'
                                    ],*/
                                ])
                            ];
                        } else {
                            $config = ArrayHelper::getValue($val, 'config', []);
                        }

                        $field = $form->field($model, $key, $fieldOptions)->widget($class, $config);
                        break;
                    default:
                        $field = '';
                        break;
                }

                $label = isset($val['label']) ? $val['label'] : ArrayHelper::getValue($model->attributeLabels(), $key, '');
                if (ArrayHelper::getValue($val, 'required', false)) {
                    $this->jsVerify .= "
                    if (data.field.$key == '' || data.field.$key == undefined) {
                        $('[name=\"$key\"]').addClass('layui-form-danger').focus();
                        layerObj.msg('" . $label . "不能为空', {icon: 5, anim: 6, offset : '25%'});
                        return false;
                    }";
                    $label .= "<span class='text-red required-tip'>*</span>";
                }
                if (isset($val['tip'])) {
                    $label .= "&nbsp;" . $val['tip'];
                }
                if (!empty($label)) {
                    $field = $field->label($label);
                }

                $hint = ArrayHelper::getValue($val, 'hint', null);
                if (!empty($hint)) {
                    $field = $field->hint($hint);
                }

                $list[] = isset($val['box']) ? Html::tag('div', $field, ['class' => $val['box']]) : $field;
            } else {
                if (is_array($val)) {
                    $html = ArrayHelper::getValue($val, 'html', false);
                    if ($html) {
                        $html = $html instanceof \Closure ? call_user_func($html, $model) : $html;
                        $label = ArrayHelper::getValue($val, 'label');
                        $wrapper = ArrayHelper::getValue($val, 'wrapper', '');
                        $html = $label ? "<div class=\"layui-form-item\">
                            <label class=\"layui-form-label\">$label</label>
                            <div class=\"layui-input-block $wrapper\">$html</div>
                        </div>" : $html;
                        $list[] = isset($val['box']) ? Html::tag('div', $html, ['class' => $val['box']]) : $html;
                    } else {
                        $divBox = ArrayHelper::getValue($val, 'div-box', '');
                        $filter = ArrayHelper::getValue($val, 'filter');
                        $content = $this->renderContent($form, ArrayHelper::getValue($val, 'fields', []));

                        if (is_array($filter)) {
                            $flag = ArrayHelper::remove($filter, 'flag', '==');
                            foreach ($filter as $key => $val) {
                                $type = is_array($fields[$key]) ? $fields[$key]['type'] : $fields[$key];
                                $js = "<script>
                                form.on('$type($key)', function (data) {
                                    if (data.value $flag '$val') {
                                        $('.$divBox').hide();
                                    } else {
                                        $('.$divBox').show();
                                    }
                                });
                                </script>";

                                $hide = $flag == '==' ? $model->$key == $val : $model->$key != $val;
                                $list[] = Html::tag('div', $content, ['class' => $divBox, 'style' => 'display:' . ($hide ? 'none' : 'block')]) . $js;
                            }
                        } else {
                            $list[] = Html::tag('div', $content, ['class' => $divBox, 'style' => 'display:' . ($filter ? 'none' : 'block')]);
                        }
                    }
                } else {
                    $list[] = $form->field($model, $val)->textInput(['placeholder' => '请输入', 'class' => 'layui-input', 'name' => $val]);
                }

            }
        }
        return implode('', $list);
    }

    /**
     * 返回提交按钮
     * @return string
     */
    private function renderButton($class = '')
    {
        return Html::tag('div',
            Html::tag('div',
                $this->customBtn == false ?
                    Html::submitButton('立即提交', [
                        'class' => 'layui-btn layui-submit',
                        'lay-submit' => true,
                        'lay-filter' => "submit",
                    ]) .
                    Html::a('返回',
                        empty($this->backUrl) ? 'javascript:window.history.go(-1);' : Url::to($this->backUrl),
                        ['class' => 'layui-btn layui-btn-primary']
                    ) : implode('', $this->customBtn),
                ['class' => "layui-input-block"]),
            ['class' => "layui-form-item submit-box $class"]
        );
    }
}