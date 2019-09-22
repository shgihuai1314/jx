<?php

namespace system\modules\cron\controllers;

use system\modules\cron\models\Cron;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use Yii;

class TimerController extends BaseController
{
    public $dependIgnoreValueList = [
        'cron/timer/index' => ['*']
    ];

    /**
     * 列表
     */
    public function actionIndex($task_id = null)
    {
        $data = Cron::find()->joinWith('task')
            ->search(['name' => 'like', 'task_id'])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return $this->render('index', compact('data', 'task_id'));
    }

    /**
     * 添加
     * @return mixed|string
     */
    public function actionAdd($task_id = null)
    {
        $model = new Cron();
        $model->loadDefaultValues();

        $model->task_id = $task_id;
        if ($model->load(Yii::$app->request->post(), '')) {
            $this->getSaveRes($model, ['index', 'task_id' => $task_id]);
        }

        $model->start_time = date('Y-m-d 00:00:00');
        $model->interval_time = 1;

        return $this->render('form', compact('model', 'task_id'));
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

        $model = new Cron();
        return $this->ajax($model, 'del', $params);
    }

    /**
    * 获取模型对象.
    * @param string $id
    * @return Cron the loaded model
    * @throws NotFoundHttpException if the model cannot be found
    */
    protected function findModel($id)
    {
        if (($model = Cron::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

