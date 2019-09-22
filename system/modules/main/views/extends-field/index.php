<?php
/**
 * 默认的首页模板文件
 * 如果有列表和添加的权限，那么显示两个，上面是添加表单，通过折叠进行显示隐藏，下面是列表形式
 * 如果只有添加权限，没有列表权限，那么直接在本页面显示添加表单，去掉折叠功能
 * @var yii\web\View $this
 * @var $list Array 套餐列表
 */

/** @var yii\web\View $this */
/** @var array $data */

use system\modules\main\models\ExtendsField;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = '扩展字段';
$attributes = ExtendsField::getAttributesList();
?>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this">扩展字段</li>
        <li><a href="<?= Url::to(['add', 'table_name' => Yii::$app->request->get('table_name', '')]) ?>">新增字段</a></li>
    </ul>
</div>

<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'data' => $data,//数据
        'model' => ExtendsField::className(),//模型类
        'search' => [//查询字段
            'items' => [
                [
                    'class' => 'width-180',
                    'type' => 'select',
                    'name' => 'table_name',
                    'prompt' => '请选择',
                ],
                [
                    'class' => 'width-240',
                    'type' => 'input',
                    'name' => 'search',
                    'label' => '关键字',
                    'placeholder' => '输入关键字搜索',
                ],
            ],
        ],//查询
        'columns' => [//列表信息
            'table_name' => [150],
            'field_title',
            'field_name' => [200, 'align' => 'left'],
            'field_type' => [100],
            'show_type' => [120],
            'default_value' => [120, 'edit'],
            'is_must' => [100, 'checkbox'],
            'sort' => [120, 'edit'],
            [
                'type' => 'operate',
                'button' => ['edit', 'del'],
            ],
        ],//列参数
    ]);?>
</div>
