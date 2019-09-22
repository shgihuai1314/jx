# 附件展示挂件

## 1、功能说明
提供附件展示功能

## 2、调用方式
```php
<?= \system\modules\main\widgets\FileViewWidget::widget([
    ……
]) ?>
```

## 3、参数说明
```php
$files          //默认附件值，可以传入ID、ID数组、用","分隔的ID字符串、文件路径、文件路径数组和用","分隔的文件路径字符串
$flag           //附件值类型(0：传入的是附件表中的ID；1：传入的是附件路径)，默认为0
$canDownload    //是否允许下载，默认为true
```
