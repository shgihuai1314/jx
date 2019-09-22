<?php
/**
 * 公共的工具类
 * User: ligang
 * Date: 2015/5/11
 * Time: 13:28
 */

namespace system\core\utils;

use yii;
use yii\helpers\ArrayHelper;

class Tool
{
    const TRAFFIC_CARRY = 1024; //流量进位

    /**
     * 文件类型字典
     * @var array
     */
    public static $fileTypes = [
        'ai' => ['ai','application/postscript'],
        'code' => ['php', 'c', 'c++', 'asp', 'aspx', 'jsp', 'css', 'js', 'java', 'c#'],
        'excel' => ['xls', 'xlsx', 'application/vnd.ms-excel', 'application/x-excel'],
        'exe' => ['exe'],
        'fla' => ['fla', 'flac'],
        'html' => ['htm', 'html'],
        'music' => ['mp3', 'ogg', 'wav', 'ape', 'cda', 'au', 'midi', 'acc'],
        'pdf' => ['pdf', 'pdfx', 'application/pdf', 'application/pdfx'],
        'image' => ['jpg', 'png', 'gif', 'jpeg', 'bmp', 'image/jpeg', 'image/png', 'image/gif'],
        'ppt' => ['ppt', 'pptx', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'psd' => ['psd', 'application/octet-stream'],
        'rar' => ['rar'],
        'swf' => ['swf', 'application/x-shockwave-flash'],
        'txt' => ['txt', 'ini', 'inf'],
        'video' => ['rmvb', 'wmv', 'asf', 'avi', '3gp', 'mpg', 'mkv', 'mp4', 'dvd', 'ogm', 'mov', 'mpeg2', 'mpeg4', 'video/x-ms-wmv', 'video/mp4','video/x-msvideo', 'application/octet-stream', 'flv','flv-application/octet-stream','video/x-flv'],
        'word' => ['doc', 'docx', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'zip' => ['zip','7z'],
    ];

    /**
     * 发起post请求
     * @param $url string url 请求地址
     * @param $data string|array  数据，如果是数组格式，会转换为字符串格式
     * @param $header array  头数据
     * @return mixed
     */
    public static function postData($url, $data, $header = [], $method = 'post')
    {
        if (is_array($data)) {
            $data = http_build_query($data);
        }

        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        $header && curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置header

        // 针对https
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $res = curl_exec($ch);//运行curl
        curl_close($ch);

        return $res;//输出结果
    }

    /**
     * 获取指定url的httpcode，检查网站是否存在
     * @param $url
     * @param int $timeout
     * @return mixed
     */
    public static function httpcode($url, $timeout = 1)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_exec($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpcode;
    }

    /**
     * 获取图片的宽高尺寸
     * @param string $fileName 文件地址
     * @return array|bool
     */
    public static function getImgSize($fileName)
    {
        if (false === ($imageInfo = getimagesize($fileName))) {
            return false;
        }

        list($width, $height) = $imageInfo;

        return [
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * 等比例缩放图片尺寸
     * @param $fileName string 原始尺寸
     * @param $max int 期待的最大尺寸
     * @return array 返回实际的尺寸
     */
    public static function resetImgSize($fileName, $max = 500)
    {
        if (false === ($imageInfo = getimagesize($fileName))) {
            return false;
        }

        list($width, $height) = $imageInfo;

        if ($width == 0 || $height == 0) {
            return false;
        }

        if ($width <= $max && $height <= $max) {
            return [
                'width' => $width,
                'height' => $height,
            ];
        }

        if ($width > $height) {
            $height = $height * ($max / $width);
            $width = $max;
        } else {
            $width = $width * ($max / $height);
            $height = $max;
        }

        return [
            'width' => floor($width),
            'height' => floor($height),
        ];
    }

    /**
     * 判断$arr中的键是否存在于$data中，并且存在数据
     * @param $needle string|array 键名称，如果是array，则每个都需要符合才行
     * @param $arr array 目标数组
     * @return bool
     */
    public static function keyExit($needle, $arr)
    {
        if (!is_array($arr) || empty($arr)) {
            return false;
        }

        if (is_string($needle)) {
            if (isset($arr[$needle]) && !empty($arr[$needle])) {
                return true;
            } else {
                return false;
            }
        } else if (is_array($needle) && !empty($needle)) {
            foreach ($needle as $item) {
                if (!isset($arr[$item]) || empty($arr[$item])) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * 生成随机字符串
     * @param int $length 生成位数
     * @return null|string
     */
    public static function getRandChar($length = 32)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    /**
     * 简单的将数组转化为xml
     * @param $data array 要转化的数组
     * @param string $root 根节点的名称
     * @param array $cdata 是否用cdata进行包裹
     * @return string
     */
    public static function arrayToXml($data, $root = 'xml', $cdata = [])
    {
        $xml = '<' . $root . '>';
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . self::arrayToXml($val) . "</" . $key . ">";
            } else if (in_array($key, $cdata)) {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= '</' . $root . '>';

        return $xml;
    }

    /**
     * 字符串参数转化为数组;
     * 主要为了处理以下格式的数据：
     * id=123456
     * secret=888888
     * 也可以处理以下格式的数据：
     * 192.168.0.1
     * 192.168.2.10
     * @param $string
     * @return array
     */
    public static function paramsToArray($string)
    {
        $newParams = [];
        // 先把\r\n替换成标准的换行符
        $string = str_replace('\r\n', "\r\n", $string);
        $lines = preg_split('/\r?\n/', $string);
        //$lines = explode('\r\n', $string);
        //echo Json::encode($lines);exit;
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $one = explode('=', $line, 2);
            // 如果没有=号的形式，那么key和value相同，如果有=号的形式，那么按照设置的key和value进行设置
            if (count($one) == 1) {
                $newParams[$one[0]] = $one[0];
            } elseif (count($one) == 2) {
                $newParams[$one[0]] = $one[1];
            }
        }
        return $newParams;
    }

    /**
     * 格式化流量
     * @param $bytes int 字节
     * @return string
     */
    public static function bytes_format($bytes)
    {

        if ($bytes / (self::TRAFFIC_CARRY * self::TRAFFIC_CARRY * self::TRAFFIC_CARRY) >= 1)
            return number_format($bytes / (self::TRAFFIC_CARRY * self::TRAFFIC_CARRY * self::TRAFFIC_CARRY), 2) . "G";
        else if ($bytes / (self::TRAFFIC_CARRY * self::TRAFFIC_CARRY) >= 1)
            return number_format($bytes / (self::TRAFFIC_CARRY * self::TRAFFIC_CARRY), 2) . "M";
        else if (($bytes / 1000) >= 1)
            return number_format($bytes / self::TRAFFIC_CARRY, 2) . "KB";
        else
            return $bytes . "B";
    }

    /**
     * 反格式化流量
     * @param $size
     * @return float
     */
    public static function bytes_unformat($size)
    {
        if (substr($size, -1) == 'G') {
            return $size * self::TRAFFIC_CARRY * self::TRAFFIC_CARRY * self::TRAFFIC_CARRY;
        } elseif (substr($size, -1) == 'M') {
            return $size * self::TRAFFIC_CARRY * self::TRAFFIC_CARRY;
        } elseif (substr($size, -2) == 'KB') {
            return $size * self::TRAFFIC_CARRY;
        } else {
            return $size;
        }
    }

    /**
     * 格式化时间
     * @param $second int 秒
     * @return string
     */
    public static function seconds_format($second)
    {
        $h = floor($second / 3600);
        $m = floor(($second % 3600) / 60);
        $s = floor(($second % 3600) % 60);
        $out = "";
        if ($h > 0)
            $out = number_format($h, 0) . '小时' . $m . '分' . $s . '秒';
        else if ($m > 0)
            $out = $m . '分' . $s . '秒';
        else
            $out = $s . '秒';
        return $out;
    }

    /**
     * 截取utf-8字符串
     * @param $string
     * @param $length
     * @param string $etc
     * @return string
     */
    public static function truncate_utf8_string($string, $length, $etc = '...')
    {
        $result = '';
        $string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
        $strlen = strlen($string);
        for ($i = 0; (($i < $strlen) && ($length > 0)); $i++) {
            if ($number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0')) {
                if ($length < 1.0) {
                    break;
                }
                $result .= substr($string, $i, $number);
                $length -= 1.0;
                $i += $number - 1;
            } else {
                $result .= substr($string, $i, 1);
                $length -= 0.5;
            }
        }
        $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
        if ($i < $strlen) {
            $result .= $etc;
        }
        return $result;
    }

    /**
     * 按照当前页和页数 切割数组
     * @param $array
     * @param $currentPage
     * @param $pageSize
     * @return array
     */
    public static function cuttingArray($array, $currentPage, $pageSize)
    {
        $data = [];
        if (is_array($array)) {
            $total_num = count($array);
            $start = ($currentPage - 1) * $pageSize;
            if ($total_num < $currentPage * $pageSize) {
                $end = $total_num;
            } else {
                $end = $currentPage * $pageSize;
            }
            for ($i = $start; $i < $end; $i++) {
                array_push($data, $array[$i]);
            }
        }

        return $data;
    }

    /**
     * 判断数组中是否有某些key
     * @param $data array 数组
     * @param $must array 必须字段
     * @param bool $trim 是否使用trim
     * @return bool|mixed
     */
    public static function arrayIsset($data, $must, $trim = true)
    {
        if (!is_array($data) || !is_array($must)) {
            return false;
        }

        foreach ($must as $key) {
            if (isset($data[$key])) {
                if (!$trim) {
                    continue;
                } else if (trim($data[$key]) != '') {
                    continue;
                }
            }

            return false;
        }

        // 所有参数存在并且有效
        return true;
    }

    /**
     * 判断一个多维数组中键或值是否存在某个值
     * @param $value
     * @param $array
     * @return bool
     */
    public static function array_key_value_exists($value, $array)
    {
        foreach ($array as $key => $item) {
            if ($key === $value) {
                return true;
            }
            if (!is_array($item)) {
                if ($item === $value) {
                    return true;
                } else {
                    continue;
                }
            }

            //键或者值存在
            if (in_array($value, $item, true) || array_key_exists($value, $item)) {
                return true;
            }

            //递归调用
            if (self::array_key_value_exists($value, $item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 把数组按照指定键的顺序排序
     * @param $array array 需要排序的数组
     * @param $keys array 需要排序的数组的键
     * @param $flag bool $array中的key不在$keys中的数据处理是否保留
     * @return array 按照$keys中的顺序排序
     */
    public static function array_sort_by_keys($array, $keys, $flag = false)
    {
        $list = [];
        foreach ($keys as $one) {
            if (isset($array[$one])) {
                $list[$one] = ArrayHelper::remove($array, $one);
            }
        }

        return $flag ? ArrayHelper::merge($list, $array) : $list;
    }

    /**
     * 获取多维数组中所有指定键的值，返回一维数组
     * @param $value
     * @param $array
     * @return array
     */
    public static function get_array_values($value, $array)
    {
        $arr = [];
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $res = self::get_array_values($value, $item);
                if (!empty($res)) {
                    $arr = array_merge($arr, $res);
                }
            } elseif ($key === $value && !empty($item)) {
                $arr[] = $item;
            }
        }

        return $arr;
    }

    /**
     * 把一维数组根据指定字段转换成多维数组
     * @param $array array
     * @param $field string
     * @param bool $has_key
     * @return array
     */
    public static function array_to_multiple_by_index($array, $field, $has_key = true)
    {
        $list = [];
        foreach ($array as $k => $val) {
            if ($has_key) {
                $list[$val[$field]][$k] = $val;
            } else {
                $list[$val[$field]][] = $val;
            }
        }

        return $list;
    }

    /**
     * 从数组中找出满足条件的值
     * @param $array array 原数组
     * @param $condition array 条件['key1' => 'val1', 'key2' => 'val2', ['like/!=/not in', 'field', 'value']……]
     * @param $keep_key bool 保持key
     * @return array 满足条件的值组成的数组
     */
    public static function get_array_by_condition($array, $condition, $keep_key = true)
    {
        $arr = [];
        if (!is_array($array)) {
            return [];
        }

        if (empty($condition)) {
            return $array;
        }

        foreach ($array as $id => $one) {
            $flg = true;//满足条件标志，用来判断数据是否满足条件，如果有一个条件不满足，flg之置为false
            //and条件合并, ['and', $condition1, $condition2, ……]
            if (reset($condition) === 'and') {
                ArrayHelper::remove($condition, 0);
                foreach ($condition as $con) {
                    if ($array == []) {
                        break;
                    }
                    $array = self::get_array_by_condition($array, $con, $keep_key);
                }

                return $array;
            } //or条件查询, ['or', $condition1, $condition2, ……]
            elseif (reset($condition) === 'or') {
                ArrayHelper::remove($condition, 0);
                $arr = [];
                foreach ($condition as $con) {
                    $result = self::get_array_by_condition($array, $con);
                    foreach ($result as $key => $val) {
                        if (!in_array($key, array_keys($arr), true)) {
                            $arr[$key] = $val;
                        }
                    }
                }
                return $keep_key ? $arr : array_values($arr);
            } //特殊操作符'!=', '<=', '<', '>=', '>', 'like', 'not in'条件查询
            elseif (in_array(reset($condition), ['!=', '<=', '<', '>=', '>', 'like', 'not in'], true)) {
                list($operate, $field, $value) = $condition;
                switch ($operate) {
                    case '!='://不等于
                        if (!($one[$field] != $value)) {
                            $flg = false;
                        }
                        break;
                    case '<=':// 小于等于
                        if (!($one[$field] <= $value)) {
                            $flg = false;
                        }
                        break;
                    case '<':// 小于
                        if (!($one[$field] < $value)) {
                            $flg = false;
                        }
                        break;
                    case '>=':// 大于等于
                        if (!($one[$field] >= $value)) {
                            $flg = false;
                        }
                        break;
                    case '>':// 大于
                        if (!($one[$field] > $value)) {
                            $flg = false;
                        }
                        break;
                    case 'like'://模糊匹配
                        if (!(strpos($one[$field], $value) !== false)) {
                            $flg = false;
                        }
                        break;
                    case 'not in'://不在范围中
                        if (in_array($one[$field], is_array($value) ? $value : [$value], true)) {
                            $flg = false;
                        }
                        break;
                }
            } //特殊操作符between条件查询
            elseif (in_array(reset($condition), ['between'], true)) {
                list($operate, $field, $value1, $value2) = $condition;
                switch ($operate) {
                    case 'between'://在范围中，包括左右边界值
                        if (!(($one[$field] > $value1) && ($one[$field] < $value2))) {
                            $flg = false;
                        }
                        break;
                }
            } //条件为[$key => $val]形式
            else {
                foreach ($condition as $key => $val) {
                    if (is_array($val)) {//值为数组
                        if (!in_array(strval($one[$key]), $val)) {
                            $flg = false;
                        }
                    } else {//值为字符串
                        if (strval($one[$key]) != $val) {
                            $flg = false;
                        }
                    }
                }
            }

            //满足条件，则加入结果数组中
            if ($flg) {
                $arr[$id] = $one;
            }
        }
        return $keep_key ? $arr : array_values($arr);
    }

    /**
     * 把ID字符串转换成对应的名称字符串
     * @param array|string $ids ID传
     * @param array $map
     * @param string $glue
     * @return string
     */
    public static function convertIdsToNames($ids, $map, $glue = ',')
    {
        $ids = is_array($ids) ? $ids : explode($glue, $ids);
        foreach ($ids as $key => $val) {
            $ids[$key] = $map[$val];
        }
        return implode($glue, $ids);
    }

    /**
     * 根据php的$_SERVER['HTTP_USER_AGENT'] 中各种浏览器访问时所包含各个浏览器特定的字符串来判断是属于PC还是移动端
     * @author           discuz3x
     * @lastmodify    2014-04-09
     * @return  BOOL
     */
    public static function checkmobile()
    {
        global $_G;
        $mobile = [];
        //各个触控浏览器中$_SERVER['HTTP_USER_AGENT']所包含的字符串数组
        $touchbrowser_list = [
            'iphone',
            'android',
            'phone',
            'mobile',
            'wap',
            'netfront',
            'java',
            'opera mobi',
            'opera mini',
            'ucweb',
            'windows ce',
            'symbian',
            'series',
            'webos',
            'sony',
            'blackberry',
            'dopod',
            'nokia',
            'samsung',
            'palmsource',
            'xda',
            'pieplus',
            'meizu',
            'midp',
            'cldc',
            'motorola',
            'foma',
            'docomo',
            'up.browser',
            'up.link',
            'blazer',
            'helio',
            'hosin',
            'huawei',
            'novarra',
            'coolpad',
            'webos',
            'techfaith',
            'palmsource',
            'alcatel',
            'amoi',
            'ktouch',
            'nexian',
            'ericsson',
            'philips',
            'sagem',
            'wellcom',
            'bunjalloo',
            'maui',
            'smartphone',
            'iemobile',
            'spice',
            'bird',
            'zte-',
            'longcos',
            'pantech',
            'gionee',
            'portalmmm',
            'jig browser',
            'hiptop',
            'benq',
            'haier',
            '^lct',
            '320x320',
            '240x320',
            '176x220',
        ];
        //window手机浏览器数组【猜的】
        $mobilebrowser_list = ['windows phone'];
        //wap浏览器中$_SERVER['HTTP_USER_AGENT']所包含的字符串数组
        $wmlbrowser_list = [
            'cect',
            'compal',
            'ctl',
            'lg',
            'nec',
            'tcl',
            'alcatel',
            'ericsson',
            'bird',
            'daxian',
            'dbtel',
            'eastcom',
            'pantech',
            'dopod',
            'philips',
            'haier',
            'konka',
            'kejian',
            'lenovo',
            'benq',
            'mot',
            'soutec',
            'nokia',
            'sagem',
            'sgh',
            'sed',
            'capitel',
            'panasonic',
            'sonyericsson',
            'sharp',
            'amoi',
            'panda',
            'zte',
        ];
        $pad_list = ['pad', 'gt-p1000'];
        $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (self::dstrpos($useragent, $pad_list)) {
            return false;
        }
        if (($v = self::dstrpos($useragent, $mobilebrowser_list, true))) {
            $_G['mobile'] = $v;

            return '1';
        }
        if (($v = self::dstrpos($useragent, $touchbrowser_list, true))) {
            $_G['mobile'] = $v;

            return '2';
        }
        if (($v = self::dstrpos($useragent, $wmlbrowser_list))) {
            $_G['mobile'] = $v;

            return '3'; //wml版
        }
        $brower = ['mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop'];
        if (self::dstrpos($useragent, $brower)) return false;
        $_G['mobile'] = 'unknown';
        //对于未知类型的浏览器，通过$_GET['mobile']参数来决定是否是手机浏览器
        if (isset($_G['mobiletpl'][$_GET['mobile']])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断$arr中元素字符串是否有出现在$string中
     * @param  $string string $_SERVER['HTTP_USER_AGENT']
     * @param  $arr  array         各中浏览器$_SERVER['HTTP_USER_AGENT']中必定会包含的字符串
     * @param  $returnvalue bool 返回浏览器名称还是返回布尔值，true为返回浏览器名称，false为返回布尔值【默认】
     * @author           discuz3x
     * @lastmodify    2014-04-09
     * @return bool
     */
    public static function dstrpos($string, $arr, $returnvalue = false)
    {
        if (empty($string)) return false;
        foreach ((array)$arr as $v) {
            if (strpos($string, $v) !== false) {
                $return = $returnvalue ? $v : true;

                return $return;
            }
        }

        return false;
    }

    /**
     * 格式化日期
     * @param $unixTime
     * @return bool|string
     */
    public static function showDay($unixTime)
    {
        $day = date('Y-m-d', $unixTime);
        $today = date('Y-m-d', time());
        //今天
        if ($day == $today) {
            return 'Today';
        } //昨天
        else if ($day == date('Y-m-d', strtotime('-1 day'))) {
            return 'Yesterday';
        }
        //前天
        /*else if($day == date('Y-m-d', strtotime('-1 day'))){
            return '前天';
        }*/ //其他
        else {
            return $day;
        }

    }

    /**
     * 格式化金额
     * @param $money
     * @return string
     */
    public static function money_format($money)
    {
        return number_format($money, 2);
    }

    /**
     * 格式化时长
     * @param $second int 秒
     * @return string
     */
    public static function secondsFormat($second)
    {
        $d = floor($second / 86400);
        $h = floor(($second % 86400) / 3600);
        $m = floor(($second % 3600) / 60);
        $s = floor(($second % 3600) % 60);

        if ($d > 0) {
            $out = $d . '天' . $h . '小时' . $m . '分' . $s . '秒';
        } else if ($h > 0) {
            $out = $h . '小时' . $m . '分' . $s . '秒';
        } else if ($m > 0) {
            $out = $m . '分' . $s . '秒';
        } else {
            $out = $s . '秒';
        }

        return $out;
    }

    /**
     * 要求某个数组中是否存在某些参数
     * @param array $data 数据数组
     * @param array $keys 待检验的键
     * @return bool
     * @throws yii\web\HttpException
     */
    public static function needParams($data, $keys)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new yii\web\HttpException(200, '缺少参数：' . $key, 1);
            }
        }

        return true;
    }

    /**
     * 时间处理函数，有日期，时间，周
     * 日期分为 今天，昨天，前天，普通日期2015-01-20
     * 时间分为：在今天12小时内：xx秒前，xx分钟前，xx小时前，其他都显示时间格式：08:30:10
     * 周：直接显示周
     * @param $unixTime
     * @param null $type
     * @return array|mixed
     */
    public static function showDateTime($unixTime, $type = null)
    {
        $arr = [
            'time' => self::showTime($unixTime),
            'day' => self::showDay($unixTime),
            'week' => self::showWeek($unixTime),
        ];
        if ($type != null && isset($arr[$type])) {
            return $arr[$type];
        } else {
            return $arr;
        }
    }

    /**
     * 时间处理
     * 时间分为：在今天12小时内：xx秒前，xx分钟前，xx小时前，其他都显示时间格式：08:30:10
     * @param $unixTime
     * @return string
     */
    public static function showTime($unixTime)
    {
        $value = time() - $unixTime;
        //12小时内
        if ($value < 43200) {
            if ($value < 60) {
                $time = $value . '秒前';
            } elseif ($value < 3600) {
                $time = floor($value / 60) . '分钟前';
            } else {
                $time = floor($value / 3600) . '小时前';
            }
        } else {
            if (date('Y-m-d', time()) == date('Y-m-d', $unixTime)) {
                $time = '今天' . date('H:i', $unixTime);
            } elseif (date('Y-m-d', time() - 3600 * 24) == date('Y-m-d', $unixTime)) {
                $time = '昨天' . date('H:i', $unixTime);
            } else {
                $time = date('Y-m-d H:i', $unixTime);
            }
        }

        return $time;
    }

    /**
     * 判断某一天是星期几
     * @param string $unixTime
     * @return string
     */
    public static function showWeek($unixTime = '', $type = 0)
    {
        $unixTime = is_numeric($unixTime) ? $unixTime : time();
        $weekArray = [
            '0' => ['周日', '周一', '周二', '周三', '周四', '周五', '周六'],
            '1' => ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六']
        ];

        return $weekArray[$type][date('w', $unixTime)];
    }

    /**
     * 把秒转换成 X小时X分X秒 格式
     * @param $second
     * @return string
     */
    public static function ftime($second)
    {
        $d = floor($second / 86400);
        $h = floor(($second % 86400) / 3600);
        $m = floor(($second % 3600) / 60);
        $s = floor($second % 60);
        $out = '';
        if ($d > 0) {
            $out .= $d . "天";
        }
        if ($h > 0) {
            $out .= $h . "小时";
        }
        if ($m > 0) {
            $out .= $m . (($h == 0 && $s == 0) ? "分钟" : "分");
        }
        if ($s > 0) {
            $out .= $s . "秒";
        }

        return $out;
    }

    /**
     * 显示微秒时间
     * @param null $mtime
     * @return mixed
     */
    public static function showMicrotime($mtime = null)
    {
        if ($mtime == null) {
            $mtime = microtime();
        }

        list($t1, $t2) = explode(' ', $mtime);
        return $t2 + $t1;
    }

    /**
     * 删除指定文件夹和内部所有文件
     * @param $dirName
     * @param bool $rmSelf 是否删除当前文件夹
     * @return bool
     */
    public static function removeDir($dirName, $rmSelf = true)
    {
        if (!is_dir($dirName)) {
            return false;
        }
        $handle = @opendir($dirName);
        while (($file = @readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                $dir = $dirName . '/' . $file;
                is_dir($dir) ? self::removeDir($dir) : @unlink($dir);
            }
        }
        closedir($handle);

        return $rmSelf ? rmdir($dirName) : true;
    }

    /**
     * 根据文件后缀返回文件图标名
     * @param $type
     * @return int|string
     */
    public static function getFileType($type)
    {
        foreach (self::$fileTypes as $key => $val) {
            if (in_array($type, $val, true)) {
                return $key;
            }
        }

        return 'unknown';
    }

    /**
     * 最全的PHP汉字转拼音函数 （共25961字，包括 20902基本字 + 5059生僻字）
     * @param $s
     * @param bool $isfirst
     * @return string
     */
    public static function pinyin($s, $isfirst = false)
    {
        static $pinyins;

        $s = trim($s);
        $len = strlen($s);
        if ($len < 3) return $s;

        if (!isset($pinyins)) {
            $data = file_get_contents(__DIR__ . '/pinyin.txt');
            $a1 = explode('|', $data);
            $pinyins = [];
            foreach ($a1 as $v) {
                $a2 = explode(':', $v);
                $pinyins[$a2[0]] = $a2[1];
            }
        }

        $rs = '';
        for ($i = 0; $i < $len; $i++) {
            $o = ord($s[$i]);
            if ($o < 0x80) {
                if (($o >= 48 && $o <= 57) || ($o >= 97 && $o <= 122)) {
                    $rs .= $s[$i]; // 0-9 a-z
                } elseif ($o >= 65 && $o <= 90) {
                    $rs .= strtolower($s[$i]); // A-Z
                } else {
                    $rs .= '_';
                }
            } else {
                $z = $s[$i] . $s[++$i] . $s[++$i];
                if (isset($pinyins[$z])) {
                    $rs .= $isfirst ? $pinyins[$z][0] : $pinyins[$z];
                } else {
                    $rs .= '_';
                }
            }
        }

        return $rs;
    }

    /**
     * 把数据转换成数字(如果数据是带有数字的字符串，截取后面的数字位，如U1返回1；如果最后几位不是数字，返回0)
     * @param string $val
     * @return int
     */
    public static function toIntger($val)
    {
        if (empty($val)) {
            return 0;
        }
        if (is_numeric($val)) {
            return (int)$val;
        }

        return self::toIntger(substr($val, 1));
    }

    /**
     * 把用户选择器返回的数组转换成ID数组或者字符串
     * @param array|string $list 用户选择器返回的数据，如['U1', 'U2', 'U3', 'U4'……]或U1,U2,U3,U4……
     * @param int $backType 返回类型；0：返回implode连接后的字符串；1：返回处理后的ID数组
     * @param string $glue implode函数的连接字符串，默认为','
     * @return array|string 返回值
     */
    public static function convertIds($list, $backType = 0, $glue = ',')
    {
        $arr = [];

        $list = is_array($list) ? $list : explode(',', $list);

        foreach ($list as $val) {
            $arr[] = self::toIntger($val);
        }

        if ($backType == 0) {
            return implode($glue, $arr);
        } else {
            return $arr;
        }
    }

    /**
     * 格式化时长
     * @param $second int 秒
     * @return string
     */
    public static function videoSecond($second)
    {
        $h = floor(($second) / 3600);
        $m = floor(($second % 3600) / 60);
        $s = floor(($second % 3600) % 60);

        $h = $h < 10 ? '0' . $h : $h;
        $m = $m < 10 ? '0' . $m : $m;
        $s = $s < 10 ? '0' . $s : $s;

        return $h . ':' . $m . ':' . $s;
    }

    /**
     * 把内容实时输出到浏览器
     * @param $content
     */
    public static function output($content)
    {
        echo str_repeat(" ", 1024);
        echo "$content";
        ob_flush();
        flush();
    }

    /**
     * 获取操作系统名称
     * @return string
     */
    public static function getSystemName()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $systemName = 'windows';
        } else {
            $systemName = 'linux';
        }

        return $systemName;
    }

    /* 将一个字符串转变成键值对数组
     * @param string str 要处理的字符串 $str ='TranAbbr=IPER|AcqSsn=000000073601|MercDtTm=20090615144037';
     * @param string sp 键值对分隔符
     * @param string kv 键值分隔符
     * @return  array
     */
    public static function strToArr($str,$sp="&",$kv="=")
    {
        $arr = str_replace(array($kv,$sp),array('"=>"','","'),'array("'.$str.'")');
        eval("\$arr"." = $arr;");   // 把字符串作为PHP代码执行
        return $arr;
    }

    /**
     * 将图片转化为base64
     *
     * @param string $image_file 图片
     * return string
     */
    public static function base64EncodeImage($image_file)
    {
        $base64_image = '';
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }

    /**
     * 获取路径下的所有文件
     *
     * @param $folder
     * @return array
     *
     */
    public static function getDirFiles($folder)
    {
        $filesArr = array();
        if (is_dir($folder)) {
            $hander = opendir($folder);
            while ($file = readdir($hander)) {
                //print_r($file);
                if ($file == '.' || $file == '..') {
                    continue;
                } elseif (is_file($folder . '/' . $file)) {
                    $filesArr[] = $file;
                } elseif (is_dir($folder . '/' . $file)) {
                    $filesArr[$file] = self::getDirFiles($folder . '/' . $file);
                }
            }
        }
        return $filesArr;
    }
}
