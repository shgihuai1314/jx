<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/28
 * Time: 11:26
 */

namespace system\modules\main\widgets;

use system\core\utils\Tool;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Widget;

class FileUploadWidget extends Widget
{
	//模型对象
	public $model = null;
	//指定的属性
	public $attribute = null;
	// 参数数组
	public $item = null;
	// 提交的字段名
	public $inputName = 'file';
	// 默认文件
	public $files = null;
	// 附件值类型(0：传入的是附件表中的ID；1：传入的是附件路径)
	public $flag = 0;
	// 是否重置文件名
	public $resetName = true;
	// 操作权限
	public $permission = ['upload', 'download', 'delete'];
	// 文档操作
    // public $wordOperate = ['view', 'edit', 'writing', 'useTemplate', 'print'];
    public $wordOperate = [];

    public $mode = ''; // 强制规定手机或者pc模式

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();

        if ($this->model != null) {
            $attribute = $this->attribute;
            $this->files = $this->model->$attribute;
            $this->inputName = $attribute;
        }
    }

    public function run()
	{
		$accept = ArrayHelper::getValue($this->item, 'accept', 'images');
		switch ($accept) {
			case 'images'://图片
				$icon = ArrayHelper::getValue($this->item, 'icon', 'image');
				$title = ArrayHelper::getValue($this->item, 'title', '上传图片');
				break;
			case 'video'://视频
				$icon = ArrayHelper::getValue($this->item, 'icon', 'video-camera');
				$title = ArrayHelper::getValue($this->item, 'title', '上传视频');
				break;
			case 'audio'://音频
				$icon = ArrayHelper::getValue($this->item, 'icon', 'music');
				$title = ArrayHelper::getValue($this->item, 'title', '上传音乐');
				break;
			default://其他文件
				$icon = ArrayHelper::getValue($this->item, 'icon', 'paperclip');
				$title = ArrayHelper::getValue($this->item, 'title', '上传附件');
				break;
		}
		
		$params = [
			'url' => ArrayHelper::getValue($this->item, 'url', 'upload'),//服务端上传接口
			'accept' => $accept,//可选值有：images（图片）、file（所有文件）、video（视频）、audio（音频）
			'exts' => ArrayHelper::getValue($this->item, 'exts', null),//允许上传的文件后缀。一般结合 accept 参数类设定。假设 accept 为 file 类型时，那么你设置 exts: 'zip|rar|7z' 即代表只允许上传压缩格式的文件。如果 accept 未设定，那么限制的就是图片的文件格式
			'data' => ArrayHelper::getValue($this->item, 'data', []),//请求上传接口的额外参数
			'field' => ArrayHelper::getValue($this->item, 'field', 'file'),//设定文件域的字段名
			'multiple' => ArrayHelper::getValue($this->item, 'multiple', false),//是否允许多文件上传。设置 true即可开启。不支持ie8/9
			'done' => ArrayHelper::getValue($this->item, 'done', null),//执行上传请求后的回调。返回三个参数，分别为：res（服务端响应信息）、index（当前文件的索引）、upload（重新上传的方法，一般在文件上传失败后使用）
		];

		$button = [//上传按钮
			'id' => ArrayHelper::getValue($this->item, 'btnId', 'upload-btn'),//按钮ID，一个页面多个附件挂件必须要给按钮赋予ID
			'class' => ArrayHelper::getValue($this->item, 'btnClass', 'layui-btn-primary'),//按钮类名，可以给按钮定义样式
			'icon' => $icon,//按钮图标（fa字体）
			'title' => $title,//按钮标签
		];
		
		return $this->render('files/' . ((Tool::checkmobile() || $this->mode == 'mobile') ? 'mobile/' : '') . 'upload', [
			'button' => $button,
			'params' => $params,
			'inputName' => $this->inputName,
			'files' => $this->files,
			'flag' => $this->flag,
			'resetName' => $this->resetName,
			'permission' => $this->permission,
            'wordOperate' => $this->wordOperate
		]);
	}
}