<?php

namespace system\modules\user\controllers;

use system\modules\user\models\ContentPermission;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use Yii;


/**
 * ContentPermissionController implements the CRUD actions for ContentPermission model.
 */
class ContentPermissionController extends BaseController
{
    /**
     * Lists all ContentPermission models.
     * @return mixed
     */
    public function actionIndex()
    {
        $data = ContentPermission::find()->joinWith('user')
            ->search(['search' => ['like', 'realname', ':val']])
            ->paginate()
            ->orderBy(['sort' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'data' => $data,
        ]);
    }

    /**
     * Creates a new ContentPermission model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ContentPermission();
        $model->loadDefaultValues();

	    if ($model->load(Yii::$app->request->post(), '')) {
		    $this->getSaveRes($model);
	    }
	
        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ContentPermission model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
	
	    if ($model->load(Yii::$app->request->post(), '')) {
		    $this->getSaveRes($model);
	    }
	
	    return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ContentPermission model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDel()
    {
        $params = Yii::$app->request->post();

        $model = new ContentPermission();
        return $this->ajax($model, 'del', $params);
    }

    /**
     * Finds the ContentPermission model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ContentPermission the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ContentPermission::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('内容不存在');
        }
    }
}
