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

?>
&#60;?php

/** @var yii\web\View $this */
/** @var system\modules\<?= $module ?>\models\<?= $model_class ?> $model */

use yii\helpers\Html;
use yii\helpers\Url;

// 标题
$this->title = $model->isNewRecord ? '添加' : '编辑';
?>

&#60;div class="layui-tab layui-tab-brief">
    &#60;ul class="layui-tab-title">
        &#60;li>&#60;a href="&#60;?= Url::toRoute('index')?>">列表&#60;/a>&#60;/li>
        &#60;li class="layui-this">&#60;?= $this->title ?>&#60;/li>
    &#60;/ul>
&#60;/div>

&#60;div class="layui-row">
    &#60;?= system\widgets\FormViewWidget::widget([
        'model' => $model,
        'fields' => [
            // 表单字段设置详情请查看文档
<?php foreach ($label as $field => $val) :?>
            '<?= $field ?>',
<?php endforeach; ?>
        ],
    ]) ?>
&#60;/div>