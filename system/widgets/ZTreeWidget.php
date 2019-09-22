<?php
/**
 * Created by PhpStorm.
 * User: luobo
 * Date: 17/5/5
 * Time: 上午11:49
 */

namespace system\widgets;

use yii\bootstrap\Widget;

class ZTreeWidget extends Widget
{
	//模型对象
	public $model = null;
	//指定的属性
	public $attribute = null;
    // 容器id
    public $divId = 'group';
    // 容器div的附加
    public $divOption = 'style="max-height: 200px; overflow-y: auto;"';
    // 要选中的note_id
    public $note_id = null;
    // input的name
    public $inputName = 'group_id';
    // 是否多选; 当自定义onSelect回调函数后此选项无效
    public $isMulti = false;
    // 获取ajax数据的url
    public $getUrl = null;
    // 更新节点的Url
    public $updateUrl = false;
	// 自定义点异步加载成功回调函数
	public $onAsyncSuccess = null;
	// 用于对 Ajax 返回数据进行预处理的函数
	public $dataFilter = null;
    // 自定义回调函数
    public $onSelect = null;
    // 是否展开节点
	public $isExpand = false;
    // 操作权限['add' => false, 'edit' => false, 'del' => false]
    public $permission = [];
    /**
     * @inheritDoc
     */
    public function run()
    {
        parent::run();
	
	    if ($this->model != null) {
	    	$attribute = $this->attribute;
			$this->note_id = $this->model->$attribute;
		    $this->inputName = $attribute;
	    }
	
	    return $this->render('ztree', [
            'id' => $this->divId, // 容器id
            'note_id' => $this->note_id, // 当前选中的groupid
            'inputName' => $this->inputName,
            'isMulti' => $this->isMulti,
            'getUrl' => $this->getUrl,
            'updateUrl' => $this->updateUrl,
            'divOption' => $this->divOption,
            'onAsyncSuccess' => $this->onAsyncSuccess,
            'dataFilter' => $this->dataFilter,
            'onSelect' => $this->onSelect,
            'isExpand' => $this->isExpand,
            'permission' => $this->permission,
        ]);
    }
}