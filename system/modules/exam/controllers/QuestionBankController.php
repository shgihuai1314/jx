<?php

namespace system\modules\exam\controllers;

use system\modules\exam\models\ExamQuestionCategory;
use Yii;
use yii\helpers\Json;

class QuestionBankController extends BaseController
{
    public function actionIndex()
    {
        $condition = [
            'search' => [
                'or',
                ['like', 'name', ':val'],
            ],
        ];
        $data =ExamQuestionCategory ::find()
            ->search($condition)
            ->where(['is_delete'=>0,'user_id'=>Yii::$app->user->id])
            ->paginate()
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return $this->render('index', compact('data'));

    }

    /**
     * @return string
     */
    public function actionAdd()
    {
        $model = new ExamQuestionCategory();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post(), '')) {
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

        $res=ExamQuestionCategory::updateAll(['is_delete' => 1], ['id' => $params['id']]);

       if($res){
           return Json::encode([
               'code' => 0,
               'message' => '删除成功',
           ]);
       }else{
           return Json::encode([
               'code' => 1,
               'message' => '删除失败，请重试',
           ]);
       }



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
        if (($model = ExamQuestionCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }

}
