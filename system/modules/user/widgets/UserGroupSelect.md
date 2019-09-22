# 用户选择器使用说明

## 1、功能说明

根据参数配置灵活的选择用户、部门、职位等数据

## 2、使用说明
只要给任意一个input输入框加上类名user-group-select即可，页面加载后会自动找到类名包含user-group-select的input输入框进行初始化，需要的参数在input中用data属性配置即可；需要的参数如下：

* data-title        弹出选择框的头部标题，默认：请选择
* data-show_user    是否显示用户，0：不显示；1：显示；默认为1
* data-show_page    显示的选择标签页，可根据部门(department)、岗位(position)、指定用户(user)进行选择，默认department,position;
* data-select_max   最多选择数量，0表示不显示；默认为0
* data-select_type  选择的类型，默认为user；可以选择部门(department)、岗位(position)、用户(user)，比如同时选部门和用户：department,user
* data-show_range   选择范围，默认为空，可以限制能选择的部门、岗位、用户；如G1,G2,G3,P1,P2,U1,U2,U3,U4,U5，表示只能选择部门ID为1、2、3；岗位ID为1、2；用户ID为1、2、3、4、5
* data-range_type   选择范围类型，0：取交集，1：去并集；默认为0；
* data-glue         默认为‘,’；详细说明请看注意事项

## 3、注意事项
#### 1、用户选择器最终返回的数据是G、P、U接对应ID的字符串，如G1,G2,P1,U1,U2；大多数情况最终想要的是一个纯ID的数组或者数组拼接的字符串，因此我在Tool工具类中提供了一个方法：
```php
/**
 * 把用户选择器返回的数组转换成ID数组或者字符串
 * @param array|string $list 用户选择器返回的数据，如G1,G2,P1,U1,U2
 * @param int $backType 返回类型；0：返回implode连接后的字符串；1：返回处理后的ID数组
 * @param string $glue  implode函数的连接字符串，默认为','
 * @return array|string 返回值
 */
public static function convertIds($list, $backType = 0, $glue = ',') {
    ……
}
```
#### 将获取到的值经过Tool::convertIds(……)转换后可以得到ID数组或者拼接好的字符串。

#### 2、传入的数据通常为一个数字ID或者数字ID拼接的字符串，对此我也进行了一些预处理：
* 后台首先把传入的数据(字符串类型)用explode()转换成数组，explode()的第一个参数就是data-glue的值，默认是','；
* 然后会判断select_type的值，如果是'user'，会给每个ID前面加上U，如果是'department'，会给每个ID前面加上G，如果是'position'，会给每个ID前面加上P；(如果select_type有多种类型，则不会处理)