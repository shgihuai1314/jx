<?php
/**
 * 系统日志组件
 * 使用方式：Yii::$app->myLog->write();
 */
namespace system\modules\main\components;

use yii\base\Component;


/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/7
 * Time: 下午11:22
 */
class Log extends Component
{
    /**
     * 写日志
     * @param $data array 数据数组
     * @return bool
     */
    public function write($data)
    {
        if (\Yii::$app instanceof \yii\console\Application) {
            $ip = '127.0.0.1';
        } else {
            $ip = \Yii::$app->request->userIP;
        }

        $user_id = 0; // 默认是系统：0
        if (isset($data['user_id'])) {
            $user_id = $data['user_id'];
        }
        else if (\Yii::$app instanceof \yii\web\Application && !\Yii::$app->user->isGuest) {
            $user_id = \Yii::$app->user->identity->getId();
        }

        $model = new \system\modules\main\models\Log();
        $model->type = isset($data['type']) ? $data['type'] : 'unknown';
        $model->target_id = isset($data['target_id']) ? $data['target_id'] : 0;
        $model->target_id2 = isset($data['target_id2']) ? $data['target_id2'] : 0;
        $model->content = isset($data['content']) ? $data['content'] : '';
        $model->user_id = $user_id;
        $model->ip = $ip;
        $model->add_time = time();
        return $model->save();
    }
}