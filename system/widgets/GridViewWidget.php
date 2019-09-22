<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/17
 * Time: 19:32
 */

namespace system\widgets;

use system\core\utils\Tool;
use system\modules\main\models\ExtendsField;
use yii\bootstrap\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Html;
use yii\helpers\Url;
use Yii;

class GridViewWidget extends Widget
{
    //表格数据
    public $data = [];
    //模型的类 如：system\modules\setting\module\models\Module
    public $model = null;
    //主键字段
    public $primaryKey = null;
    //查询参数 ['url' => 查询表单提交地址, 'items' => [[],[]……]]查询条件字段控件信息
    public $search = false;
    //查询参数
    public $params = [];
    //解析表格参数
    public $parseData = [];
    //数据信息
    public $columns = [];
    //第一列是否显示序号列
    public $showNumbers = true;
    //是否允许批量删除
    public $batchBtn = false;
    //分页参数
    public $pagination = null;

    public function init()
    {
        parent::init();

        if (isset($this->data['pagination'])) {
            $this->pagination = ArrayHelper::remove($this->data, 'pagination');
        }

        if ($this->primaryKey == null && $this->model != null) {
            $model = $this->model;
            $primaryKeys = $model::primaryKey();
            $this->primaryKey = empty($primaryKeys) ? null : (is_array($primaryKeys) ? $primaryKeys[0] : $primaryKeys);
        }

        if ($this->batchBtn) {
            array_unshift($this->columns, ['type' => 'checkbox']);
        } elseif ($this->showNumbers && (!isset($this->columns[0]) || ArrayHelper::getValue($this->columns[0], 'type') != 'ID')) {
            array_unshift($this->columns, ['type' => 'numbers']);
        }
        $this->params = Yii::$app->request->queryParams;
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        $queryHtml = $this->renderQuery();
        $table = $this->renderTable();
        $page = $this->renderPagination();

        return $queryHtml . Html::tag('div', $table . $page, ['class' => 'custom-table']);
    }

    /**
     * 获取查询表单html代码
     * @return string
     */
    private function renderQuery()
    {
        if ($this->search != false) {
            return Html::tag('form',
                Html::tag('div',
                    $this->getSearchContent(),
                    ['class' => 'layui-row layui-col-space10']
                ),
                [
                    'action' => isset($this->search['url']) ? Url::to($this->search['url']) : '',
                    'class' => 'layui-form custom-form search-form',
                    'method' => 'get',
                ]);
        } else {
            return '';
        }
    }

    /**
     * 获取表格html代码
     * @return string
     */
    private function renderTable()
    {
        $this->transformColumn();
        $thead = $this->getThead();
        $tr = '';
        foreach ($this->data as $one) {
            $tr .= $this->getTbody($one);
        }
        $tbody = Html::tag('tbody', $tr);

        return Html::tag('table', $thead . $tbody, [
            'class' => 'layui-table',
            'lay-filter' => 'parse-table',
            'lay-data' => Json::encode(ArrayHelper::merge(['id' => 'parse-table', 'limit' => 200], $this->parseData)),
        ]);
    }

    /**
     * 获取分页html代码
     * @return string
     */
    private function renderPagination()
    {
        $count = empty($this->pagination) ? 0 : ceil($this->pagination->totalCount / $this->pagination->defaultPageSize);
        return Html::tag('div',
            $this->getBatchBtn() .
            ($count > 1 ? Html::tag('div',
                \yii\widgets\LinkPager::widget([
                    'pagination' => $this->pagination,
                    'nextPageLabel' => '下一页',
                    'prevPageLabel' => '上一页',
                    'firstPageLabel' => '首页',
                    'lastPageLabel' => '末页',
                    'maxButtonCount' => 5,
                ]),
                ['class' => 'page']) : '') .
            $this->getPageGoto($count),
            ['class' => 'table-page clearfix']
        );
    }

