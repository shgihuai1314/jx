<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/5/12
 * Time: 下午5:40
 */
/** @var \system\modules\user\models\Group $model */
// 加载用户选择挂件
$this->title = '修改部门';
?>

<form class="layui-form" method="post" action="<?= \yii\helpers\Url::toRoute(['edit', 'id' => $model->id])?>">
    <input name="<?= Yii::$app->request->csrfParam?>" type="hidden" value="<?= Yii::$app->request->csrfToken ?>">
    <div class="layui-form-item">
        <label class="layui-form-label">部门名称</label>
        <div class="layui-input-block">
            <input type="text" name="name" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="<?= $model->name?>">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">排序</label>
        <div class="layui-input-inline">
            <input type="text" name="sort" lay-verify="required|number" autocomplete="off" placeholder="" class="layui-input" value="<?= $model->sort?>">
        </div>
        <div class="help-block">数字越大排序越高</div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">部门负责人</label>
        <div class="layui-input-block">
	        <input type="text" class="layui-input user-group-select" name="manager" value="<?= $model->manager ?>"
			        data-select_max="1" data-select_type="user" data-title="请选择部门负责人"/>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">部门助理</label>
        <div class="layui-input-block">
	        <input type="text" class="layui-input user-group-select" name="assistant" value="<?= $model->assistant ?>"
			        data-select_max="1" data-select_type="user" data-title="请选择部门助理"/>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">上级领导</label>
        <div class="layui-input-block">
	        <input type="text" class="layui-input user-group-select" name="leader" value="<?= $model->leader ?>"
			        data-select_max="1" data-select_type="user" data-title="请选择上级主管领导"/>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">分管主任</label>
        <div class="layui-input-block">
	        <input type="text" class="layui-input user-group-select" name="sub_leader" value="<?= $model->sub_leader ?>"
			        data-select_max="1" data-select_type="user" data-title="请选择上级分管主任"/>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">联系电话</label>
        <div class="layui-input-block">
            <input type="text" name="tel" placeholder="" autocomplete="off" class="layui-input" value="<?= $model->tel?>">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">传真</label>
        <div class="layui-input-block">
            <input type="text" name="fax" placeholder="请输入" autocomplete="off" class="layui-input" value="<?= $model->fax?>">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">地址</label>
        <div class="layui-input-block">
            <input type="text" name="address" placeholder="请输入" autocomplete="off" class="layui-input" value="<?= $model->address?>">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">部门职能</label>
        <div class="layui-input-block">
            <textarea placeholder="请输入部门职能" class="layui-textarea" name="func" ><?=$model->func?></textarea>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">部门代码</label>
        <div class="layui-input-block">
            <input type="text" name="code" placeholder="请输入" autocomplete="off" class="layui-input" value="<?= $model->code?>">
        </div>
    </div>
    <?=\system\modules\main\widgets\ExtendedFieldWidget::widget(['model'=>$model])?>

    <div class="layui-form-item">
        <div class="layui-input-block">
            <button class="layui-btn" lay-submit="">立即提交</button>
            <a href="JavaScript:history.go(-1)" class="layui-btn layui-btn-primary">返回</a>
        </div>
    </div>
</form>
