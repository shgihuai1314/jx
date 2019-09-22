<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/13
 * Time: 下午1:49
 */

/** @var \system\modules\user\models\User $model */

// 用户性别数组
$user_gender_list = Yii::$app->systemConfig->getValue('USER_GENDER_LIST', []);
// 用户状态数组
$user_status_list = Yii::$app->systemConfig->getValue('USER_STATUS_LIST', []);
$user_field_required_list = Yii::$app->systemConfig->getValue('USER_FIELD_REQUIRED_LIST',[]);

$isEdit = false;
if (!$model->isNewRecord) {
    $isEdit = true;
}

if ($model->avatar) {
    $avatar = $model->avatar;
} else {
    $avatar = \Yii::$app->request->hostInfo . '/static/images/avatar/default/'.rand(1, 20).'.jpg';
}

\system\assets\Select2Asset::register($this);
?>

<form class="layui-form" method="post" action="" lay-fileter="userForm">
    <div class="layui-row">
        <div class="layui-col-xs6">
            <input name="<?= Yii::$app->request->csrfParam?>" type="hidden" value="<?= Yii::$app->request->csrfToken ?>">
            <div class="layui-form-item">
                <label class="layui-form-label">头像</label>
                <div class="layui-input-inline" style="width: 250px;">
                    <img id="avatar_img" width="80" height="80" style="border-radius: 50%;" src="<?= $avatar?>">
                    <button type="button" class="layui-btn layui-btn-primary" id="uploadImg">
                        <i class="layui-icon">&#xe608;</i>上传新头像
                    </button>
                    <input type="hidden" name="avatar" value="<?= $avatar?>">
                </div>
                <!--<div class="layui-input-inline" style="margin:10px 0 10px 110px;line-height:30px">
                    <p>建议上传300*300的图片</p>
                </div>-->
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">用户名<span class="text-red">*</span></label>
                <div class="layui-input-block">
                    <input type="text" name="username" lay-verify="required|config_name" autocomplete="off" placeholder="只能使用英文且不能重复" class="layui-input" value="<?= $model->username?>">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">姓名<?=in_array('realname',$user_field_required_list)?'<span class="text-red">*</span>':''?></label>
                <div class="layui-input-block">
                    <input type="text" name="realname" placeholder="请输入真实姓名" autocomplete="off" class="layui-input" value="<?= $model->realname?>" <?=in_array('realname',$user_field_required_list)?'lay-verify="required"':''?>>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">状态</label>
                <div class="layui-input-block">
                    <?php foreach ($user_status_list as $key => $value):?>
                        <input type="radio" name="status" value="<?= $key?>" title="<?= $value?>" <?php if ($model->status == $key): ?> checked="" <?php endif ?>>
                    <?php endforeach;?>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">性别<span class="text-red">*</span></label>
                <div class="layui-input-block">
                    <?php foreach ($user_gender_list as $key => $value):?>
                        <input type="radio" name="gender" value="<?= $key?>" title="<?= $value?>" <?php if ($model->gender == $key): ?> checked="" <?php endif ?>>
                    <?php endforeach;?>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">密码<span class="text-red">*</span></label>
                <div class="layui-input-inline">
                    <input type="text" name="password" <?php if (!$isEdit) echo 'lay-verify="required"'; ?> autocomplete="off" class="layui-input" placeholder="请输入"  value="">
                    <?php if ($isEdit):?><div class="help-block">如果不填写，则不修改密码</div><?php endif;?>
                </div>

                <div class="help-block"><?php if ($model->last_change_password > 0): ?>最后修改于：<?= date('Y-m-d H:i:s', $model->last_change_password)?><?php endif;?></div>

            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">手机号<?=in_array('phone',$user_field_required_list)?'<span class="text-red">*</span>':''?></label>
                <?php if ($model->validation_phone):?>
                    <div class="layui-input-inline form-input-text">
                        <?= $model->phone?>
                        <i style="color: #00B83F" class="fa fa-check-square-o system-tip" aria-hidden="true" data-tip="已验证绑定"></i>
                    </div>
                <?php else:?>
                    <div class="layui-input-inline">
                        <input type="text" name="phone" placeholder="请输入" autocomplete="off" class="layui-input" value="<?= $model->phone?>" <?=in_array('phone',$user_field_required_list)?'lay-verify="required"':''?>>
                    </div>
                    <div class="help-block">未验证</div>
                <?php endif;?>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">QQ</label>
                <div class="layui-input-inline">
                    <input type="text" name="qq" placeholder="请输入" autocomplete="off" class="layui-input" value="<?= $model->qq?>"
                        <?=in_array('qq',$user_field_required_list)?'lay-verify="required"':''?>>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">邮箱<?=in_array('email',$user_field_required_list)?'<span class="text-red">*</span>':''?></label>
                <?php if ($model->validation_email):?>
                    <div class="layui-input-inline form-input-text">
                        <?= $model->email?>
                        <i style="color: #00B83F" class="fa fa-check-square-o system-tip" aria-hidden="true" data-tip="已验证绑定"></i>
                    </div>
                <?php else:?>
                    <div class="layui-input-inline">
                        <input type="text" name="email" placeholder="请输入" autocomplete="off" class="layui-input" value="<?= $model->email?>" <?=in_array('email',$user_field_required_list)?'lay-verify="required"':''?>>
                    </div>
                    <div class="help-block">未验证</div>
                <?php endif;?>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">用户备注</label>
                <div class="layui-input-block">
                    <textarea name="remark" class="layui-textarea"><?= $model->remark?></textarea>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="formUser">立即提交</button>
                    <a href="JavaScript:history.go(-1)" class="layui-btn layui-btn-primary">返回</a>
                </div>
            </div>

        </div>

        <div class="layui-col-xs6">

            <div class="layui-form-item">
                <label class="layui-form-label">排序</label>
                <div class="layui-input-inline">
                    <input type="text" name="sort" layui-verify="required|number" autocomplete="off" class="layui-input" value="<?= $model->sort?>">
                </div>
                <div class="help-block">数字越大排序越高</div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">职位<?=in_array('position_id',$user_field_required_list)?'<span class="text-red">*</span>':''?></label>
                <div class="layui-input-inline">
                    <select name="position_id" <?=in_array('position_id',$user_field_required_list)?'lay-verify="required"':''?> >
                        <option value="0" selected>请选择...</option>
                        <?php foreach ($position as $k => $v):?>
                            <option value="<?= $k?>" <?php if ($k == $model->position_id) echo 'selected="selected"'; ?> ><?= $v?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if (\system\modules\role\models\AuthAssign::isSuper(Yii::$app->user->getId())):?>
           <!-- <div class="layui-form-item">
                <label class="layui-form-label">管理权限</label>
                <div class="layui-input-inline">
                    <input type="radio" lay-filter="isAdmin" name="is_admin" value="0" title="不允许" <?php /*if ($model->is_admin == 0): */?> checked="" <?php /*endif; */?> >
                    <input type="radio" lay-filter="isAdmin" name="is_admin" value="1" title="允许" <?php /*if ($model->is_admin == 1): */?> checked="" <?php /*endif; */?>>
                </div>
            </div>-->

            <div class="layui-form-item" id="adminRole" >
                <label class="layui-form-label">角色</label>
                <div class="layui-input-block">
                    <select multiple=”multiple” class="selectRole" lay-ignore style="width: 80%;">
                        <?php foreach ($role as $k => $v):?>
                            <option value="<?= $k?>" <?php if (in_array($k, $model->roles)) echo 'selected="selected"'; ?> ><?= $v?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="roles" value="" id="roleInput">
                </div>
            </div>
            <?php endif;?>

            <div class="layui-form-item">
                <label class="layui-form-label">部门</label>
                <div class="layui-input-block">
                    <?= \system\widgets\ZTreeWidget::widget([
                        'divId' => 'group',
                        'note_id' => $model->group_id,
                        'getUrl' => ['/user/group/ajax'],
                        'divOption' => 'style="padding: 8px; border: 1px solid #e6e6e6; max-height: 200px; overflow-y: auto;"',
                        'isExpand' => false,
                    ]);?>
                </div>
            </div>

            <?= \system\modules\main\widgets\ExtendedFieldWidget::widget([
                'model' => $model,
            ]) ?>

        </div>
    </div>