    /**
     * 获取查询控件
     * @return string
     */
    private function getSearchContent()
    {
        $html = '';
        if (!empty($this->model)) {
            $model = new $this->model;
            $labels = $model->attributeLabels();
        } else {
            $labels = [];
        }

        // print_r($this->search['items']);
        if ($this->search != false) {//查询参数不为空
            $this->addExtendSearchItems();
            $url = [''];
            foreach (ArrayHelper::getValue($this->search, 'items', []) as $one) {
                $type = ArrayHelper::getValue($one, 'type', 'input');
                $label = ArrayHelper::getValue($one, 'label', ArrayHelper::getValue($labels, $one['name'], ''));
                $value = ArrayHelper::getValue($this->params, $one['name']);
                if ($value) {
                    $url[$one['name']] = $value;
                }
                if ($type == 'input') {//输入框
                    $inputOption = ArrayHelper::getValue($one, 'inputOption', []);
                    $control = Html::tag('div', Html::input('text', $one['name'], $value,
                        ArrayHelper::merge([
                            'class' => "layui-input " . ArrayHelper::remove($inputOption, 'class', ''),
                            'placeholder' => ArrayHelper::getValue($one, 'placeholder', $label),
                        ], $inputOption)
                    ), ['class' => 'layui-inline ' . ArrayHelper::getValue($one, 'class', 'width-240')]);
                } elseif ($type == 'checkbox') {//单选框
                    $control = Html::tag('div', Html::checkbox($one['name'], $value,
                        [
                            'lay-skin' => 'switch',
                            'lay-filter' => 'search',
                            'lay-text' => implode('|',
                                isset($one['items']) ? $one['items'] : $model::getAttributesList($one['name'])),
                        ]
                    ),
                        [
                            'class' => 'layui-inline ' . ArrayHelper::getValue($one, 'class', 'width-150'),
                            'style' => 'height: 38px;line-height: 38px;',
                        ]
                    );
                } elseif ($type == 'select') {//下拉框
                    $items = isset($one['items']) ? $one['items'] : $model::getAttributesList($one['name']);

                    $control = Html::tag('div', Html::dropDownList($one['name'], $value, $items,
                        [
                            'class' => count($items) > 10 ? 'select2' : '',
                            'lay-ignore' => count($items) > 10,
                            'lay-filter' => isset($one['filter']) ? $one['filter'] : 'search',
                            'prompt' => $one['prompt'],
                        ]
                    ), ['class' => 'layui-inline ' . ArrayHelper::getValue($one, 'class', 'width-150')]);
                } elseif ($type == 'radio') {//单选框
                    $items = isset($one['items']) ? $one['items'] : $model::getAttributesList($one['name']);

                    $radio = '';
                    foreach ($items as $key => $val) {
                        $value = ArrayHelper::getValue($this->params, $one['name'], $one['default']);
                        $value = is_numeric($value) ? intval($value) : $value;
                        $radio .= Html::radio($one['name'], $value === $key,
                            [
                                'title' => $val,
                                'value' => $key,
                                'lay-filter' => isset($one['filter']) ? $one['filter'] : 'search',
                            ]
                        );
                    }

                    $control = Html::tag('div', $radio, ['class' => 'layui-inline', 'style' => 'padding-bottom: 6px;']);
                } elseif ($type == 'date') {//日期
                    $control = Html::tag('div', Html::input('text', $one['name'], $value,
                        [
                            'class' => "layui-input date",
                            'placeholder' => ArrayHelper::getValue($one, 'placeholder', $label),
                            'autocomplete' => 'off'
                        ]
                    ), ['class' => 'layui-inline ' . ArrayHelper::getValue($one, 'class', 'width-150')]);
                } elseif ($type == 'date-range') {//日期区间
                    $control = Html::tag('div', Html::input('text', $one['name'], $value,
                        [
                            'class' => "layui-input date",
                            'data-range' => 'true',
                            'placeholder' => ArrayHelper::getValue($one, 'placeholder', $label),
                            'autocomplete' => 'off'
                        ]
                    ), ['class' => 'layui-inline ' . ArrayHelper::getValue($one, 'class', 'width-180')]);
                }

                if ($type == 'hidden') {
                    $html .= Html::hiddenInput($one['name'], ArrayHelper::getValue($this->params, $one['name'],
                        (isset($one['default']) ? $one['default'] : '')));
                } else {
                    $html .= Html::tag('div',
                        (empty($label) ? '' : Html::label($label) . '：') . $control,
                        ['class' => 'layui-col']
                    );
                }
            }

            $html .= Html::tag('div', Html::submitButton('搜索', ['class' => 'layui-btn']), [
                'class' => 'layui-col',
            ]);

            //自定义按钮，如：导出excel
            $customBtn = ArrayHelper::getValue($this->search, 'customBtn');
            if (!empty($customBtn)) {
                foreach ($customBtn as $btn) {
                    $html .= Html::tag('div', $btn, ['class' => 'layui-col']);
                }
            }

            // 面包屑控件  样式如：全部 | 正常 | 禁用 | 锁定 | 删除
            if (isset($this->search['breadcrumb'])) {
                $name = ArrayHelper::getValue($this->search['breadcrumb'], 'name', '');
                $items = ArrayHelper::getValue($this->search['breadcrumb'], 'items', $model::getAttributesList($name));
                $prompt = ArrayHelper::getValue($this->search['breadcrumb'], 'prompt');
                $value = ArrayHelper::getValue($this->params, $name);

                $html .= '<div class="layui-col"><span class="layui-breadcrumb" lay-separator="|">';
                if ($prompt) {// 初始默认选项
                    $html .= Html::a($prompt, $url, ['class' => $value == '' ? 'layui-this' : '']);
                }
                foreach ($items as $key => $val) {
                    $html .= Html::a($val, ArrayHelper::merge($url, [$name => $key]), ['class' => ($value != '' && $value == $key) ? 'layui-this' : '']);
                }
                $html .= '</span></div>';
            }

        }

        unset($model);
        return $html;
    }

