# 用户选择流

## 1、功能说明
新建一项数据时调用，选择人员后生成user_id数组

## 2、调用方式
```php
<?= system\modules\user\widgets\UserSelectFlow::widget([
    ……
]) ?>
```

## 3、参数说明
$default_user             //默认用户
                            读取数据库中默认用户字段，格式为 "U1,U2,U3..."

$input_name               //字段name，默认为user_select

$input_class              //字段class，默认为空

$required                 //是否必填，默认为必填，取消传参false

$input_label              //该挂件的label名称
