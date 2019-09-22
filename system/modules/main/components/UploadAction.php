<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/7/6
 * Time: 下午1:24
 */

namespace system\modules\main\components;

use system\core\utils\Attach;
use yii;
use yii\base\Action;
use yii\helpers\Json;

/**
 * 上传图片action，用法：
 * public function actions()
 * {
 * return [
 * // 上传logo图标
 * 'upload-logo' => [
 * 'class' => UploadAction::className(),   // 本类名称
 * 'fileInput' => 'logo',  // file字段的名称
 * 'saveDir' => 'app/ico', // 保存的路径
 * ]
 * ];
 * }
 * @package system\modules\main\components
 */
class UploadAction extends Action
{
    public $baseDir = '@webroot/upload/';            // 基础目录
    public $baseUrl = '@web/upload/';                // 基础url
    public $saveDir = '';                           // 存储的路径
    public $fileInput = 'file';                     // file控件的名称
    public $fileName = '';                          // 上传文件的名称
    public $fileNameType = 'range';                 // 原始名称；ori原始，range：随机
    public $resetSize = 0;                          // 图片最大尺寸，0代表不压缩
    public $quality = 0;                            // 压缩后的图片质量，0-100，100最高，但是图片更大
    public $ext = ['jpeg', 'jpg', 'png', 'gif'];    // 允许的后缀名称
    public $returnPath = 'relativePath';            // 使用的返回路径
    public $returnCallback = '';                    // 返回时的回调

    public function run()
    {
        $avatarFile = yii\web\UploadedFile::getInstanceByName($this->fileInput);

        if (!$avatarFile) {
            return Json::encode([
                'code' => 1,
                'message' => 'file参数错误'
            ]);
        }

        $res = Attach::saveUpload($avatarFile, [
            'dir' => $this->saveDir,
            'resetSize' => $this->resetSize,
            'quality' => $this->quality,
            'ext' => $this->ext,
            'fileName' => $this->fileName,
            'fileNameType' => $this->fileNameType,
            'baseDir' => $this->baseDir,
            'baseUrl' => $this->baseUrl,
        ]);

        if ($res) {
            $data = Json::encode([
                'code' => 0,
                'message' => '上传成功',
                'data' => [
                    'src' => Attach::${$this->returnPath}
                ],
            ]);
        } else {
            $data = Json::encode([
                'code' => 1,
                'message' => Attach::$error,
            ]);
        }

        if (is_callable($this->returnCallback)) {
            $data = call_user_func($this->returnCallback, $data);
        }

        return $data;
    }
}