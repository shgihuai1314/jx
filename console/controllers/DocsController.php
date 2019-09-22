<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-4-20
 * Time: 16:20
 */

namespace console\controllers;

use system\core\utils\Tool;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Markdown;
use Yii;

class DocsController extends Controller
{
    /**
     * 提取API接口中所有的接口信息，生成接口文档
     * @param string $version 接口版本号
     */
    public function actionIndex($version = 'v1')
    {
        $data = [];

        $path = Yii::getAlias('@api') . '/modules/' . $version . '/controllers';
        // 遍历api/modules/v1/controllers中的所有文件夹
        if (is_dir($path) && $dh = opendir($path)) {
            while (($file = readdir($dh)) !== false) {
                if (is_file($path . '/' . $file) && $file != 'BaseApiController.php') {
                    // 遍历文件夹中所有控制器，获取控制器中所有接口信息
                    $content = file_get_contents($path . '/' . $file);

                    $controller = $this->humpToLine(str_replace('Controller.php', '', $file), '-');
                    echo "Get Controller Content: " . $file . "\n";
                    $data[$controller] = $this->getActionInfo($content);
                }
            }
        }

        $page = $this->getMarkdownDoc($data);
//        print_r(Yii::getAlias('@docs' . '/markdown/ApiDoc.md'));die;
        file_put_contents(Yii::getAlias('@docs' . '/markdown/ApiDoc.md'), $page);

        $this->MarkdownToHtml();
    }

    /**
     * 创建markdown文档
     * @param array $data api接口信息
     * @return string 文档内容
     */
    private function getMarkdownDoc($data)
    {
        $content = "
# 雨滴在线教学平台API接口说明文档

## 概述

本文档主要提供给雨滴在线教学平台前端进行前后端分离式开发使用。

### 文档修订日期

日期 | 修订人
:---:|:--:
" . date('Y-m-d') . " | 罗波

## 使用说明

- 接口的完整地址为  http://服务器地址/api.php/请求地址 或者 http://服务器地址/api.php/版本号/请求地址 (*针对移动端不同版本*)  
    如：认证接口请求地址为 user/auth，接口版本号为v1，则接口完整地址为 http://服务器地址/api.php/user/auth 或 http://服务器地址/api.php/v1/user/auth。
- 需要认证才允许访问的接口，请将认证后获得的用户token加入到header头部，格式为 
> Authorization：Bearer {token}
- 所有接口返回信息格式均为：
> 成功返回
```json
{
    'code': 0,
    'message': ..., // 成功信息
    'data': ...// 返回数据，无数据返回则没有该信息
}
```
> 失败返回
```json
{
    'code': 1,
    'message': ...// 失败信息
}
```

<html>
<a name=\"api-menu\"></a>
</html>

## 接口目录
                   
<html>
";

        $data = Tool::array_sort_by_keys($data, ['user'], true);
        $n = 1;
        foreach ($data as $controller => $actions) {
            foreach ($actions as $one) {
                if (isset($one['info'])) {
                    $url = $controller . "/" . $one['action'];
                    $info = $one['info'];

                    $content .= "<a class='api-item' href='#$url'>$n. $info: $url</a>\n";
                    $n ++;
                }
            }
        }

        $content .= "
</html>

## 接口信息
";
        $n = 1;
        foreach ($data as $controller => $actions) {
            foreach ($actions as $one) {
                $url = $controller . "/" . $one['action'];
                $info = isset($one['info']) ? $one['info'] : '';
                $auth = isset($one['auth']) ? $one['auth'] : '' ? '是' : '否';
                $method = isset($one['method']) ? $one['method'] : '';
                $desc = isset($one['desc']) ? $one['desc'] : '';
                $params = isset($one['param']) ? $one['param'] : [];
                $return = isset($one['return']) ? $one['return'] : '';
               // var_dump($one);exit;
                $return = $this->formatArrToJson($return);

                $return = str_replace('[', '{', str_replace(']', '}', str_replace(' =>', ':', $return)));

                $paramInfo = "";
                foreach ($params as $param) {
                    $paramInfo .= trim(isset($param[1]) ? $param[1] : '', '$') . " |"; // 参数名
                    $paramInfo .= (isset($param[3]) && $param[3] == 'required' ? '是' : '否') . " |"; // 是否必填
                    $paramInfo .= (isset($param[2]) ? str_replace('|', "\|", $param[2]) : '') . "\r\n"; // 参数说明
                }
                $content .= "
                    
<html>
<a name=\"$url\"></a>
</html>

### $n. $info
$desc

#### 接口地址：$url  

> http请求方式：$method  
> 是否需要认证：**$auth**

#### 参数说明：

参数名称 | 是否必填 | 说明
:---:|:---:|:---
$paramInfo

#### 返回信息
```json
$return
```
##
                    ";
                $n++;
            }
        }

        return $content;
    }

