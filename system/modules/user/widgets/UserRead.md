# 访问记录显示挂件

## 1、功能说明
提供查看访问人员记录功能

## 2、调用方式
```挂件提供了一个组件。可以在生成查看信息的时候定义参与的所有人未读状态。
   UserRead::addAllUserRead($target_type, $target_id, $group_id = 'G1')
```php
<?= \system\modules\user\widgets\UserReadWidget::widget([
    ……
]) ?>
```

## 3、参数说明
```php
$target_type     //目标类型，可自定义(推荐使用模型名作为标识)

$target_id       //进入访问记录时的ID，目标唯一标识   如$model->id

$mode            //用于区分pc端与移动端，默认为pc端，手机端添加 'mode' => 'mobile',
```
