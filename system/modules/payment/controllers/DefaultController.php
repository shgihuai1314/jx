<?php

namespace system\modules\payment\controllers;

use system\modules\payment\models\paymentApp;
use yii\helpers\ArrayHelper;
use Yii;

class DefaultController extends BaseController
{

    public function actionIndex()
    {
        $condition = [
            'search' => [
                'or',
                ['like', 'name', ':val'],
                ['like', 'describle', ':val'],
                ['like', 'code', ':val'],
            ],
        ];
        $data = paymentApp::find()
            ->search($condition)
            ->paginate()
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return $this->render('index', compact('data'));
    }

    /**
     * 添加
     * @return mixed|string
     */
    public function actionAdd()
    {
        $model = new paymentApp();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post(), '')) {

            $this->getSaveRes($model);
        }

        $model->pay_type=0;

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

        $model = new paymentApp();
        return $this->ajax($model, 'del', $params);
    }

    /**
     * 获取模型对象.
     * Finds the ExamPaper model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return paymentApp the loaded model
     * @throws yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = paymentApp::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}