    /**
     * 将/docs/markdown中的md文档转换成html格式的文档
     */
    private function MarkdownToHtml()
    {
        $handle = opendir(Yii::getAlias('@docs' . '/markdown/'));
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $md = explode('.', $file)[0];
            $mdHtml = Markdown::process(file_get_contents(Yii::getAlias('@docs' . '/markdown/' . $md . '.md')), 'gfm');
            $page = $this->getHtmlDoc($mdHtml);
            file_put_contents(Yii::getAlias('@docs' . '/html/' . $md . '.html'), $page);
        }
    }

    /**
     * 创建html文档
     * @param $mdHtml
     * @param string $title
     * @return string
     */
    private function getHtmlDoc($mdHtml)
    {
        return '<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="static/css/index.css"/>
    <title>API接口说明文档</title>
</head>
<body>
    <div class="markdown-body">
        ' . $mdHtml . '
        <div class="back_to_menu"><a href="#api-menu"><img src="static/images/back_to_top.png"/></a></div>
    </div>
</body>
</html>';
    }

    /**
     * 正则匹配API控制器中所有action的信息
     * @param string $content API控制器文本内容
     * @return array
     */
    private function getActionInfo($content)
    {
        // 匹配到extends BaseApiController后面的内容，防止类前面有/** ... */格式的注释
        preg_match("/extends BaseApiController.*?\{(.*)/s", $content, $matches);
        $content = isset($matches[1])? $matches[1] : '';
        // 获取不需要认证的action
        preg_match('/public \$notAuthAction = \[(.*?)\]/s', $content, $matches);
        $notAuth = empty($matches) ? [] : explode(',', str_replace(["'","\r\n"], '', str_replace(" ", '', $matches[1])));

        // 正则匹配控制器中所有方法的注释信息和action名
        preg_match_all("/\/\*\*\s*(.*?)\*\/.*?public function action([\w]+)/s", $content, $matches);

        $arr = [];
        foreach ($matches[1] as $key => $notes) {
            $notes = explode("@", str_replace("\r\n", '', str_replace('*', '', $notes)));
            $arr[$key] = [];
            echo "action:" . $this->humpToLine($matches[2][$key], '-') . "......\n";
            foreach ($notes as $note) {
                $arr[$key]['action'] = $this->humpToLine($matches[2][$key], '-');
                $arr[$key]['auth'] = !in_array($arr[$key]['action'], $notAuth);
                $note = trim($note);
                if ($note) {
                    $note = explode(' ', $note, 2);
                    if ($note[0] == 'param') {// 参数信息
                        $arr[$key]['param'][] = explode(' ', $note[1]);
                    } elseif ($note[0] == 'return') {// 返回信息
                        $return = str_replace('array', '', str_replace(' ', '', $note[1]));
                        $arr[$key]['return'] = $return;
                    } else {// 其他信息，如info、method、desc等
                        $arr[$key][$note[0]] = ArrayHelper::getValue($note, 1, '');
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * 驼峰转下划线
     * @param $str
     * @return null|string|string[]
     */
    private function humpToLine($str, $blue = '_')
    {
        $str = preg_replace_callback('/([A-Z]{1})/', function ($matches) use ($blue) {
            return $blue . strtolower($matches[0]);
        }, $str);

        return trim($str, $blue);// 去掉首个下划线
    }

    /**
     * 格式化数组格式的字符串，转换成json格式
     * @param $str
     * @return string
     */
    private function formatArrToJson($str)
    {
        $newStr = '';
        $tab = 0;
        $str = str_replace('=>', ': ', str_replace(' ', '', $str));
        for ($i = 0; $i < strlen($str); $i++) {
            if ($str[$i] == '[') {
                $newStr .= "{\n" . str_repeat("\t", ++$tab);
            } elseif ($str[$i] == ',') {
                $newStr .= ",\n" . str_repeat("\t", $tab);
            } elseif ($str[$i] == ']') {
                $newStr .= "\n" . str_repeat("\t", --$tab) . "}";
            } else {
                $newStr .= $str[$i];
            }
        }

        return $newStr;
    }
}
