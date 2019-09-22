<?php
/**
 * 数组批量转码
 * User: ligang
 * Date: 2016/4/1
 * Time: 17:13
 */

namespace system\core\utils;


class IconvHelper
{
    static protected $in;
    static protected $out;

    /**
     * 静态方法,该方法将输入的数组递归的转换成对应编码的数组
     * @param array $array 输入的数组
     * @param string $in 输入数组的编码
     * @param string $out 返回数组的编码
     * @return array 返回的数组
     */
    static public function Conversion($array, $in, $out)
    {
        self::$in=$in;
        self::$out=$out;
        return self::arraymyicov($array);
    }
    /**
     * 内部方法,循环数组
     *
     * @param array $array
     * @return array
     */
    static private function arraymyicov($array)
    {
        foreach ($array as $key=>$value)
        {
            $key=self::myiconv($key);
            if (!is_array($value)) {
                $value=self::myiconv($value);
            }else {
                $value=self::arraymyicov($value);
            }
            $temparray[$key]=$value;
        }
        return $temparray;
    }

    /**
     * 替换数组编码
     *
     * @param string $str
     * @return string
     */
    static private function myiconv($str)
    {
        return @iconv(self::$in, self::$out, $str);
    }
}