    /**
     * 加载扩展字段中的搜索项字段
     */
    private function addExtendSearchItems()
    {
        $model = $this->model;
        $fields = ExtendsField::getSearchFieldByTable($model::tableName());
        foreach ($fields as $item) {
            $type = in_array($item['show_type'], ['select']) ? 'select' : (in_array($item['show_type'],
                ['radio']) ? 'checkbox' : 'input');
            $this->search['items'][] = [
                'type' => $type,
                'name' => $item['field_name'],
                'label' => $item['field_title'],
                'default' => $item['default_value'],
                'items' => ExtendsField::valueToArray($item['field_value']),
                'prompt' => '请选择',
            ];
        }

    }

    /**
     * 表格列解析配置参数
     * 把$columns的['field']、['field' => 'type']、['field' => [100, 'edit', ['type' => 'numbers'], 'align' => 'center'……]]
     * 三种格式转换成['key' => 'value', ……]键值对的形式
     */
    private function transformColumn()
    {
        foreach ($this->columns as $key => $column) {
            if (is_string($key)) {//字段名作为key
                if (is_array($column)) {//值为数组
                    $column['field'] = $key;
                    if (isset($column[0])) {//第一个值如果是数字则为width, 如果是字符串则为类型
                        if (is_numeric($column[0])) {
                            $column['width'] = intval(ArrayHelper::remove($column, 0));
                        } else {
                            $column[ArrayHelper::remove($column, 0)] = [];
                        }
                    }
                    if (isset($column[1])) {//第二个值为类型（edit、checkbox、date、custom……），第三个值为类型参数
                        $column[ArrayHelper::remove($column, 1)] = ArrayHelper::remove($column, 2, []);
                    }
                } else {//值为类型（edit、checkbox、date、custom……）
                    $column = [
                        'field' => $key,
                        $column => [],
                    ];
                }
            } elseif (is_string($column)) {//没有key，字段名作为value
                $column = ['field' => $column];
            }
            $this->columns[$key] = $column;
        }
        $this->columns = array_values($this->columns);
    }

