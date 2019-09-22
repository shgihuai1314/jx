<?php

namespace system\modules\notify\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use system\modules\notify\models\NotifyNode;
use yii\helpers\Url;

class NodeController extends BaseController
{

    public function actions()
    {
        return [
            //文件上传
            'upload' => [
                'class' => \system\modules\main\extend\Upload::className(),
                //upload/目录下的文件夹，需要指定模块名称
                'dir' => 'notify/message/' . date('Y') . '/' . date('m') . '/' . date('d'),
            ],
        ];
    }

    /**
     * 节点列表
     * @return string
     */
    public function actionIndex()
    {
        $data = NotifyNode::find()
            ->search(['name' => ['or', ['like', 'node_name', ':val'], ['like', 'node_info', ':val']], 'module'])
            ->orderBy(['node_id' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'data' => $data,
        ]);
    }

    /**
     * 添加节点
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {
        $params = Yii::$app->request->post();

        //ajax验证节点是否已经存在
        if (isset($params['action']) && $params['action'] == 'name-exit') {
            if (NotifyNode::findOne(['node_name' => $params['node_name']])) {
                return $this->ajaxReturn([
                    'code' => 1,
                    'message' => '节点已经存在',
                ]);
            } else {
                return $this->ajaxReturn([
                    'code' => 0,
                    'message' => '节点不存在'
                ]);
            }
        }

        $category = Yii::$app->systemConfig->getValue('NOTIFY_TYPE', []);

        $model = new NotifyNode();
        $model->loadDefaultValues();
        //保存传递过来的信息
        if ($model->load($params, '')) {
            $icon = json_decode($params['icon'],true);
            if($icon){
                $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
                $model->icon = $http_type . $_SERVER['HTTP_HOST'].$icon['src'];
            }
            $this->getSaveRes($model);
        }

        return $this->render('add', [
            'model' => $model,
            'category' => $category
        ]);
    }

    /**
     * 编辑节点
     * @return string|\yii\web\Response
     */
    public function actionEdit($id)
    {
        $params = Yii::$app->request->post();

        if (Yii::$app->request->isAjax) {//如果是通过ajax编辑
            $model = $this->findModel($id);

            if (isset($params['action']) && $params['action'] == 'name-exit') {
                if (NotifyNode::find()->where(['and', ['!=', 'node_id', $model->node_id], ['node_name' => $params['node_name']]])->count()) {
                    return $this->ajaxReturn([
                        'code' => 1,
                        'message' => '节点已经存在',
                    ]);
                } else {
                    return $this->ajaxReturn([
                        'code' => 0,
                        'message' => '节点不存在'
                    ]);
                }
            }

            return $this->ajax($model,'edit', $params);
        }

        $category = Yii::$app->systemConfig->getValue('NOTIFY_TYPE', []);

        $model = $this->findModel($id);
        //保存传递过来的信息
        if ($model->load($params, '')) {
            $icon = json_decode($params['icon'],true);
            if($icon){
                $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
                $model->icon = $http_type . $_SERVER['HTTP_HOST'].$icon['src'];
            }
            $this->getSaveRes($model);
        }

        return $this->render('edit', [
            'model' => $model,
            'category' => $category
        ]);
    }

    /**
     * 删除节点
     */
    public function actionDel()
    {
        $params = Yii::$app->request->post();

        $model = new NotifyNode();
        return $this->ajax($model, 'del', $params);
    }

    /**
     * 获取模型对象.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return NotifyNode the loaded model
     * @throws yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = NotifyNode::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}
