<?php

namespace system\modules\notify\controllers;

use system\modules\notify\models\NotifyMessage;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;
use Yii;

/**
 * 用户消息
 */
class UserController extends BaseController
{
    //忽略权限
    public $ignoreList = [
        'notify/user/index', // 我的消息
        'notify/user/not-read', // 未读消息
        'notify/user/test',
        'notify/user/ajax',
        'notify/user/view'
    ];

    /**
     * 当前用户的消息
     * @return mixed
     */
    public function actionIndex()
    {
        $data = NotifyMessage::find()
            ->select('module')
            ->orderBy(['created_at' => SORT_DESC])
            ->groupBy('module, user_id, created_at')
            ->where(['user_id' => Yii::$app->user->id, 'is_delete' => 0])
            ->asArray()
            ->all();

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $new = NotifyMessage::find()->asArray()
                    ->where(['user_id' => Yii::$app->user->id, 'is_read' => 0, 'module' => $val['module']])
                    ->orderBy('created_at desc')
                    ->all();
                if (!empty($new)) {
                    $firstOne = reset($new);
                    $res[$val['module']]['time'] = $firstOne['created_at'];
                    $res[$val['module']]['new'] = $new;
                    $res[$val['module']]['count'] = count($new);
                    $data = $res;
                } else {
                    $last = NotifyMessage::find()->asArray()
                        ->where(['user_id' => Yii::$app->user->id, 'module' => $val['module']])
                        ->orderBy('created_at desc')
                        ->one();
                    $res[$val['module']]['time'] = 0;
                    $res[$val['module']]['last'] = $last;
                    $res[$val['module']]['count'] = 0;
                    $data = $res;
                }
            }
            $refer = [];
            foreach ($data as $key => $value) {
                $refer[$key] = $value['time'];
            }
            array_multisort($refer, SORT_DESC, $data);
        }

        return $this->render('index', [
            'data' => $data,
        ]);
    }

    /**
     * 消息操作
     * @param $id integer 消息ID
     * @return string
     */
    public function actionAjax()
    {
        $params = Yii::$app->request->post();
        $type = ArrayHelper::remove($params, 'type', 0);
        $status = ArrayHelper::remove($params, 'status', 0);
        //批量修改已读状态
        if ($type == "btn-batch-read") {
            $ids = ArrayHelper::getValue($params, 'ids', []);
            NotifyMessage::updateAll(['is_read' => 1], [
                'user_id' => Yii::$app->user->id,
                'module' => $ids,
            ]);

            return $this->ajaxReturn([
                'code' => 0,
            ]);
            //批量删除
        } elseif ($type == "btn-all-del") {
            if ($status == 1) {
                $ids = ArrayHelper::getValue($params, 'ids', []);
                $data = NotifyMessage::updateAll(['is_delete' => 1, 'is_read' => 1], ['user_id' => Yii::$app->user->id, 'message_id' => $ids]);
            } else {
                $ids = ArrayHelper::getValue($params, 'ids', []);
                $data = NotifyMessage::updateAll(['is_delete' => 1, 'is_read' => 1], ['user_id' => Yii::$app->user->id, 'module' => $ids]);
            }

            if ($data) {
                return $this->ajaxReturn([
                    'code' => 0,
                ]);
            } else {
                return $this->ajaxReturn([
                    'code' => 1,
                    'msg' => '删除失败',
                ]);
            }
            //单个删除
        } elseif ($type == 'btn-one-del') {
            if ($status == 1) {
                $id = ArrayHelper::remove($params, 'id', 0);
                $model = NotifyMessage::findOne(['user_id' => Yii::$app->user->id, 'message_id' => $id]);
                $model->is_delete = 1;
                $model->is_read = 1;
                $data = $model->save();
            } else {
                $id = ArrayHelper::remove($params, 'id', 0);
                $data = NotifyMessage::updateAll(['is_delete' => 1, 'is_read' => 1], ['user_id' => Yii::$app->user->id, 'module' => $id]);
            }
            if ($data) {
                return $this->ajaxReturn([
                    'code' => 0,
                ]);
            } else {
                return $this->ajaxReturn([
                    'code' => 1,
                    'msg' => '删除失败',
                ]);
            }
        } else {
            $id = ArrayHelper::remove($params, 'id', 0);
            $model = NotifyMessage::findOne(['user_id' => Yii::$app->user->id, 'message_id' => $id]);
            $model->is_read = 1;
            $data = $model->save();
            if ($data) {
                return $this->ajaxReturn([
                    'code' => 0,
                ]);
            } else {
                return $this->ajaxReturn([
                    'code' => 1,
                    'msg' => '修改失败',
                ]);
            }
        }

    }

    /**
     * 消息详情页
     */
    public function actionView($module)
    {
        $model = NotifyMessage::find()->asArray()
            ->where(['user_id' => Yii::$app->user->id, 'module' => $module, 'is_delete' => 0])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $data = [];
        foreach ($model as $k => $v) {
            $data[date('m月d日', $v['created_at'])][] = $v;//分组
        }
        if ($model) {
            NotifyMessage::updateAll(['is_read' => 1], ['user_id' => Yii::$app->user->id, 'module' => $model[0]['module']]);
        }
        return $this->render('view', [
            'data' => $data,
        ]);
    }

    /**
     * 获取未读消息记录
     */
    public function actionNotRead()
    {
        $count = NotifyMessage::find()->where(['user_id' => Yii::$app->user->id, 'is_read' => 0])->count();

        // 解析页面
        $html = $this->renderPartial('not-read');

        return $this->ajaxReturn([
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'html' => $html,
                'count' => $count, // 是否有新的未读信息
            ],
        ]);
    }

}