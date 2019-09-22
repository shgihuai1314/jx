<?php

namespace system\modules\main\controllers;

use system\modules\main\models\AppCategory;
use system\modules\main\models\App;
use system\modules\main\models\AppRelation;
use system\modules\user\models\ContentPermission;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;
use Yii;

/**
 * 控制器
 */
class AppController extends BaseController
{
    // 禁用csrf的action
    public $disableCsrfAction = ['upload', 'editor-upload'];

    public $dependIgnoreValueList = [
        'main/app/index' => ['*']
    ];

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'upload' => [
                'class' => \system\modules\main\extend\Upload::className(),
                'resetSize' => 100, // 重置大小
                'dir' => 'app/icon',
            ],
            'editor-upload' => [
                'class' => \xj\ueditor\actions\Upload::className(),
                'csrf' => false,
                'pathFormat' => [
                    'imagePathFormat' => 'app/image/{yyyy}/{mm}/{dd}/{time}{rand:6}',
                    'videoPathFormat' => 'app/video/{yyyy}/{mm}/{dd}/{time}{rand:6}',
                    'filePathFormat' => 'app/files/{yyyy}/{mm}/{dd}/{time}{rand:6}',
                ],
            ]
        ];
    }

    /**
     * 应用首页
     * @return string
     */
    public function actionIndex()
    {
        $query = App::find()
            ->select('a.*,r.cate_id')
            ->from(App::tableName() . ' a')
            ->leftJoin(AppRelation::tableName() . ' r', 'r.app_id = a.id')
            ->search([
                'category_id' => function ($val) {
                    return $val == 0 ? [] : ['r.cate_id' => AppCategory::getChildIds($val)];
                },
                'search' => ['like', 'name', ':val']
            ]);

        $contentPermission = ContentPermission::find()->asArray()->where(['user_id' => Yii::$app->user->id])->one();
        $arr = ArrayHelper::getValue($contentPermission, 'extend_app_category_list');
        $arr = empty($arr) ? [] : explode(',', $arr);
        if (!empty($arr)) {
            $query = $query->andWhere(['r.cate_id' => AppCategory::getChildIds($arr)]);
        }

        $list = $query->paginate()
            ->orderBy(['sort' => SORT_DESC, 'id' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'list' => $list,
        ]);
    }

    /**
     * 添加应用
     * @return string
     */
    public function actionAdd()
    {
        $model = new App();
        $model->loadDefaultValues();

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            $category_id = $data['category_id'] ? $data['category_id'] : 0;
            if ($model->load($data, '') && $model->save()) {
                foreach (explode(',', $category_id) as $item) {
                    $relation[] = [$item, $model->id];
                }
                Yii::$app->db->createCommand()->batchInsert(AppRelation::tableName(), ['cate_id', 'app_id'], $relation)->execute();
                $this->flashMsg('ok', '添加成功');
                return $this->redirect(['index']);
            } else {
                $this->flashMsg('error', '添加失败');
            }
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * 编辑应用
     * @param $id integer 应用ID
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
            if ($model->save()) {
                $category_id = Yii::$app->request->post('category_id', 0);
                foreach (explode(',', $category_id) as $item) {
                    $relation[] = [$item, $model->id];
                }
                AppRelation::deleteAll(['app_id' => $model->id]);
                Yii::$app->db->createCommand()->batchInsert(AppRelation::tableName(), ['cate_id', 'app_id'], $relation)->execute();
                $this->flashMsg('ok', '添加成功');
                return $this->redirect(['index']);
            } else {
                $this->flashMsg('error', '添加失败');
            }
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * 删除应用分类
     */
    public function actionDel()
    {
        $params = Yii::$app->request->post();

        $model = new App();
        return $this->ajax($model, 'del', $params);
    }

    /**
     * 获取模型对象.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return App the loaded model
     * @throws yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = App::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}
