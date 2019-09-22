<?php
/**
 * Created by PhpStorm.
 * User: luobo
 * Date: 2017-4-18
 * Time: 15:48
 */

namespace system\modules\main\extend;

use system\core\utils\Tool;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\UploadedFile;
use yii\base\Action;
use Yii;

/**
 * 上传文件
 * Class Upload
 * @package system\extend
 */
class Upload extends Action
{
	public $dir = null;                              // 存储的路径

    public $baseDir = '@webroot/upload/';            // 基础目录
    public $baseUrl = '@web/upload/';                // 基础url
    //public $saveDir = '';
    public $fileInput = 'file';                     // file控件的名称
    public $fileName = '';                          // 上传文件的名称
    public $fileNameType = 'range';                 // 原始名称；ori原始，range：随机
    public $resetSize = 0;                          // 图片最大尺寸，0代表不压缩
    public $quality = 0;                            // 压缩后的图片质量，0-100，100最高，但是图片更大
    public $ext = ['jpeg', 'jpg', 'png', 'gif'];    // 允许的后缀名称
    public $returnPath = 'relativePath';            // 使用的返回路径
    public $returnCallback = '';                    // 返回时的回调
	
	/**
	 * Runs the action.
	 */
	public function run()
	{
        $params = ArrayHelper::merge(Yii::$app->request->post(), Yii::$app->request->queryParams);
		$files = UploadedFile::getInstancesByName(ArrayHelper::getValue($params, 'field', 'file'));
		// 是否保留附件原名（0：不保存；1：保存）
		$flag = ArrayHelper::getValue($params, 'flag', 0);
		$isIe8 = ArrayHelper::getValue($params, 'isIe8', false);
		$dir = ArrayHelper::getValue($params, 'dir', $this->dir);
		$data = [];

		if ($isIe8) {
		    header("Content-Type: text/html");
        }

		foreach ($files as $file) {
			if (is_object($file)) {
				$type = substr($file->name, strrpos($file->name, '.') + 1);
				if (SaveUpload::saveFile($file, [
				    'dir' => $dir,
                    'fileName' => $flag ? null : iconv('utf-8', 'gb2312//IGNORE', $file->name),
                    'resetSize' => $this->resetSize,
                    'quality' => $this->quality,
                    'ext' => $this->ext,
                    //'fileName' => $this->fileName,
                    'fileNameType' => $this->fileNameType,
                    'baseDir' => $this->baseDir,
                    'baseUrl' => $this->baseUrl,
                ])) {
					$arr['src'] = @iconv('gb2312', 'utf-8//IGNORE', SaveUpload::$relativePath);
					$arr['name'] = $file->name;
					$arr['size'] = Tool::bytes_format(filesize(SaveUpload::$absolutePath));
					$arr['type'] = Tool::getFileType(strtolower($type));
					$data[] = $arr;
				} else {
					return Json::encode([
						'code' => 1,
						'msg' => $file->name . '保存失败！',
					]);
				}
			} else {
				return Json::encode([
					'code' => 1,
					'msg' => '无效文件',
				]);
			}
		}
		return Json::encode([
			'code' => 0,
			'msg' => 'success',
			'data' => $data
		]);
	}

}