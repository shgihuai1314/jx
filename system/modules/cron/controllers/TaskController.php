<?php

namespace system\modules\cron\controllers;

use system\modules\cron\models\Cron;
use system\modules\main\components\UploadAction;
use system\modules\cron\models\CronTasks;
use yii\helpers\ArrayHelper;
use Yii;

class TaskController extends BaseController
{
    public $disableCsrfAction = ['upload'];
    public $dependIgnoreValueList = [
        'cron/task/index' => ['*']
    ];

    public function actions()
    {
        return [
            'upload' => [
                'class' => UploadAction::className(),
                'baseDir' => '@extension',
                'baseUrl' => '@extension',
                'returnPath' => 'absolutePath',//返回的路径形式
                'saveDir' => '/cron/',
                'ext' => ['php'], // 支持的扩展名
                'fileNameType' => 'ori'
            ],
        ];
    }

    /**
     * 列表
     */
    public function actionIndex()
    {
        $data = CronTasks::find()->joinWith('cron')
            ->search(['module_id', 'name' => 'like'])
            ->paginate()
            ->orderBy(['sort' => SORT_DESC])
            ->all();

        Cron::getCronList();
        return $this->render('index', compact('data'));
    }

    /**
     * 添加
     * @return mixed|string
     */
    public function actionAdd()
    {
        $model = new CronTasks();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post(), '')) {
            $model->command = Yii::$app->request->post('command' . $model->type, '');
            $this->getSaveRes($model);
        }

        return $this->render('form', compact('model'));
    }

    /**
    * 编辑
    * @param $id integer
    * @return string
    */
    public function actionEdit($id)
    {
        if (Yii::$app->request->isAjax) {//如果是通过ajax编辑
            $params = Yii::$app->request->post();
            $model = $this->findModel($id);
            return $this->ajax($model,'edit', $params);
        }

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post(), '')) {
            $model->command = Yii::$app->request->post('command' . $model->type, '');
            $this->getSaveRes($model);
        }

        return $this->render('form', compact('model'));
    }

    /**
     * 删除
     */
    public function actionDel()
    {
        $params = Yii::$app->request->post();

        $model = new CronTasks();
        return $this->ajax($model, 'del', $params);
    }

    /**
    * 获取模型对象.
    * If the model is not found, a 404 HTTP exception will be thrown.
    * @param string $id
    * @return CronTasks the loaded model
    * @throws yii\web\NotFoundHttpException if the model cannot be found
    */
    protected function findModel($id)
    {
        if (($model = CronTasks::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}

