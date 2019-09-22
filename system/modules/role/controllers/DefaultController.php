<?php
namespace system\modules\role\controllers;

use system\modules\role\models\AuthAssign;
use system\modules\role\models\AuthRole;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * 角色管理控制器
 */
class DefaultController extends BaseController
{
    /**
     * 管理员首页
     * @return string
     */
    public function actionIndex()
    {
        $data = AuthRole::find()
            ->search(['or', ['like', 'name', ':val'], ['like', 'description', ':val']])
            ->paginate()
            ->asArray()
            ->all();

        $users = AuthAssign::getUserGroupByRole();

        return $this->render('index', [
            'data' => $data,
            'users' => $users,
        ]);
    }

    /**
     * 增加角色
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {
        $model = new AuthRole();
        $model->loadDefaultValues();

        if ($model->load(\Yii::$app->request->post(), '')) {
            $this->getSaveRes($model);
        }

        // 获取所有用户
        $allUser = ArrayHelper::index(AuthAssign::getAllUser(), 'user_id');

        return $this->render('form', [
            'model' => $model,
            'user' => [],
            'allUser' => $allUser,
        ]);
    }

    /**
     * 编辑角色
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionEdit($id)
    {
        $model = $this->findModel($id);

        if (!$model) {
            $this->flashMsg('error', '数据不存在');
            return $this->redirect('index');
        }

        if ($model->load(\Yii::$app->request->post(), '')) {
            $this->getSaveRes($model);
        }

        // 获取角色对应的所有用户
        $user = AuthAssign::getUseridByRole($id);
        // 获取所有用户
        $allUser = ArrayHelper::index(AuthAssign::getAllUser(), 'user_id');

        return $this->render('form', [
            'model' => $model,
            'user' => $user,
            'allUser' => $allUser,
        ]);
    }

    /**
     * 删除角色
     */
    public function actionDel()
    {
        $params = Yii::$app->request->post();

        $model = new AuthRole();
        return $this->ajax($model, 'del', $params);
    }

    /**
     * 获取模型对象.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AuthRole the loaded model
     * @throws yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AuthRole::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}
