<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/12/21
 * Time: 17:11
 */

namespace system\modules\main\components;

use yii\base\Action;
use Yii;
use yii\helpers\ArrayHelper;

class WebOfficeAction extends Action
{
    public $view = '@system/modules/main/views/file-info/office-view';
    //文档编辑状态
    public $EditType;
    //是否允许批注
    public $Writing;
    //是否允许套红
    public $Template;
    //是否允许打印
    public $Print;
    //文档名称
    public $FileName;
    //文档路径
    public $FilePath;
    
    public function run() {
        $params = Yii::$app->request->queryParams;
        
        $this->EditType = ArrayHelper::getValue($params, 'EditType', 0);
        $this->Writing = ArrayHelper::getValue($params, 'Writing', 0);
        $this->Template = ArrayHelper::getValue($params, 'Template', 0);
        $this->Print = ArrayHelper::getValue($params, 'Print', 0);
        $this->FileName = ArrayHelper::getValue($params, 'FileName', '');
        $this->FilePath = ArrayHelper::getValue($params, 'FilePath', '');
        
        $RecordId = explode('.', basename($this->FilePath))[0];
        $FileType = substr($this->FilePath, strrpos($this->FilePath, '.'));
        
        return $this->controller->renderPartial($this->view, [
            'EditType' => $this->EditType,
            'Writing' => $this->Writing,
            'Template' => $this->Template,
            'Print' => $this->Print,
            'FileName' => $this->FileName,
            'FilePath' => $this->FilePath,
            'FileType' => $FileType,
            'RecordId' => $RecordId,
        ]);
    }
}