<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/16
 * Time: 上午11:11
 */

namespace system\modules\user\controllers;

use system\modules\user\models\Group;
use system\modules\user\models\Position;
use system\modules\role\models\AuthRole;
use system\modules\user\models\User;
use yii\helpers\ArrayHelper;
use yii;

class ManageController extends BaseController
{
    // 禁用csrf的action
    public $disableCsrfAction = ['upload-avatar'];

    //依赖权限
    public $dependIgnoreList = [
        'user/manage/list' => [
            'user/manage/index',
        ],
        'user/manage/upload-avatar' => [
            'user/manage/add',
            'user/manage/edit',
        ],
    ];

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'upload-avatar' => [
                'class' => \system\modules\main\components\UploadAction::className(), // action类名称
                'saveDir' => 'avatar/' . date('Y') . '/' . date('m'), // 保存的路径
                'fileInput' => 'avatarFile', // file的名称,input 中设置的 name
            ],
        ];
    }

    /**
     * 用户列表
     * @return string
     */
    public function actionIndex()
    {
        $condition = [
            'status',
            'search' => [ 'or',
                ['like', 'username', ':val'],
                ['like', 'realname', ':val'],
                ['like', 'phone', ':val'],
                ['like', 'email', ':val']
            ],
            'group' => function ($id) {
                $flag = Yii::$app->request->get('flag');
                $groupPermission = Group::getAllPermissionGroupIdsByUser(Yii::$app->user->getId());
                if ($groupPermission) {
                    return ['and', ['group_id' => $flag ? Group::getChildIdsById($id) : $id], ['group_id' => $groupPermission]];
                } else {
                    return ['group_id' => $flag ? Group::getChildIdsById($id) : $id];
                }
            }
        ];

        $data = User::find()
            ->with('group', 'role')
            ->search($condition)
            ->paginate()
            ->orderBy(['sort' => SORT_DESC, 'user_id' => SORT_DESC])
            ->all();

        return $this->render('index', compact('data'));
    }

    /**
     * 增加用户
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {
        $get = \Yii::$app->request->get();

        if (isset($get['action']) && $get['action'] == 'name-exit') {
            if (User::findOne(['username' => $get['username']])) {
                return $this->ajaxReturn([
                    'code' => 1,
                    'message' => '用户名已经存在',
                ]);
            } else {
                return $this->ajaxReturn([
                    'code' => 0,
                    'message' => '用户名不存在'
                ]);
            }
        }

        $model = new User();
        $model->loadDefaultValues();
        $model->log_flag = true; // 打开日志
        if (\Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            $data['roles'] = explode(',', $data['roles']);
            $groupPermission = Group::getAllPermissionGroupIdsByUser(Yii::$app->user->getId());
            if (isset($data['group_id']) && $groupPermission && !in_array($data['group_id'], $groupPermission)) {
                $this->flashMsg('error', '没有权限添加到此组');
            } else if ($model->load($data, '') && $model->save()) {
                $this->flashMsg('ok', '添加完成');
                return $this->redirect('index');
            } else {
                $this->flashMsg('error', '添加失败，请重试');
            }
        }

        $model->group_id = ArrayHelper::getValue($get, 'group_id', 1);
        // 职位和角色
        $position = Position::getAllMap();
        $role = AuthRole::getAllMap();

        return $this->render('add', [
            'model' => $model,
            'position' => $position,
            'role' => $role,
        ]);
    }

    /**
     * 编辑用户
     * @param $id
     * @return string|\yii\web\Response
     * @throws yii\web\NotFoundHttpException
     */
    public function actionEdit($id)
    {
        if (Yii::$app->request->isAjax) {//如果是通过ajax编辑
            $params = Yii::$app->request->post();
            $model = $this->findModel($id);
            return $this->ajax($model,'edit', $params);
        }

        $get = \Yii::$app->request->get();

        $model = $this->findModel($id);
        $model->log_flag = true; // 打开日志

        $groupPermission = Group::getAllPermissionGroupIdsByUser(Yii::$app->user->getId());
        if ($groupPermission && !in_array($model->group_id, $groupPermission)) {
            throw new yii\web\NotFoundHttpException('找不到此用户');
        }

        if (\Yii::$app->request->isAjax && isset($get['action']) && $get['action'] == 'name-exit') {
            if (User::find()->where(['and', ['!=', 'user_id', $id], ['username' => $get['username']]])->count()) {
                return $this->ajaxReturn([
                    'code' => 1,
                    'message' => '用户名已经存在',
                ]);
            } else {
                return $this->ajaxReturn([
                    'code' => 0,
                    'message' => '用户名不存在'
                ]);
            }
        }

        if (\Yii::$app->request->isPost) {
            $data = \Yii::$app->request->post();
            $data['roles'] = explode(',', $data['roles']);
            if (isset($data['group_id']) && $groupPermission && !in_array($data['group_id'], $groupPermission)) {
                $this->flashMsg('error', '没有权限添加到此组');
            }

            if ($model->load($data, '')) {
                $this->getSaveRes($model);
            }
        }

        // 职位和角色
        $position = Position::getAllMap();
        $role = AuthRole::getAllMap();
        $model->roles = $model->getRoles(); // 获取用户的所有角色

        return $this->render('edit', [
            'model' => $model,
            'position' => $position,
            'role' => $role,
        ]);
    }

    /**
     * 将用户的状态置为删除状态
     * @param $id
     */
    public function actionDel()
    {
        $params = Yii::$app->request->post();
        $id = ArrayHelper::remove($params, 'id', 0);

        $model = User::findOne(['status' => User::STATUS_ACTIVE, 'user_id' => $id]);
        if (!$model) {
            $model = User::findOne(['status' => User::STATUS_DELETE, 'user_id' => $id]);
            if (!$model) {
                return $this->ajaxReturn([
                    'code' => 1,
                    'message' => '数据不存在',
                ]);
            }
            $model->log_flag = true; // 打开日志
            return $this->getAjaxReturn($model->delete(), ['彻底删除成功', '彻底删除失败']);
        }

        $model->log_flag = true; // 打开日志
        // 更改状态
        $model->status = User::STATUS_DELETE;
        return $this->getAjaxReturn($model->save(), ['删除成功', '删除失败，请重试']);
    }

    /**
     * 批量操作
     */
    public function actionBatch()
    {
        $params = Yii::$app->request->post();

        if (empty($params)) {
            $this->layout = '/frame';
            return $this->render('operate');
        } else {
            $field = ArrayHelper::getValue($params, 'field');
            $val = ArrayHelper::getValue($params, 'val');
            $uids = ArrayHelper::getValue($params, 'uids');

            foreach ($uids as $uid) {
                $model = User::findOne($uid);
                if ($model) {
                    $model->$field = $val;
                    $model->save();
                }
            }

            return $this->ajaxReturn([
                'code' => 0,
                'message' => '修改成功',
            ]);

        }
    }

    /**
     * 获取模型对象.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return User the loaded model
     * @throws yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}