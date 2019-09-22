<?php
/**
 * 文件上传
 * User: ligang
 * Date: 2015/12/17
 * Time: 14:37
 */

namespace system\modules\main\extend;

use system\core\utils\Tool;
use yii;

/**
 * 保存文件类，包括文件上传和抓取
 * 可以指定上传文件的地址以及文件名，指定是否缩放，以及图片最大尺寸，设置图片质量，是否抓取远程图片到本地
 * 可以获得处理后的图片名称，图片大小，路径，url等信息
 * Class UploadPicture
 * @package common\extend
 */
class SaveUpload
{
    //配置
    public static $dir; //文件所在目录

    //上传完成后生成的信息
    public static $absolutePath; //文件生成的绝对路径
    public static $relativePath; //文件生成的相对路径
    public static $absoluteUrl; //文件的绝对url，如果是保存到远程，那么需要存储绝对路径
    public static $fileName;    //文件的名称
    public static $ext; //文件的后缀
    public static $fileObj; // 文件类

    //错误信息
    //public static $error;

    /**
     * 保存上传的文件
     * @param $fileObj yii\web\UploadedFile uploadedFile对象
     * @param array $config 配置参数
     * 包含以下的键：
     *  -resetSize：是否要缩放图片，设置后会缩放图片，比如设置500，代表图片最宽500，高度按比例缩放；默认不缩放；
     *  -quality：此参数依赖于resetSize参数，代表在缩放后图片的质量，取值范围：0-100，默认80；
     *  -dir：文件的保存的文件夹，比如：news； 默认以年月命名，比如：201512
     *  -fileName：文件的名称；默认随机生成
     * @return bool
     */
    public static function saveFile($fileObj, $config = [])
    {
        if (!($fileObj instanceof yii\web\UploadedFile)) {
            return false;
        }

        self::$fileObj = $fileObj;

        //文件扩展名
        self::$ext = $fileObj->getExtension();

        /*if (!in_array(strtolower(self::$ext), ['jpg', 'jpeg', 'png', 'gif'])) {
            return false;
        }*/

        //生成文件名称
        if (!self::generateFileName($config)) {
            return false;
        }

        //保存文件
        if (!$fileObj->saveAs(self::$absolutePath)) {
            return false;
        }

        self::_resetSize($config);

        // 上传成功以后进行后续处理
        self::afterUpload(self::$relativePath);

        return true;
    }

    /**
     * 抓取指定url的图片到本地
     * @param $url string 图片url，包括http形式的图片和data:type/... 形式的图片
     * @param array $config 配置数组，参照saveFile
     * @return bool
     */
    public static function fetchPicture($url, $config = [])
    {
        if ($url == "") {
            return false;
        }

        //特殊的图片格式，data:image/png;base64, 共有：gif,jpeg,png三种，x-icon不处理
        if (preg_match('/data:image\/(.*?);base64,/i', $url, $match)) {
            self::$ext = $match[1];
            $base64 = substr($url, strlen($match[0]));
            //还原为二进制
            $img = base64_decode($base64);
        } //默认从远处抓取
        else {
            //判断图片url是否正确
            $ext = strtolower(strrchr($url, "."));
            if (!in_array(strtolower($ext), [
                '.gif',
                '.jpg',
                'jpeg',
                '.png',
                '.bmp'
            ])) {
                return false;
            }
            //文件扩展名
            self::$ext = substr($ext, 1);
            //抓取图片
            if (!$img = self::_fetchUrl($url)) {
                return false;
            }
        }

        //生成图片路径
        if (!self::generateFileName($config)) {
            return false;
        }

        //将图片写到本地
        $fp = @fopen(self::$absolutePath, "a");
        if (!$fp) {
            return false;
        }
        @fwrite($fp, $img);
        @fclose($fp);

        unset($img);
        unset($fp);

        //缩放图片
        self::_resetSize($config);

        return true;
    }

    /**
     * 生成文件的路径
     * @param array $config 配置数组，用到了dir和fileName，代表文件夹和文件名称
     * @return bool
     * @throws yii\base\Exception
     */
    public static function generateFileName($config = [])
    {
        $dir = isset($config['dir']) ? $config['dir'] : null;
        $fileName = isset($config['fileName']) ? $config['fileName'] : null;

        $type = in_array(self::$ext, ['jpg', 'png', 'gif', 'jpeg', 'bmp']) ? 'images' : 'files';
        //文件夹名称
        self::$dir = $dir ? $dir . '/' : $type . '/' . date('Y') . '/' . date('m') . '/';
        //绝对目录
        $absoluteDir = Yii::getAlias('@webroot') . '/upload/' . self::$dir;
        //创建文件所在的目录
        if (!yii\helpers\BaseFileHelper::createDirectory($absoluteDir)) {
            return false;
        }
        //文件名称
        self::$fileName = $fileName ? $fileName : time() . rand(100000, 999999) . '.' . self::$ext;
        //文件相对路径
        self::$relativePath = '/upload/' . self::$dir . self::$fileName;
        //文件绝对路径
        self::$absolutePath = $absoluteDir . self::$fileName;
        //文件的绝对url
        self::$absoluteUrl = self::$relativePath;

        return true;
    }

    /**
     * 保存上传的文件到指定路径
     * @param $tempName string 临时文件名称
     * @param $newFile string 新文件名称
     * @param bool $deleteTempFile 是否删除缓存文件
     * @return mixed
     */
    public static function saveAs($tempName, $newFile, $deleteTempFile = true)
    {
        if ($deleteTempFile) {
            return move_uploaded_file($tempName, $newFile);
        } elseif (is_uploaded_file($tempName)) {
            return copy($tempName, $newFile);
        }
    }

    /**
     * 缩放图片
     * @param array $config
     */
    private static function _resetSize($config = [])
    {
        //是否要缩放图片，如果尺寸大于10k并且设置了压缩参数才会进行压缩；如果缩放图片出错，程序也不会停止，会继续进行，因为缩放只是附加功能，实际图片已经上传成功了
        if (self::$fileObj->size > 10240 && isset($config['resetSize']) && $config['resetSize'] > 0) {
            //压缩后的图片质量
            $quality = (isset($config['quality']) && $config['quality'] > 0 && $config['quality'] <= 100) ? (int)$config['quality'] : 80;

            //重新计算图像的大小并且等比例 压缩图像; 封面图片可以小一点儿
            $newImgSize = Tool::resetImgSize(self::$absolutePath, $config['resetSize']);
            if ($newImgSize) {
                yii\imagine\Image::thumbnail(self::$absolutePath, $newImgSize['width'], $newImgSize['height'])->save(self::$absolutePath, ['quality' => $quality]);
            }
        }
    }

    /**
     * 抓取内容
     * @param $url
     * @return mixed
     */
    private static function _fetchUrl($url)
    {
        //抓取图片
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $img = curl_exec($ch);
        curl_close($ch);
        return $img;
    }

    /**
     * 文件上传完毕后调用
     * @param $path
     */
    public static function afterUpload($path)
    {

    }
}