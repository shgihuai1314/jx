<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/16
 * Time: 上午11:46
 */

namespace system\modules\user\controllers;


use system\modules\user\models\InfoForm;
use system\modules\user\models\User;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

class InfoController extends BaseController
{
    // 禁用csrf的action
    public $disableCsrfAction = ['upload-avatar', 'upload'];
    //忽略权限
    public $ignoreList = [
        'user/info/update', // 个人信息
        'user/info/password', // 修改密码
        'user/info/upload-avatar', // 忽略权限
        'user/info/upload', // 忽略权限
    ];

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'upload-avatar' => [
                'class' => \system\modules\main\components\UploadAction::className(), // action类名称
                'saveDir' => 'avatar/'.date('Y').'/'.date('m'), // 保存的路径
                'fileInput' => 'avatarFile', // file的名称,input 中设置的 name
            ],
            'upload' => [
                'class' => \system\modules\main\extend\Upload::className(),
                'dir' => 'user/autograph',
            ],
        ];
    }
    /**
     * 修改个人密码
     * @return string|\yii\web\Response
     */
    public function actionPassword()
    {
        if (\Yii::$app->request->isPost) {
            $model = new InfoForm();
            $model->scenario = 'password';
            if ($model->load(\Yii::$app->request->post(), '')) {
                $res = $model->changePassword();
                if ($res === true) {
                    $this->flashMsg('ok', '密码修改成功！');
                } else {
                    $message = $res ?: '';
                    $this->flashMsg('error', '密码修改失败；'.$message);
                }
            }

            return $this->refresh();
        }

        $model = $this->getUser();

        return $this->render('password', [
            'model' => $model
        ]);
    }

    /**
     * 修改个人信息
     * @return string|\yii\web\Response
     */
    public function actionUpdate()
    {
        if (\Yii::$app->request->isPost) {
            $model = new InfoForm();
            $model->scenario = 'update';

            if ($model->load(\Yii::$app->request->post(), '')) {
                $res = $model->updateInfo();
                if ($res === true) {
                    $this->flashMsg('ok', '资料修改成功！');
                } else {
                    $message = $res ?: '';
                    $this->flashMsg('error', '资料修改失败；'.$message);
                }
            }

            return $this->refresh();
        }

        $model = $this->getUser();

        return $this->render('update', [
            'model' => $model
        ]);

    }

    /**
     * 获取当前用户model
     * @return ActiveRecord
     * @throws NotFoundHttpException
     */
    private function getUser()
    {
        $model = User::findOne(\Yii::$app->user->identity->getId());
        if (!$model) {
            throw new NotFoundHttpException('数据不存在！');
        }

        return $model;
    }
}