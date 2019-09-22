<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2018-3-21
 * Time: 11:43
 */

namespace system\modules\main\controllers;

use system\modules\main\models\Modules;
use Yii;
use system\modules\role\models\AuthAssign;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;


class GiiController extends BaseController
{
    public function init()
    {
        parent::init();
        // 模块管理只允许超级管理员操作
        if (!AuthAssign::isSuper(Yii::$app->user->getId())) {
            throw new yii\web\ForbiddenHttpException('只有超级管理员可以处理');
        }
    }

    /**
     * @param string $type 生成类型 model|controller
     * @return string
     */
    public function actionIndex()
    {
        $tables = Yii::$app->db->createCommand("SHOW TABLES")->queryColumn();
        $modules = Modules::getModuleMap();
        return $this->render('index', compact('tables', 'modules'));
    }

    /**
     * 预览文件内容
     */
    public function actionPreview()
    {
        $data = Yii::$app->request->post();
        $type = ArrayHelper::getValue($data, 'type', 'model');

        $code = $this->renderFile(Yii::getAlias('@system/modules/main/views/gii/views/' . $type . '.php'), compact('data'));

        echo str_replace('<?php', '&#60;?php', $code);
    }

    /**
     * 生成文件
     */
    public function actionGenerate()
    {
        $data = Yii::$app->request->post();
        $files = ArrayHelper::getValue($data, 'files', 'model');

        foreach (explode(',', $files) as $type) {
            if (in_array($type, ['model', 'controller', 'index', 'form'])) {
                $code = $this->renderFile(Yii::getAlias('@system/modules/main/views/gii/views/' . $type . '.php'), compact('data'));

                if ($type == 'model') {
                    $filePath = '@system/modules/' . $data['module'] . '/models/' . $data['model_class'] . '.php';
                } elseif ($type == 'controller') {
                    $filePath = '@system/modules/' . $data['module'] . '/controllers/' . ucfirst($data['controller_name']) . 'Controller.php';
                } else {
                    if (!is_dir(Yii::getAlias('@system/modules/' . $data['module'] . '/views/' . $data['controller_name']))) {
                        FileHelper::createDirectory(Yii::getAlias('@system/modules/' . $data['module'] . '/views/' . $data['controller_name']));
                    }
                    $filePath = '@system/modules/' . $data['module'] . '/views/' . $data['controller_name'] . '/' . $type . '.php';
                    $code = str_replace('&#60;', '<', str_replace('&#62;', '>', $code));
                }
                file_put_contents(Yii::getAlias($filePath), $code);
            }
        }

        return $this->getAjaxReturn(true);
    }

    /**
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionAjax()
    {
        $params = Yii::$app->request->queryParams;
        $action = ArrayHelper::getValue($params, 'action');

        switch ($action) {
            case 'tableInfo':
                $table = ArrayHelper::getValue($params, 'table');
                $tableInfo = Yii::$app->db->getTableSchema($table);

                return json_encode([
                    'data' => ArrayHelper::toArray($tableInfo)
                ]);
            case 'check-model':
                $class = ArrayHelper::getValue($params, 'modelClass');

                return json_encode([
                    'code' => class_exists($class) ? 1 : 0
                ]);
        }
    }
}