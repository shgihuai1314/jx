# 附件上传挂件

## 1、功能说明
提供附件上传功能

## 2、调用方式
```php
<?= \system\modules\main\widgets\FileUploadWidget::widget([
    ……
]) ?>
```

## 3、参数说明
```php
$model      //数据对象，表单挂件中会自动载入
$attribute  //指定对象的属性，表单挂件中会自动初始化
$item       //参数数组，上传附件需要的参数，参数内容如下
            [
                'accept' => 'images',   //接收的文件类型，默认为images；
                                            可选值有：images（图片）、file（所有文件）、video（视频）、audio（音频）
                'icon' => '',           //按钮中的fa字体图标，会根据accept类型自动赋值，也可以自定义；
                'title' => '',          //按钮标签的内容，如：上传图片；会根据accept类型自动赋值，也可以自定义；
                'url' => '',            //附件提交的url地址，默认是当前控制器的upload方法
                'exts' => '',           //允许上传的文件后缀。一般结合 accept 参数类设定。
                                            假设 accept 为 file 类型时，那么你设置 exts: 'zip|rar|7z' 即代表只允许上传压缩格式的文件。
                                            如果 accept 未设定，那么限制的就是图片的文件格式
                'data' => [],           //请求上传接口的额外参数
                'field' => '',          //设定文件域input的字段名，默认为file
                'multiple' => '',       //是否允许多文件上传。设置 true即可开启。
                'btnId' => '',          //上传按钮的ID，默认为upload-btn，如果一个页面有多个上传功能必须区分
            ]
$inputName  //提交给控制器的字段名
$files      //默认附件值，在表单挂件中会自动根据字段名从对象中获取，
                可以传入ID、ID数组、用","分隔的ID字符串、文件路径、文件路径数组和用","分隔的文件路径字符串
$flag       //附件值类型(0：传入的是附件表中的ID；1：传入的是附件路径)
$resetName  //是否重置文件名，默认为false
$permission //操作权限，默认['upload', 'download', 'delete']，分别对应上传、下载、删除权限
```

## 4、使用说明
附件上传后需要提交给后台进行处理，然后返回指定格式的数据，默认上传给当前控制器的Upload方法，所以需要在控制器中定义actionUpload方法，
后台已经提供了一个公共的upload方法，所以只需要在控制器中加上：
```php
/**
 * @inheritdoc
 */
public function actions()
{
    return [
        'upload' => [
            'class' => \system\modules\main\extend\Upload::className(),
            //upload/目录下的文件夹，需要指定模块名称
            'dir' => 'oa/options/' . date('Y') . '/' . date('m') . '/' . date('m'),
        ],
    ];
}
```
