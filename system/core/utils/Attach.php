<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/4/1
 * Time: 上午10:26
 */

namespace system\core\utils;

use yii;

/**
 * Class Attach 上传管理
 * @package system\core\utils
 */
class Attach
{
    //配置
    public static $dir; //文件所在目录

    /* @var $fileObj yii\web\UploadedFile */
    public static $fileObj = null;  // 上传文件的对象

    //上传完成后生成的信息
    public static $absolutePath;    //文件生成的绝对路径
    public static $relativePath;    //文件生成的相对路径
    public static $aliasPath;    //文件生成的相对路径
    public static $absoluteUrl;     //文件的绝对url，如果是保存到远程，那么需要存储绝对路径
    public static $fileName;        //文件的名称
    public static $ext;             //文件的后缀

    // 保存错误消息
    public static $error;

    /**
     * 保存上传的图片
     * @param $fileObj yii\web\UploadedFile uploadedFile对象
     * @param array $config 配置参数
     * 包含以下的键：
     *  -ext: 扩展名数组，比如如果是图片，那么jpg,jpeg,png,gif 等
     *  -resetSize：是否要缩放图片，设置后会缩放图片，比如设置500，代表图片最宽500，高度按比例缩放；默认不缩放；
     *  -quality：此参数依赖于resetSize参数，代表在缩放后图片的质量，取值范围：0-100，默认80；
     *  -dir：文件的保存的文件夹，比如：news； 默认以年月命名，比如：201512
     *  -fileName：文件的名称；默认随机生成
     * @return bool
     */
    public static function saveUpload($fileObj, $config = [])
    {
        if (!($fileObj instanceof yii\web\UploadedFile)) {
            throw new yii\base\InvalidParamException('fileObj参数必须是UploadedFile的实例对象');
        }

        self::$fileObj = $fileObj;

        //文件扩展名
        self::$ext = $fileObj->getExtension();

        if (isset($config['ext']) && $config['ext']) {
            if (!is_array($config['ext'])) {
                self::$error = 'ext参数必须是一维数组';
                return false;
            }

            if (!in_array(self::$ext, $config['ext'])) {
                self::$error = "扩展名:" . self::$ext . "不支持";
                return false;
            }
        }

        //生成文件名称
        if (!self::generateFileName($config)) {
            return false;
        }

        //保存文件
        if (!$fileObj->saveAs(self::$absolutePath)) {
            return false;
        }

        // 如果是图片格式的
        if (in_array(self::$ext, ['jpg', 'gif', 'jpeg', 'png'])) {
            self::_resetImgSize($config);
        }

        // 上传成功以后进行后续处理
        self::afterUpload(self::$relativePath);

        return true;
    }

