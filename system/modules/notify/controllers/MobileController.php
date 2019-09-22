<?php
namespace system\modules\notify\controllers;

use system\modules\notify\models\NotifyMessage;
use system\modules\main\models\Modules;
use yii\helpers\ArrayHelper;
use system\core\utils\Tool;
use Yii;

class MobileController extends BaseController
{
    /**
     * 消息列表
     * @return string
     */
    public function actionIndex()
    {
        $message = NotifyMessage::find()
            ->with('moduleinfo')
            ->where(['user_id' => \Yii::$app->user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->all();

        $message = Tool::array_to_multiple_by_index($message, 'module');
        return $this->render('index', [
            'message' => $message
        ]);
    }

    /**
     * 消息详情
     * @param $type
     * @return string|\yii\web\Response
     */
    public function actionDetail($type)
    {
        $user_id = \Yii::$app->user->id;

        $list = NotifyMessage::find()
            ->asArray()
            ->indexBy('created_at')
            ->where(['module' => $type, 'user_id' => $user_id,'is_delete' => 0])
            ->orderBy(['is_read' =>SORT_ASC ,'created_at' => SORT_DESC])
            ->all();

        //标记消息为已读
        if (\Yii::$app->request->isAjax) {
            $data = \Yii::$app->request->get();
            // 设置某一条为已读
            if (isset($data['message_id'])) {
                $model = NotifyMessage::findOne($data['message_id']);
                $model->is_read = 1;
                if ($model->save()) {
                    return $this->ajaxReturn([
                        'code' => 0,
                        'message' => '操作成功',
                    ]);
                }
            }
            return $this->ajaxReturn([
                'code' => 1,
                'message' => '操作失败，请重试',
            ]);
        }
        $module = Modules::find()->where(['module_id' => $type])->asArray()->one();

        return $this->render('detail', [
            'list' => $list,
            'title' => $module['name']
        ]);
    }

    /**
     * 删除消息通知
     * @param $message_id
     * @return string|\yii\web\Response
     */
    public function actionDel($message_id)
    {
        $model = NotifyMessage::findOne(['user_id' => Yii::$app->user->id, 'message_id' => $message_id,'is_delete' => 0]);
        if(!$model){
            return $this->getAjaxReturn(false,'数据不存在');
        }
        $model->is_delete = 1;
        $model->is_read = 1;
        $res = $model->save();
        return $this->getAjaxReturn($res,['删除成功，删除失败']);
    }
}