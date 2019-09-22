<?php
/**
 * Created by PhpStorm.
 * User: THINKPAD
 * Date: 2017/8/11
 * Time: 10:47
 */

namespace system\modules\main\controllers;

use system\core\utils\Excel;
use system\core\utils\Tool;
use yii\helpers\ArrayHelper;
use yii;

class BatchOperationController extends BaseController
{
    public $disableCsrfAction = ['upload'];
    
    //忽略权限
    public $dependIgnoreList = [
        'main/batch-operation/upload' => [
            'main/batch-operation/index',
        ],
    ];
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'upload' => [
                'class' => \system\modules\main\extend\Upload::className(),
                'dir' => 'main/batch-operation/' . date('Y') . '/' . date('m') . '/' . date('d'),
            ],
        ];
    }
    
    /**
     * 批量处理
     */
    public function actionIndex($action = 'create')
    {
        $params = Yii::$app->request->post();
        if (!empty($params)) {
            $model = ArrayHelper::getValue($params, 'model');
            $constraintField = ArrayHelper::getValue($params, 'constraint_field');
            $fields = ArrayHelper::getValue($params, 'fields', []);
            $isClear = ArrayHelper::getValue($params, 'is_clear', 0);
            $isContinue = ArrayHelper::getValue($params, 'is_continue', 0);
            $file = ArrayHelper::getValue($params, 'file');
            if (!empty($file)) {
                if (file_exists(Yii::getAlias('@webroot').$file)) {
                    $arr = Excel::set_file(Yii::getAlias('@webroot').$file);
                    $execl_data = array_splice($arr[0], 1);
                    $res = $model::batchOperate($execl_data, $action, $constraintField, $fields, $isClear, $isContinue);
                    if ($res) {
                        return $this->ajaxReturn([
                            'code' => 0,
                            'msg' => '处理成功',
                        ]);
                    } else {
                        return $this->ajaxReturn([
                            'code' => 1,
                            'msg' => '处理失败！error：' . $res,
                        ]);
                    }
                } else {
                    return $this->ajaxReturn([
                        'code' => 1,
                        'msg' => '上传文件失败',
                    ]);
                }
            } else {
                return $this->ajaxReturn([
                    'code' => 1,
                    'msg' => '请上传数据文件',
                ]);
            }
        }
    
        return $this->render('index', [
            'action' => $action,
            'params' => $params
        ]);
    }
    
    /**
     * AJAX操作
     * @return string|yii\web\Response
     */
    public function actionAjax()
    {
        $params = ArrayHelper::merge(Yii::$app->request->post(), Yii::$app->request->queryParams);
        $action = ArrayHelper::getValue($params, 'action');
        
        switch ($action) {
            case 'get-fields':
                $model = ArrayHelper::getValue($params, 'model');
                $operate_fields = $model::$batch_operate_fields;
                if (empty($operate_fields)) {
                    $one = new $model;
                    $operate_fields = $one->attributeLabels();
                }

                $key = 'batch:' . $model . ':' . Yii::$app->user->id;
                $cache = Yii::$app->systemOption->getValue($key);
                $cache = ArrayHelper::toArray($cache);
                if (isset($cache['list'])) {
                    $operate_fields = Tool::array_sort_by_keys($operate_fields, $cache['list']);
                }
                return $this->ajaxReturn([
                    'code' => 0,
                    'data' => $operate_fields,
                    'cache' => $cache
                ]);
                break;
            case 'down-template':
                $table = ArrayHelper::getValue($params, 'table');
                $field_names = ArrayHelper::getValue($params, 'field_names');
                
                return $this->ajaxReturn([
                    'code' => 0,
                    'data' => yii\helpers\Url::to(['down-template', 'table' => $table, 'field_names' => $field_names])
                ]);
            case 'set-cache':
                $model = ArrayHelper::getValue($params, 'model');
                $constraint = ArrayHelper::getValue($params, 'constraint');
                $list = ArrayHelper::getValue($params, 'list');
                $checked = ArrayHelper::getValue($params, 'checked');

                $key = 'batch:' . $model . ':' . Yii::$app->user->id;
                Yii::$app->systemOption->setValue($key, [
                    'constraint' => $constraint,
                    'list' => $list,
                    'checked' => $checked,
                ]);

                return $this->getAjaxReturn(true);
            default:
                return false;
        }
    }
    
    /**
     * 下载模板
     */
    public function actionDownTemplate()
    {
        $field_names = Yii::$app->request->get('field_names');
        $table = Yii::$app->request->get('table');
    
        $this->downTemplate($field_names, $table);
    }
    
    /**
     * excel写入
     * @param $activeSheetIndex
     * @param $tableName
     */
    private function downTemplate($activeSheetIndex,$tableName)
    {
        $phpExcel = new \PHPExcel();
        //设置表头
        $zm = array();
        for($i='A';$i<='Z';$i++){
            $zm[]=$i;
        }
        foreach ($activeSheetIndex AS $k=>$value) {
            $phpExcel->setActiveSheetIndex()->setCellValue($zm[$k].'1', $value);
        }
        @ob_end_clean();
        header('Content-Type : application/vnd.ms-excel');

        //设置输出文件名及格式
        header('Content-Disposition:attachment;filename="'.$tableName.''.date("YmdHis").'.xls"');

        //导出.xls格式的话使用Excel5,若是想导出.xlsx需要使用Excel2007
        $objWriter= \PHPExcel_IOFactory::createWriter($phpExcel,'Excel5');
        $objWriter->save('php://output');
        ob_end_flush();

        //清空数据缓存
        unset($data);
        exit;
    }
}