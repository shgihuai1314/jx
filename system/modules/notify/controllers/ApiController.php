<?php
namespace system\modules\notify\controllers;

use system\modules\main\models\Modules;
use system\modules\notify\models\NotifyMessage;

class ApiController extends BaseController
{
    public $enableCsrfValidation = false;

    public $ignoreList = ['notify/api/get-message'];

    public function actionGetMessage()
    {
        if(\Yii::$app->user->isGuest){
            return json_encode([
                'code' => 1,
                'message' => '当前用户不在线',
            ]);
        }

        $user_id = \Yii::$app->user->getId();

        $notifyMessage = NotifyMessage::find()
            ->select('name,module_id,icon,content,message_id,sender_id,is_read,url,created_at')
            ->from(Modules::tableName().' m')
            ->leftJoin(NotifyMessage::tableName().' n', 'n.module = m.module_id')
            ->where(['user_id' => $user_id])
            ->orderBy(['created_at' => SORT_DESC ])
            ->asArray()
            ->all();

        // 临时解决方案，因为ios客户端消息url处理时有问题
        $data = [];
        foreach ($notifyMessage as $item) {
            if (!strpos($item['url'], '?')) {
                $item['url'] .= '?flag=1';
            }
            $data[] = $item;
        }

        return json_encode([
            'code' => 0,
            'message' => 'success',
            'data' => $data,
        ]);
    }
}