    /**
     * 抓取指定url的图片到本地
     * @param $url string 图片url，包括http形式的图片和data:type/... 形式的图片
     * @param array $config 配置数组，参照saveUpload
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
            if (!in_array(strtolower($ext), ['.gif', '.jpg', 'jpeg', '.png', '.bmp'])) {
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
        self::_resetImgSize($config);

        return true;
    }

    /**
     * 生成图片的路径
     * @param array $config 配置数组，用到了dir和fileName，代表文件夹和文件名称
     * @return bool
     * @throws yii\base\Exception
     */
    public static function generateFileName($config = [])
    {
        $dir = isset($config['dir']) ? $config['dir'] : null;
        $fileName = isset($config['fileName']) ? $config['fileName'] : null;

        //文件名称
        if (!$fileName) {
            if ($config['fileNameType'] == 'ori') {
                self::$fileName = self::$fileObj->name;
            } else {
                // 随机名称
                self::$fileName = $fileName ?: time() . rand(100000, 999999) . '.' . self::$ext;
            }
        } else {
            self::$fileName = $fileName;
        }

        //文件夹名称
        self::$dir = $dir ?: 'unknown/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
        //绝对目录
        $absoluteDir = Yii::getAlias($config['baseDir']) . self::$dir;
        //创建文件所在的目录
        if (!yii\helpers\FileHelper::createDirectory($absoluteDir)) {
            return false;
        }


        //文件别名路径
        self::$aliasPath = $config['baseUrl'] . self::$dir . self::$fileName;
        //文件相对路径
        self::$relativePath = Yii::getAlias($config['baseUrl']) . self::$dir . self::$fileName;
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
    private static function _resetImgSize($config = [])
    {
        //是否要缩放图片，如果缩放图片出错，程序也不会停止，会继续进行，因为缩放只是附加功能，实际图片已经上传成功了
        if (isset($config['resetSize']) && $config['resetSize'] > 0) {
            //重新计算图像的大小并且等比例 压缩图像; 封面图片可以小一点儿
            $newImgSize = self::resetImgSize(self::$absolutePath, $config['resetSize']);

            if ($newImgSize) {
                // 压缩图片
                $image = \yii\imagine\Image::thumbnail(self::$absolutePath, $newImgSize['width'], $newImgSize['height']);
                // 压缩质量
                if (isset($config['quality']) && $config['quality'] > 0 && $config['quality'] <= 100) {
                    $image->save(self::$absolutePath, ['quality' => $config['quality']]);
                } else {
                    $image->save();
                }
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
        if (isset(Yii::$app->params['safe_directory'])) {
            $safe_directory = Yii::$app->params['safe_directory'];
            $filename = base64_encode($path);
            //拷贝一份到指定的目录
            copy(Yii::getAlias('@webroot') . $path, Yii::getAlias($safe_directory['sender']) . $filename);
        }
    }

    /**
     * 获取图片尺寸，可以解决从获取获取图片时特别 getimagesize 特别慢的问题
     * @param $img_loc
     * @return bool
     * @see  http://php.net/manual/en/function.getimagesize.php
     */
    public static function getJpegSize($img_loc) {
        $handle = fopen($img_loc, "rb") or die("Invalid file stream.");
        $new_block = NULL;
        if(!feof($handle)) {
            $new_block = fread($handle, 32);
            $i = 0;
            if($new_block[$i]=="\xFF" && $new_block[$i+1]=="\xD8" && $new_block[$i+2]=="\xFF" && $new_block[$i+3]=="\xE0") {
                $i += 4;
                if($new_block[$i+2]=="\x4A" && $new_block[$i+3]=="\x46" && $new_block[$i+4]=="\x49" && $new_block[$i+5]=="\x46" && $new_block[$i+6]=="\x00") {
                    // Read block size and skip ahead to begin cycling through blocks in search of SOF marker
                    $block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
                    $block_size = hexdec($block_size[1]);
                    while(!feof($handle)) {
                        $i += $block_size;
                        $new_block .= fread($handle, $block_size);
                        if($new_block[$i]=="\xFF") {
                            // New block detected, check for SOF marker
                            $sof_marker = array("\xC0", "\xC1", "\xC2", "\xC3", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCD", "\xCE", "\xCF");
                            if(in_array($new_block[$i+1], $sof_marker)) {
                                // SOF marker detected. Width and height information is contained in bytes 4-7 after this byte.
                                $size_data = $new_block[$i+2] . $new_block[$i+3] . $new_block[$i+4] . $new_block[$i+5] . $new_block[$i+6] . $new_block[$i+7] . $new_block[$i+8];
                                $unpacked = unpack("H*", $size_data);
                                $unpacked = $unpacked[1];
                                $height = hexdec($unpacked[6] . $unpacked[7] . $unpacked[8] . $unpacked[9]);
                                $width = hexdec($unpacked[10] . $unpacked[11] . $unpacked[12] . $unpacked[13]);
                                return array($width, $height);
                            } else {
                                // Skip block marker and read block size
                                $i += 2;
                                $block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
                                $block_size = hexdec($block_size[1]);
                            }
                        } else {
                            return FALSE;
                        }
                    }
                }
            }
        }
        return FALSE;
    }

    /**
     * 等比例缩放图片尺寸
     * @param $fileName string 原始尺寸
     * @param $max int 期待的最大尺寸
     * @return array|bool 返回实际的尺寸
     */
    public static function resetImgSize($fileName, $max = 500)
    {
        //if (false === ($imageInfo = self::getJpegSize($fileName))) {
        if (false === ($imageInfo = getimagesize($fileName))) {
            return false;
        }

        list($width, $height) = $imageInfo;

        if ($width == 0 || $height == 0) {
            return false;
        }

        // 原始图片小于最大尺寸，则无需压缩
        if ($width <= $max && $height <= $max) {
            return false;
            /*return [
                'width' => $width,
                'height' => $height,
            ];*/
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
            'height' => floor($height)
        ];
    }
}