</form>

<script type="text/javascript">
    layui.upload.render({
        elem: '#uploadImg',
        field: 'avatarFile',
        accept: 'images',
        size: 2048,
        url: '<?= \yii\helpers\Url::toRoute(['upload-avatar'])?>',
        done: function(res){
            //console.log(res); //上传成功返回值，必须为json格式
            if (res.code == 0) {
                $("#avatar_img").attr('src', res.data.src);
                $(":input[name=avatar]").val(res.data.src);
            }

            layer.msg(res.message);
        }
    });

    //自定义验证规则
    form.verify({
        config_name: function(value) {
            var message;
            $.ajax({
                type : "get",
                url : '<?= \yii\helpers\Url::toRoute(['', 'action'=> 'name-exit', 'id' => $model->user_id, 'username' => ''])?>'+value,
                async : false,
                success : function(res){
                    //res = eval("(" + res + ")");
                    if (res.code == 1) {
                        //a = 'hello';
                        message = res.message;
                    }
                }
            }, 'json');
            return message;
        }
    });

    /*form.on('radio(isAdmin)', function(data){
        if (data.value == 1) {
            $("#adminRole").show();
        } else {
            $("#adminRole").hide();
        }
    });*/

    //$("#adminRole").show();
    $(document).ready(function () {
        $(".selectRole").select2({
            placeholder: "请选择角色"
        });
    });

    form.on('submit(formUser)', function () {
        $('#roleInput').val($(".selectRole").select2().val());
    })
</script>

