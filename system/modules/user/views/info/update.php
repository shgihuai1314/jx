<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/13
 * Time: 下午1:49
 */
/** @var \system\modules\user\models\User $model*/

$this->title = '修改个人资料';

// 用户状态数组
$user_status_list = Yii::$app->systemConfig->getValue('USER_STATUS_LIST', []);
$isEdit = false;
if (!$model->isNewRecord) {
    $isEdit = true;
}
?>
<style>
    .layui-upload {overflow: hidden; padding: 0;}
    .layui-upload .layui-upload-list li.image-item img {height: 80px; border-radius: 50%;}
</style>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title clearfix">
        <li class="layui-this">个人资料</li>
        <li><a href="<?= \yii\helpers\Url::toRoute('password')?>">修改密码</a></li>
    </ul>

</div>

<?= \system\widgets\FormViewWidget::widget([
    'model' => $model,
    'fields' => [
        'avatar' => [
            'type' => 'widget',
            'class' => \system\modules\main\widgets\FileUploadWidget::className(),
            'config' => [
                'item'=>[
                    'url' => 'upload-avatar',
                    'field' => 'avatarFile',
                    'done' => '$("#upload-btn-list").html(\'<li class="image-item"><input type="hidden" name="avatar" value="\' + res.data.src + \'"/><img src="\' + res.data.src + \'"></li>\');layerObj.closeAll();'
                ],
                'permission' => ['upload'],
                'inputName' => '',
                'flag' => 1,
            ],
        ],
        [
            'label' => '个性签名',
            'html' => \system\modules\main\widgets\FileUploadWidget::widget([
                'files' => $model->extend ? $model->extend->autograph_img : '',
                'inputName' => 'autograph_img',
                'flag' => 1,
                'item' => [
                    'btnId' => 'upload-autograph_img'
                ]
            ])
        ],
        'username' => [
            'options' => ['disabled' => true]
        ],
        'realname',
        'phone',
        'email'
    ]
])?>