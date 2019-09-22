<?php
$this->title = '模块';
$label = $model->attributeLabels();
?>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title clearfix">
        <li><a href="<?= \yii\helpers\Url::to(['index']) ?>">模块</a></li>
    </ul>
</div>

<div class="layui-col-lg6 layui-col-md6">
    <form action="" method="post" class="layui-form">
        <input type="hidden" name="<?= \Yii::$app->request->csrfParam ?>" value="<?= \Yii::$app->request->csrfToken ?>">

        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label"><?=$label['config']?></label>
            <div class="layui-input-block">
                <textarea name="config" placeholder="请输入内容" class="layui-textarea" lay-verify="required"><?=$model->config?></textarea>
                <div class="help-block">
                    例子：system\modules\app\Module
                </div>
            </div>
        </div>

        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label"><?=$label['component']?></label>
            <div class="layui-input-block">
                <textarea name="component" placeholder="请输入内容" class="layui-textarea" lay-verify="required"><?=$model->component?></textarea>
                <div class="help-block">
                    例子：{"systemCollection":{"class":"system\\modules\\collection\\components\\CollectionComponent"}}
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="formDemo">确认修改</button>
                <a href="JavaScript:history.go(-1)" class="layui-btn layui-btn-primary">返回</a>
            </div>
        </div>
    </form>
</div>