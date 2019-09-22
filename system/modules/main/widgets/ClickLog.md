# 点击量记录挂件

## 1、功能说明
对当前页面进行点击记录

## 2、调用方式
```php
<?= \system\modules\main\widgets\ClickLogWidget::widget([
    ......
]) ?>
```

## 3、参数说明
```php

$target_type     //目标类型，可自定义(推荐使用模型名作为标识)

$target_id       //进入访问记录时的ID，目标唯一标识   如$model->id


