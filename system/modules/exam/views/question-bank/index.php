<?php
/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2019/1/28
 * Time: 10:30
 */
use yii\helpers\Url;
// 标题
$this->title = "列表";

?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this"><?= $this->title ?></li>
        <li><a href="<?= Url::toRoute('add') ?>">添加</a></li>
    </ul>
</div>


<div class="layui-row">
    <?= \system\widgets\GridViewWidget::widget([
        'data' => $data,
        'model' => \system\modules\exam\models\ExamQuestionCategory::className(),
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
            'name',
            'is_question_bank',
            'update_time'=>
                [ 180, 'datetime'],
            [
                'type' => 'operate',
                'button' => [
                    'edit',
                    'del',
                ],
            ],
        ],
    ]) ?>
</div>

