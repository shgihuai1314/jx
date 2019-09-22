<?php

namespace system\modules\article\controllers;

use system\modules\article\models\ArticleCategory;
use yii\helpers\ArrayHelper;
use Yii;

class CategoryController extends BaseController
{
    public $disableCsrfAction = ['upload', 'editor-upload'];
    public $dependIgnoreValueList = [
        'article/category/index' => ['*']
    ];

    /**
     * @return array
     */
    public function actions()
    {
        return [
            //文件上传
            'upload' => [
                'class' => \system\modules\main\extend\Upload::className(),
                //upload/目录下的文件夹，需要指定模块名称
                'dir' => 'article/' . date('Y') . '/' . date('m') . '/' . date('d'),
            ],

        ];
    }

    /**
     * 文章分类列表
     */
    public function actionIndex()
    {
        $data = ArticleCategory::find()
            ->search([
                'search' => ['or', ['like', 'title', ':val']]
            ])->paginate()
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return $this->render('index', compact('data'));
    }

    /**
     * 添加文章
     * @return mixed|string
     */
    public function actionAdd()
    {
        $model = new ArticleCategory();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post(), '')) {
            $model->image = Yii::$app->systemFileInfo->save($model->image, "ArticleCategory");
            $this->getSaveRes($model);
        }

        return $this->render('form', compact('model'));
    }

    /**
     * 编辑文章
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
            $model->image = Yii::$app->systemFileInfo->save($model->image, "ArticleCategory");
            $this->getSaveRes($model);
        }

        return $this->render('form', compact('model'));
    }

    /**
     * 删除文章
     */
    public function actionDel()
    {
        $params = Yii::$app->request->post();

        $model = new ArticleCategory();
        return $this->ajax($model, 'del', $params);
    }

    /**
     * ajax请求
     * @return string|\yii\web\Response
     */
    public function actionAjax()
    {
        $params = ArrayHelper::merge(Yii::$app->request->post(), Yii::$app->request->queryParams);
        $action = ArrayHelper::getValue($params, 'action');

        if ($action == 'get-nodes') {
            $list = ArticleCategory::find()->asArray()->orderBy(['id' => SORT_ASC])->all();

            $data = [];
            foreach ($list as $key => $val) {
                if (empty($arr) || in_array($val['id'], ArticleCategory::getChildIds($arr))) {
                    $data[] = ['id' => $val['id'], 'name' => $val['title'], 'pid' => $val['pid'],];
                }
            }
            array_unshift($data, ['id' => 0, 'name' => '全部分组', 'pid' => 0, 'open' => false]);

            return $this->ajaxReturn($data);
        }
    }

    /**
     * 获取模型对象.
     * Finds the ExamPaper model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ArticleCategory the loaded model
     * @throws yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ArticleCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }


}

