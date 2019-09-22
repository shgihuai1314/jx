<?php
/**
 * 系统配置项；公用
 * User: ligang
 * Date: 2017/3/12
 * Time: 下午2:00
 */

namespace system\modules\main\controllers;

use system\modules\main\models\Config;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii;

class ConfigController extends BaseController
{
    public $disableCsrfAction = ['upload'];

    public $dependIgnoreValueList = [
        'main/config/index' => ['*']
    ];

    public function actions()
    {
        return [
            'upload' => [
                'class' => \system\modules\main\extend\Upload::className(),
                'dir' => 'main/config/image/',
            ],
        ];
    }

    /**
     * 配置管理
     * @return string
     */
    public function actionIndex()
    {
        $data = Config::find()
            ->search([
                'module',
                'search' => ['or', ['like', 'name', ':val'], ['like', 'title', ':val'], ['like', 'extra', ':val'], ['like', 'remark', ':val'],],
            ])->orderBy(['sort' => SORT_DESC])
            ->all();

        return $this->render('index', compact('data'));
    }

    /**
     * 增加配置
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {
        $model = new Config();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post(), '')) {
            $this->getSaveRes($model);
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * 编辑配置
     * @return \yii\web\Response|string
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

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * 删除的数据
     * @param $id
     * @return Response
     */
    public function actionDel()
    {
        $params = Yii::$app->request->post();

        $model = new Config();
        return $this->ajax($model, 'del', $params);
    }

    /**
     * ajax
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionAjax()
    {
        $params = Yii::$app->request->post();
        $action = ArrayHelper::getValue($params, 'action');

        switch ($action) {
            case 'name-exit':// 检查配置名称是否被占用
                $id = ArrayHelper::getValue($params, 'id');
                $name = ArrayHelper::getValue($params, 'name');
                if ($id) {
                    // 如果有ID，说明是编辑状态，查询除该id以外是否还有重名配置
                    $condition = ['and', ['name' => $name], ['!=', 'id', $id]];
                } else {
                    $condition = ['name' => $name];
                }
                if (Config::find()->where($condition)->exists()) {
                    return $this->ajaxReturn([
                        'code' => 1,
                        'message' => '标识已经存在',
                    ]);
                } else {
                    return $this->ajaxReturn([
                        'code' => 0,
                        'message' => '标识不存在',
                    ]);
                }
                break;
        }
    }

    /**
     * 获取模型对象.
     * @param string $id
     * @return Config the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Config::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}