    /**
     * 获取layui解析格式的表格顶部标题列
     * @return string
     */
    private function getThead()
    {
        $thead = [];
        foreach ($this->columns as $key => $column) {
            $layData = [];
            //默认左对齐
            $layData['align'] = ArrayHelper::getValue($column, 'align', 'center');
            //字段标签
            if (isset($column['field'])) {
                $model = isset($column['model']) ? new $column['model'] : new $this->model;
                $layData['field'] = isset($column['join']) ? $column['field'] . '_' . $column['field'] : $column['field'];
                $layData['title'] = ArrayHelper::getValue($model->attributeLabels(), $column['field'], '');
                unset($model);//销毁对象，释放内存
            }
            //自定义标签
            if (isset($column['label'])) {
                $layData['field'] = isset($layData['field']) ? $layData['field'] : $column['label'];
                $layData['title'] = $column['label'];
            }
            //特殊列（'checkbox', 'id', 'number', 'operate'）
            if (isset($column['type'])) {
                $type = ArrayHelper::remove($column, 'type');
                if (in_array($type, ['checkbox', 'numbers'])) {
                    $layData['field'] = 'id';
                    $layData['fixed'] = 'left';
                    $layData['width'] = isset($column['width']) ? intval($column['width']) : 50;
                    $layData['align'] = 'center';
                    $layData['type'] = $type;
                } elseif ($type == 'ID') {
                    $layData['width'] = isset($column['width']) ? intval($column['width']) : 60;
                    $layData['field'] = 'id';
                    $layData['fixed'] = 'left';
                    $layData['align'] = 'center';
                    $layData['title'] = 'ID';
                } elseif ($type == 'operate') {
                    $layData['width'] = isset($column['width']) ? intval($column['width']) : 60 + count($column['button']) * 40;
                    $layData['field'] = 'operate';
                    $layData['fixed'] = 'right';
                    $layData['align'] = isset($column['align']) ? $column['align'] : 'center';
                    $layData['title'] = isset($column['title']) ? $column['title'] : '操作';
                }
            } else {
                //定义宽度
                if (isset($column['width'])) {
                    $layData['width'] = intval($column['width']);
                } else {
                    $layData['minWidth'] = intval(ArrayHelper::getValue($column, 'minWidth', 120));
                    $layData['align'] = ArrayHelper::getValue($column, 'align', 'left');
                }
            }
            //可编辑列
            if (isset($column['edit']) && $column['edit'] !== false) {
                $layData['edit'] = 'text';
                $layData['title'] = $layData['title'] . " <i class='iconfont icon-edit'></i>";
            }
            //可排序列
            if (isset($column['sort'])) {
                $layData['sort'] = $column['sort'];
            }
            //固定列
            if (isset($column['fixed'])) {
                $layData['fixed'] = ArrayHelper::getValue($column, 'fixed');
            }
            //style样式
            if (isset($column['style'])) {
                $layData['style'] = ArrayHelper::getValue($column, 'style');
            }
            $thead[] = Html::tag('th', '', ['lay-data' => json_encode($layData)]);
        }

        return Html::tag('thead', empty($thead) ? '' : Html::tag('tr', implode('', $thead)));
    }

