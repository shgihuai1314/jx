<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2018-3-30
 * Time: 15:20
 */

/** @var yii\web\View $this */
/** @var array $data */

extract($data);

$primaryKey = array_keys($label)[0];
?>
&#60;?php

/** @var yii\web\View $this */
/** @var array $data */

use yii\helpers\Html;
use yii\helpers\Url;

// 标题
$this->title = "列表";
?>

&#60;div class="layui-tab layui-tab-brief">
    &#60;ul class="layui-tab-title">
        &#60;li class="layui-this">&#60;?= $this->title ?>&#60;/li>
        &#60;li>&#60;a href="&#60;?= Url::toRoute('add') ?>">添加&#60;/a>&#60;/li>
    &#60;/ul>
&#60;/div>

&#60;div class="layui-row">
    &#60;?= \system\widgets\GridViewWidget::widget([
        'data' => $data,
        'model' => \system\modules\<?= $module ?>\models\<?= $model_class ?>::className(),
        'search' => [
            'items' => [
                // 搜索项设置详情请查看文档
                [
                    'name' => 'search',
                    'class' => 'width-300',
                    'type' => 'input',
                    'placeholder' => '请输入',
                ],
            ],
        ],
        'columns' => [
            // 列表字段设置详情请查看文档
<?php foreach ($label as $field => $val) :?>
            '<?= $field ?>' => [100],
<?php endforeach; ?>
            [
                'type' => 'operate',
                'button' => [
                    'edit',
                    'del',
                ],
            ],
        ],
    ]) ?>
&#60;/div>