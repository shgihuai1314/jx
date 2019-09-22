<?php

/** @var yii\web\View $this */
/** @var system\modules\payment\models\PayTradeDetail $model */

use yii\helpers\Html;
use yii\helpers\Url;

// 标题
$this->title = $model->isNewRecord ? '添加' : '编辑';
?>

<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li><a href="<?= Url::toRoute('index') ?>">列表</a></li>
        <li class="layui-this"><?= $this->title ?></li>
    </ul>
</div>

<div class="layui-row">
    <?= system\widgets\FormViewWidget::widget([
        'model' => $model,
        'fields' => [
            'name' => [
                'required' => true,
            ],

            'code' => [
                'required' => true,
            ],

            'pay_type' => [
                'type' => 'checkbox',
            ],

            'alipay_config' => [
                'calss' => 'test',
//                'required' => true,
                'type' => 'textarea',
                'hint' => '如果是数组，每行一组数据，格式：组appid=名称，比如：1=开启'
            ],

            'wechat_config' => [
//                'required' => true,
                'type' => 'textarea',
                'hint' => '如果是数组，每行一组数据，格式：appid=xx,mchid=xx,key=xx,没有子商户号可不配置子商户(sub_appid,sub_mch_id)'
            ],

            'status' => 'radio',

          /*  'describle' => [
                //'required' => true,
                'type' => 'textarea',
            ],*/

            'notify_class'=>[
               'required' => true,
                'hint' => '支付成功之后要做什么,都在这个链接进行,格式如:http://192.168.10.116/api.php/course-notify/notify',
            ],

            'notify_url' => [
                'required' => true,
                'hint' => '注意跳转url必须是带http://www.baidu.com//或者https://的绝对路径',
            ],

            'secret'=>[
                'required' => true,
                'hint' => '对接系统和支付平台约定好的密钥',
            ],

            'pay_nums' => [
                'type' => 'textarea',
                'hint' => '填写金额 如1=1个月，2=2个月',
            ],

            'app_rand' => [
                'class' => 'user-group-select',
                'options' => [
                    //'data-show_page'=>'position',
                    //'data-select_type' => 'position',
                    'data-select_max' => '0',
                ],
            ],
        ],
    ]) ?>
</div>

<script>

    $('.field-paymentApp-pay_type').on('click', function () {
        var test = [0, 1];
        var entrance = [];
        $('.field-paymentApp-pay_type input[type="checkbox"]:checked').each(function (data) { // 遍历多选框
            // alert(data)
            //console.log(data)
            entrance.push($(this).val());
        });
</script>