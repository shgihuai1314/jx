<?php

namespace system\modules\user\controllers;

use system\modules\user\models\Position;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * 职位管理控制器
 */
class PositionController extends BaseController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $data = Position::find()
            ->search(['name' => 'like'])
            ->paginate()
            ->orderBy(['sort' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'data' => $data,
        ]);
    }

    /**
     * 增加职位
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {
        $model = new Position();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post(), '')) {
            $this->getSaveRes($model);
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * 编辑职位
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionEdit($id)
    {
        $model = Position::findOne($id);

        if ($model->load(Yii::$app->request->post(), '')) {
            $this->getSaveRes($model);
        }

        return $this->render('form', [
            'model' => $model
        ]);
    }

    /**
     * 删除职位
     * @param $id
     */
    public function actionDel()
    {
        $params = Yii::$app->request->post();

        $model = new Position();
        return $this->ajax($model, 'del', $params);
    }
}
