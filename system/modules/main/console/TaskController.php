<?php
/**
 * 转码控制器
 */
namespace system\modules\main\console;

use yii\console\Controller;
use yii;

class TaskController extends Controller
{
    /**
     * 对上传的文件进行统一转码
     */
    public function actionTrans()
    {
        try{
            Yii::$app->systemTransFile->trans();
        }catch (\Throwable  $t) {
            echo $t->getMessage();
        }catch (yii\db\Exception $e) {
            echo $e->getMessage();
        }
    }
}