    /**
     * 获取解析表格内容列
     * @param $arr
     * @return string
     * @throws \Exception
     */
    private function getTbody($arr)
    {
        if (empty($arr)) {
            return '';
        }

        $list = [];
        $primaryKey = ArrayHelper::getValue($arr, $this->primaryKey, 0);

        foreach ($this->columns as $column) {
            $text = '';
            if (isset($column['field'])) {
                //获取对应字段的值
                $fieldValue = isset($column['join']) ? $arr[$column['join']][$column['field']] : ArrayHelper::getValue($arr,
                    $column['field'], null);
                //新建一个model对象
                $model = isset($column['model']) ? new $column['model'] : new $this->model;
                if (isset($column['edit'])) {//字段可编辑
                    $text = $fieldValue;
                } elseif (isset($column['checkbox'])) {//字段为checkbox开关
                    $disabled = false;
                    if (isset($column['checkbox']['filter'])) {
                        foreach ($column['checkbox']['filter'] as $k => $v) {
                            $disabled = $arr[$k] == $v;
                        }
                    }
                    $items = isset($column['checkbox']['items']) ? $column['checkbox']['items'] : $model::getAttributesList($column['field']);
                    $keys = array_keys($items);
                    $text = $disabled ? '' : Html::input('checkbox', $column['field'], null, [
                        'lay-skin' => 'switch',
                        'lay-text' => empty($items) ? '是|否' : implode('|', $items),
                        'data-id' => $primaryKey,
                        'data-filter' => $column['field'],
                        'data-checked' => $keys[0],
                        'data-unchecked' => $keys[1],
                        'checked' => $fieldValue == $keys[0],
                    ]);
                } elseif (isset($column['date'])) {//字段为日期格式
                    $text = empty($fieldValue) ? '' : date(ArrayHelper::getValue($column['date'], 'format', 'Y-m-d'), $fieldValue);
                } elseif (isset($column['datetime'])) {//字段为日期时间格式
                    $text = empty($fieldValue) ? '' : date(ArrayHelper::getValue($column['datetime'], 'format', 'Y-m-d H:i:s'), $fieldValue);
                } elseif (isset($column['url'])) {//字段为链接
                    $text = Html::a($fieldValue, $column['url'] ?: ['view', 'id' => $primaryKey], ['class' => 'block']);
                } elseif (isset($column['custom'])) {//自定义字段
                    $paramsType = ArrayHelper::getValue($column, 'paramsType', 'field');//参数类型为field,传递该列字段值；否则传递该行数据
                    $param = $paramsType == 'field' ? $fieldValue : $arr;
                    $text = call_user_func($column['custom'], $param);
                } else {
                    $default = ArrayHelper::getValue($column, 'default', $fieldValue);
                    $text = ArrayHelper::getValue($column, 'icon') .
                        (empty($this->model) ? $fieldValue : $model::getAttributesList($column['field'], $fieldValue,
                            $default));
                }
                unset($model);//销毁对象，释放内存
            } elseif (isset($column['type'])) {//特殊列
                $column['style'] = isset($column['style']) ? $column['style'] : 'text-align: center';
                switch ($column['type']) {
                    case 'numbers':
                    case 'checkbox':
                    case 'ID':
                        $text = $primaryKey;
                        break;
                    case 'operate':
                        if (isset($column['button'])) {
                            foreach ($column['button'] as $k => $v) {
                                if (in_array($k, ['edit', 'del', 'view'])) {
                                    if (is_array($v)) {
                                        $v = count(Tool::get_array_by_condition([$arr], $v)) ? $k : '';
                                    } elseif (is_bool($v)) {
                                        $v = $v ? $k : '';
                                    }
                                }

                                if ($v instanceof \Closure) {
                                    $text .= call_user_func($v, $arr);
                                } elseif ($v == 'edit') {
                                    $text .= Html::a('编辑', Url::to(['edit', 'id' => $arr[$this->primaryKey]]), [
                                        'class' => 'layui-btn layui-btn-sm btn-edit',
                                    ]);
                                } elseif ($v == 'del') {
                                    $text .= Html::tag('a', '删除', [
                                        'class' => 'layui-btn layui-btn-primary layui-btn-sm btn-del',
                                        'data-id' => $arr[$this->primaryKey],
                                    ]);
                                } elseif ($v == 'view') {
                                    $text .= Html::a('查看', Url::to(['view', 'id' => $arr[$this->primaryKey]]), [
                                        'class' => 'layui-btn  layui-btn-sm btn-view',
                                    ]);;
                                } else {
                                    $text .= $v;
                                }
                            }
                        }
                        $text = Html::tag('div', $text, ['class' => 'layui-btn-group']);
                        break;
                }
            } elseif (isset($column['custom'])) {//自定义内容
                $text = call_user_func($column['custom'], $arr);
            } elseif (isset($column['text'])) {//自定义文本
                $text = $column['text'];
            }
            $list[] = Html::tag('td', $text);
        }
        $tr = Html::tag('tr', implode('', $list));

        if (isset($arr['children']) && !empty($arr['children'])) {
            foreach ($arr['children'] as $one) {
                $tr .= self::getTbody($one);
            }
        }

        return $tr;
    }

    /**
     * 获取批量处理按钮
     * @return string
     */
    private function getBatchBtn()
    {
        $html = '';
        if ($this->batchBtn != false) {
            foreach ($this->batchBtn as $val) {
                if ($val == 'del') {
                    $html .= Html::tag('a',
                        '删除',
                        ['class' => 'layui-btn layui-btn-sm btn-batch-del']
                    );
                } else {
                    $html .= $val;
                }
            }
            $html = $html . "";
        }
        return $html;
    }

    /**
     * 跳转页面
     * @return string
     */
    private function getPageGoto($count)
    {
        if (!empty($this->pagination)) {
            return Html::tag('p',
                '总计' . $this->pagination->totalCount . '条数据，每页显示' .
                Html::input('number', null, $this->pagination->defaultPageSize,
                    ['id' => 'pageSize', 'min' => 1])
                . '条，共' . $count . '页' .
                ';' .
                ($count > 1 ? "跳转到" . Html::input('number', null, ArrayHelper::getValue($this->params, 'page', 1),
                        ['id' => 'goto', 'min' => 1, 'max' => $count]) .
                    '页' : '') .
                Html::button('确定', ['class' => 'layui-btn layui-btn-primary  layui-btn-sm']),
                ['class' => 'goto']);
        } else {
            return "";
        }
    }
}