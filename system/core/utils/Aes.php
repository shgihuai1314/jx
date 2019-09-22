<?php
/**
 * yd-service.
 * User: ligang
 * Date: 2018/4/26 下午7:03
 */
namespace system\core\utils;

/**
 * Class Aes
 * Aes加密，解密
 * @package system\components
 */
class Aes
{
    /**
     * 加密
     * @param $data string 要加密的字符串
     * @param $password string 密码
     * @return string
     */
    public static function encrypt($data, $password)
    {
        $obj = new \phpseclib\Crypt\AES();
        $obj->setPassword($password);
        return base64_encode($obj->encrypt($data));
    }

    /**
     * 解密
     * @param $data string 要解密的字符串
     * @param $password string 密码
     * @return string
     */
    public static function decrypt($data, $password)
    {
        $obj = new \phpseclib\Crypt\AES();
        $obj->setPassword($password);
        return $obj->decrypt(base64_decode($data));
    }
}