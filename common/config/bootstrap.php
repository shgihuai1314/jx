<?php
define('VERSION', '1.9.3.171230');  // 系统版本

Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@root', dirname(dirname(__DIR__)) . '/');                    // 程序根目录
Yii::setAlias('@system', dirname(dirname(__DIR__)) . '/system');            // 主程序目录
Yii::setAlias('@api', dirname(dirname(__DIR__)) . '/api');                  // API目录
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');          // 控制台目录
Yii::setAlias('@data', dirname(dirname(__DIR__)) . '/data');                // 保存数据库备份，模版文档等系统资源文件
Yii::setAlias('@extension', dirname(dirname(__DIR__)) . '/extension');      // 保存扩展的内容
Yii::setAlias('@docs', dirname(dirname(__DIR__)) . '/docs');                // 保存扩展的内容
Yii::setAlias('@courses', dirname(dirname(__DIR__)) . '/courses');

// 读取根目录下的.env配置文件，并把期中的配置项变成变量
$env = parse_ini_file(Yii::getAlias('@root/.env'));

/**
 * 获取.env配置文件内指定值
 * @param $key
 * @param null $default
 * @return null
 */
function env($key, $default = null)
{
    global $env;
    // 小写转大写
    $key = strtoupper($key);

    return isset($env[$key]) ? $env[$key] : $default;
}


/**
 * 修改.env配置文件内指定值
 * @param array $array
 */
function env_set($array)
{
    global $env;

    foreach ($array as $key => $val) {
        $env[strtoupper($key)] = $val;
    }

    $str = "";
    foreach ($env as $k => $v) {
        $str .= "$k=$v\r\n";
    }

    file_put_contents(Yii::getAlias('@root/.env'), $str);
}
