<?php
/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2019/3/18
 * Time: 11:34
 */

namespace system\modules\wallet\components;

use system\modules\wallet\models\OrderLogs;
use yii\base\Component;

class OrderLog extends Component
{
    /**
     * 添加订单变动记录
     * @param $data
     * @return array
     */
    public function saveOrderLog($data=[])
    {
        if (!isset($data['order_id'], $data['content'])) {
            return ['code' => 1, 'message' => '缺少订单id或内容详情'];
        }

        $result = OrderLogs::SaveRecord($data);

        if ($result['code'] != 0) {
            return ['code' => 1, 'message' => $result['message']];
        }

        return ['code' => 0, 'message' =>  $result['message']];
    }
}