<?php

namespace system\modules\main\widgets;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

class TableView extends Widget
{
    public $data;

    public $filter;

    public $label;

    public $columns = [];

    public $layout;//模板

    public $tableOptions;//table样式

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        parent::run();
        $js =<<< EOT
            <script>
    $('input').blur(function(){
        $('#searchForm').submit();
    });
</script>
EOT;
        echo $this->renderItems().$js;
    }

    //渲染所有视图
    public function renderItems()
    {
        $tableHeader = $this->renderTableHeader();
        //搜索
        $renderSearch = $this->renderSearch();
        $tableBody = $this->renderTableBody();
        $content = array_filter([
            $tableHeader,
            $renderSearch,
            $tableBody,
        ]);
        return Html::tag('table', implode("\n", $content), $this->tableOptions);
    }

    //表头
    public function renderTableHeader()
    {
        $cells = [];
        $header = $this->columns[0];
        $header2 = array_pop($header);
        foreach ($header as $value) {
            $cells[] = Html::tag('th', $this->label[$value['attribute']], []);
        }

        $cells[] = Html::tag('th', $header2['header'], []);
        $content = Html::tag('tr', implode('', $cells), []);

        return "<thead>\n" . $content . "\n</thead>";
    }

    //搜索
    public function renderSearch()
    {
        $cells = [];
        $header = $this->columns[0];
        $header2 = array_pop($header);
        foreach ($header as $value) {
            //如果存在搜索把input列出来
            if (in_array($value['attribute'],$this->filter)) {
                $input = Html::tag('input',1,['type'=>'text','name'=>$value['attribute'],'class'=>'layui-input','value'=>\Yii::$app->request->get($value['attribute'])]);
                $cells[] = Html::tag('th', $input, []);
            }else{
                $cells[] = Html::tag('th', '', []);

            }

        }

        $cells[] = Html::tag('th', '', []);
        $content = Html::tag('tr', implode('', $cells), []);

        return "<form id='searchForm'><thead>\n" . $content . "\n</thead></form>";
    }

    //表内容
    public function renderTableBody()
    {
        //
        $body =[];
        $header = $this->columns[0];
        $header2 = array_pop($header);
        foreach ($this->data AS $k => $one) {
            $buttons = '';
            $rows = [];
            foreach ($header as $value) {
                //如果存在配置值的回调
                if (isset($value['value'])) {
                    $rows[] = Html::tag('td', call_user_func($value['value'],$one[$value['attribute']]), []);
                }else{
                    $rows[] = Html::tag('td', $one[$value['attribute']], []);
                }
            }
            //按钮
            foreach ($header2['buttons'] AS $controllerName => $btn) {
                $buttons .= call_user_func($btn, Url::to([$controllerName,'id'=>$one['id']]));
            }

            $rows[] = Html::tag('td', $buttons, []);

            $body[] = Html::tag('tr', implode('', $rows), []);
        }
        return "<tbody>\n" . implode('', $body) . "\n</tbody>";
    }
}



