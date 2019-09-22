<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/13
 * Time: 下午1:49
 */
/** @var$model \system\modules\main\models\Config*/

$this->title = '修改密码';

// 用户状态数组
$user_status_list = Yii::$app->systemConfig->getValue('USER_STATUS_LIST', []);
$isEdit = false;
if (!$model->isNewRecord) {
    $isEdit = true;
}
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title clearfix">
        <li><a href="<?= \yii\helpers\Url::toRoute('update')?>">个人资料</a></li>
        <li class="layui-this">修改密码</li>
    </ul>

</div>

<div class="layui-row">
    <div class="layui-col-md10">
        <form class="layui-form custom-form layui-col-space30" method="post" action="">
            <input name="<?= Yii::$app->request->csrfParam?>" type="hidden" value="<?= Yii::$app->request->csrfToken ?>">
            <div class="layui-col-lg9">
                <div class="layui-form-item">
                    <label class="layui-form-label">用户名</label>
                    <div class="layui-input-block form-input-text">
                        <input type="text" class="layui-input" disabled value="<?= $model->username?>"/>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">原始密码</label>
                    <div class="layui-input-block">
                        <input type="password" name="oldPassword" lay-verify="required" autocomplete="off" class="layui-input" placeholder="请输入旧密码"  value="">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">新密码</label>
                    <div class="layui-input-block">
                        <input type="password" name="newPassword" lay-verify="required" autocomplete="off" class="layui-input" placeholder="请输入新密码"  value="">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">确认密码</label>
                    <div class="layui-input-block">
                        <input type="password" name="newPasswordRepeat" lay-verify="required" autocomplete="off" class="layui-input" placeholder="请重复新密码"  value="">
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit="">立即提交</button>
                        <a href="JavaScript:history.go(-1)" class="layui-btn layui-btn-primary">返回</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
