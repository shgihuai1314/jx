<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/9/14
 * Time: 20:42
 */

namespace system\modules\main\widgets;

use yii\bootstrap\Widget;
use system\core\utils\Tool;

class FileViewWidget extends Widget
{
	// 附件值
	public $files = null;
	// 附件值类型(0：传入的是附件表中的ID；1：传入的是附件路径)
	public $flag = 0;
	// 允许下载
	public $canDownload = true;
    // 文档操作['view', 'edit', 'writing', 'useTemplate', 'print']编辑、批注、模板套红、打印
    public $wordOperate = [];

    public $mode = ''; // 模式，强制为手机或者pc的样式，不自动判断
	
	public function run()
	{
		$this->files = empty($this->files) ? [] : (is_array($this->files) ?  $this->files : explode(',', $this->files));

		return $this->render('files/' . ((Tool::checkmobile() || $this->mode == 'mobile') ? 'mobile/' : '') . 'view', [
			'files' => $this->files,
			'flag' => $this->flag,
			'canDownload' => $this->canDownload,
			'wordOperate' => $this->wordOperate,
		]);
	}
}