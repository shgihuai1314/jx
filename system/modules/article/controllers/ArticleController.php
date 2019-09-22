<?php

namespace system\modules\article\controllers;

use system\modules\article\models\Article;
use yii\helpers\ArrayHelper;
use Yii;

class ArticleController extends BaseController
{
    public $disableCsrfAction = ['upload', 'editor-upload'];
    public $dependIgnoreValueList = [
        'article/content/index' => ['*']
    ];

    /**
     * @return array
     */
    public function actions()
    {
        return [
            // 富文本编辑器
            'editor-upload' => [
                'class' => \xj\ueditor\actions\Upload::className(),
                'pathFormat' => [
                    'imagePathFormat' => 'article/image/{yyyy}/{mm}/{dd}/{time}{rand:6}',
                    'videoPathFormat' => 'article/video/{yyyy}/{mm}/{dd}/{time}{rand:6}',
                    'filePathFormat' => 'article/file/{yyyy}/{mm}/{dd}/{time}{rand:6}',
                ],
            ],
            //文件上传
            'upload' => [
                'class' => \system\modules\main\extend\Upload::className(),
                //upload/目录下的文件夹，需要指定模块名称
                'dir' => 'article/' . date('Y') . '/' . date('m') . '/' . date('d'),
            ],

        ];
    }

    /**
     * 文章列表
     */
    public function actionIndex()
    {
        $data = Article::find()->joinWith('category')
            ->search([
                'search' => ['like', 'tab_article_content.title', ':val']
            ])
            ->andWhere(['is_del' => 0])
            ->paginate()
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
        $model = new Article();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post(), '')) {
            $model->image = Yii::$app->systemFileInfo->save($model->image, "ArticleContent-image");
            $model->file = Yii::$app->systemFileInfo->save($model->file, "ArtileContent-file");
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
            $model->image = Yii::$app->systemFileInfo->save($model->image, "ArticleContent-image");
            $model->file = Yii::$app->systemFileInfo->save($model->file, "ArtileContent-file");
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

        // 删除操作是把is_del字段置为1，而不是删除数据
        $params['action'] = ['is_del' => 1];

        $model = new Article();
        return $this->ajax($model, 'del', $params);
    }

    /**
     * 获取模型对象.
     * Finds the ExamPaper model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Article the loaded model
     * @throws yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Article::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }

}

