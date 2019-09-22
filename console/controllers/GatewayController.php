<?php
namespace console\controllers;


use system\core\utils\Gps;
use system\modules\gateway\models\Device;
use system\modules\gateway\models\Env;
use system\modules\gateway\models\Gateway;
use system\modules\gateway\models\GatewayMessage;
use system\modules\gateway\models\PortInfo;
//use system\modules\stats\models\StatsEnv;
//use system\modules\stats\models\StatsPort;
use yii;

/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/7
 * Time: 下午1:17
 */
class GatewayController extends \yii\console\Controller
{
    // 日志文件
    private $_logFile = './task.error.log';

    const LIST_GATEWAY = 'list:gateway'; // 网关队列
    const LIST_DEVICE = 'list:dev';     // 设备队列
    const LIST_ENV = 'list:env';        // 环境队列
    const LIST_PORT = 'list:port_info'; // 端口队列
    const LIST_MSG = 'list:msg'; //消息队列

    // 处理所有数据
    public function actionAll()
    {
        $this->actionGateway(); // 新增网关队列
        $this->actionDev();     // 新增设备队列
        $this->actionEnv();     // 环境数据队列
        $this->actionPort();    // 网口流量队列
        $this->actionMsg();     // 报警消息队列
        $this->actionGatewayLocation(); // 给没有定位的网关进行定位
        $this->actionGatewayState();    // 对没有收到报文对网关离线处理
    }

    // 写日志
    private function _writeLog($content)
    {
        file_put_contents($this->_logFile, date('Y-m-d H:i:s') . '--' . yii\helpers\Json::encode(Yii::$app->request->getParams()) . "\r\n\r\n" . $content, FILE_APPEND);
    }

    // 网关
    public function actionGateway()
    {
        $data = Yii::$app->redis->lrange(self::LIST_GATEWAY, 0, -1);
        if (empty($data)) {
            return 0;
        }
        foreach ($data as $item) {
            $one = json_decode($item, true);

            // 数据格式错误，直接丢掉
            if (!$one) {
                Yii::$app->redis->lpop(self::LIST_GATEWAY);
                continue;
            }

            // 判断数据是否正确，不正确的数据直接丢掉
            if (!isset($one['gateway_id'], $one['mac'], $one['ip'])) {
                Yii::$app->redis->lpop(self::LIST_GATEWAY);
                continue;
            }

            //print_r($one);
            $model = Gateway::findOne($one['gateway_id']);
            if ($model) {
                // 弹出队列 @todo 如果ip更改了是否要写库更新
                Yii::$app->redis->lpop(self::LIST_GATEWAY);
                continue;
            }
            $model = new Gateway();
            /*$model->gateway_id =  $one['gateway_id'];
            $model->mac =  $one['mac'];
            $model->ip =  $one['ip'];*/
            if ($model->load($one, '') && $model->save()) {
                // 弹出队列
                Yii::$app->redis->lpop(self::LIST_GATEWAY);
            } else {
                $this->_writeLog(yii\helpers\Json::encode($model->errors));
                echo yii\helpers\Json::encode($model->errors);
                break;
            }
        }
    }

    // 设备信息
    public function actionDev()
    {
        $data = Yii::$app->redis->lrange(self::LIST_DEVICE, 0, -1);
        if (empty($data)) {
            return 0;
        }
        //print_r($data);//exit;
        foreach ($data as $item) {
            $one = json_decode($item, true);

            // 数据格式错误，直接丢掉
            if (!$one) {
                Yii::$app->redis->lpop(self::LIST_DEVICE);
                continue;
            }

            // 判断数据是否正确，不正确的数据直接丢掉
            if (!isset($one['gateway_id'], $one['if_port'], $one['mac'], $one['ip'])) {
                Yii::$app->redis->lpop(self::LIST_DEVICE);
                continue;
            }

            //先查询设备mac是否存在
            $model = Device::findOne(['mac' => $one['mac']]);
            if (!$model) {
                $model = new Device();
            }

            if ($model->load($one, '') && $model->save()) {
                // 弹出队列
                Yii::$app->redis->lpop(self::LIST_DEVICE);
            } else {
                $this->_writeLog(yii\helpers\Json::encode($model->errors));
                echo yii\helpers\Json::encode($model->errors);
                break;
            }
        }
    }

    // 端口信息
    public function actionPort()
    {
        $data = Yii::$app->redis->lrange(self::LIST_PORT, 0, -1);
        if (empty($data)) {
            return 0;
        }

        /*$stats_interval = Yii::$app->systemConfig->getValue('STATS_INTERVAL_GATEWAY_PORT', 60);
        // 如果超过60分钟，那么按照60来计算
        if ($stats_interval > 60) {
            $stats_interval = 60;
        } else if ($stats_interval < 10) {
            $stats_interval = 10;
        }*/

        foreach ($data as $item) {
            $one = json_decode($item, true);
            //print_r($one);

            // 数据格式错误，直接丢掉
            if (!$one) {
                Yii::$app->redis->lpop(self::LIST_PORT);
                continue;
            }

            // 判断数据是否正确，不正确的数据直接丢掉
            if (!isset($one['gateway_id'], $one['if_port'], $one['mac'], $one['ip'], $one['pkg_num'], $one['bytes'], $one['time'])) {
                Yii::$app->redis->lpop(self::LIST_PORT);
                continue;
            }

            $model = new PortInfo();
            $model->add_time = $one['time'];
            unset($one['time']);
            if ($model->load($one, '') && $model->save()) {
                // 弹出队列
                Yii::$app->redis->lpop(self::LIST_PORT);

                // 统计端口数据, 进行统计，根据时间判断当前时间应该放到哪个时间段内，时间肯定在一个小时内，比如60，那么
                // 当前的分钟数
                /*$time = $this->_getTime($model->add_time, $stats_interval);

                $statsModel = StatsPort::find()->where(['gateway_id' => $model->gateway_id, 'if_port' => $model->if_port, 'time' => $time])->one();
                if (!$statsModel) {
                    $statsModel = new StatsPort();
                    $statsModel->gateway_id = $model->gateway_id; // 网关id
                    $statsModel->if_port = $model->if_port; // 网口号
                    $statsModel->time = $time;
                }

                // 重新计算温度，湿度，震动 ，温度和湿度用一位小数保存，震动保存是整数
                $statsModel->pkg_num = sprintf("%.2f", ($statsModel->pkg_num + $model->pkg_num)/2);
                $statsModel->bytes = sprintf("%.2f", ($statsModel->bytes + $model->bytes)/2);
                $statsModel->pps = sprintf("%.2f", ($statsModel->pps + $model->pps)/2);
                $statsModel->bandwidth = sprintf("%.2f", ($statsModel->bandwidth + $model->bandwidth)/2);
                $statsModel->total += 1; // 统计数+1

                $statsModel->save();*/

                //break;
            } else {
                $this->_writeLog(yii\helpers\Json::encode($model->errors));
                echo yii\helpers\Json::encode($model->errors);
                break;
            }
        }

        return 0;
    }

    // 环境信息
    public function actionEnv()
    {
        $data = Yii::$app->redis->lrange(self::LIST_ENV, 0, -1);
        if (empty($data)) {
            return 0;
        }

        // 统计时间间隔，必须大于10，小于60
        /*$stats_interval = Yii::$app->systemConfig->getValue('STATS_ INTERVAL_GATEWAY_ENV', 60);
        // 如果超过60分钟，那么按照60来计算
        if ($stats_interval > 60) {
            $stats_interval = 60;
        } else if ($stats_interval < 10) {
            $stats_interval = 10;
        }*/

        //print_r($data);
        foreach ($data as $item) {
            $one = json_decode($item, true);
            //var_dump($one);

            // 数据格式错误，直接丢掉
            if (!$one) {
                Yii::$app->redis->lpop(self::LIST_ENV);
                continue;
            }

            // 判断数据是否正确，不正确的数据直接丢掉
            if (!isset($one['gateway_id'], $one['temperature'], $one['humidity'], $one['location'], $one['vibration'], $one['time'])) {
                Yii::$app->redis->lpop(self::LIST_PORT);
                continue;
            }

            $model = new Env();
            $model->add_time = $one['time'];
            unset($one['time']);
            if ($model->load($one, '') && $model->save()) {
                // 弹出队列
                Yii::$app->redis->lpop(self::LIST_ENV);

                // 进行统计，根据时间判断当前时间应该放到哪个时间段内，时间肯定在一个小时内，比如60，那么
                //echo $current_timezone = date_default_timezone_get();
                // 当前的分钟数
                /*$time = $this->_getTime($model->add_time, $stats_interval);

                $statsModel = StatsEnv::find()->where(['gateway_id' => $model->gateway_id, 'time' => $time])->one();
                if (!$statsModel) {
                    $statsModel = new StatsEnv();
                    $statsModel->gateway_id = $model->gateway_id;
                    $statsModel->time = $time;
                }

                // 重新计算温度，湿度，震动 ，温度和湿度用一位小数保存，震动保存是整数
                $statsModel->temperature = sprintf("%.1f", ($statsModel->temperature + $model->temperature)/2);
                $statsModel->humidity = sprintf("%.1f", ($statsModel->humidity + $model->humidity)/2);
                $statsModel->vibration = ceil(($statsModel->vibration + $model->vibration)/2);
                $statsModel->total += 1; // 统计数+1

                $statsModel->save();*/
                //echo $statsModel->id;exit;

                //break;
            } else {
                $this->_writeLog(yii\helpers\Json::encode($model->errors));
                echo yii\helpers\Json::encode($model->errors);
                break;
            }
        }

        return 0;
    }

    /**
     * 根据给定的时间和时间间隔计算出应该属于哪个时间
     * @param $time
     * @param $stats_interval
     * @return false|int
     */
    /*private function _getTime($time, $stats_interval)
    {
        $minute = date('i', $time) + 0; // 转换为int，并且将08转换为8的形式
        $currentDate = date('Y-m-d H:00:00', $time); // 默认显示当前小时
        for ($i=0; $i<60/$stats_interval; $i++) {
            if ($i*$stats_interval <= $minute && ($i+1)*$stats_interval > $minute) {
                $currentMinute = $i * $stats_interval;
                $currentDate = date('Y-m-d H:'.$currentMinute.':00', $time);
            }
        }
        return strtotime($currentDate);
    }*/

    // 消息
    public function actionMsg()
    {
        // 网关消息类型
        $gateway_msg_type = Yii::$app->systemConfig->getValue('GATEWAY_MSG_TYPE_LIST', []);

        $data = Yii::$app->redis->lrange(self::LIST_MSG, 0, -1);
        if (empty($data)) {
            return 0;
        }

        foreach ($data as $item) {
            $one = json_decode($item, true);

            // 数据格式错误，直接丢掉
            if (!$one) {
                Yii::$app->redis->lpop(self::LIST_MSG);
                continue;
            }

            // 判断数据是否正确，不正确的数据直接丢掉
            if (!isset($one['gateway_id'], $one['if_port'], $one['type'], $one['content'], $one['time'])) {
                Yii::$app->redis->lpop(self::LIST_MSG);
                continue;
            }

            // @TODO 把数据进行整理，放到消息表

            // 先简单处理，把消息放到mysql中，发送给对应的管理员，并生成工单
            $model = new GatewayMessage();
            $model->created_time = $one['time'];
            //$model->load($one, '');print_r($model);exit;
            if ($model->load($one, '') && $model->save()) {
                // 弹出队列
                Yii::$app->redis->lpop(self::LIST_MSG);
            } else {
                // 将数据压入尾部，等待下一轮处理
                Yii::$app->redis->rpush(self::LIST_MSG, $item);

                // 弹出队列
                Yii::$app->redis->lpop(self::LIST_MSG);

                // 写日志
                $this->_writeLog(yii\helpers\Json::encode($model->errors));
                echo yii\helpers\Json::encode($model->errors);
                break;
            }
        }
    }

    /**
     * 把数据库中没有定位信息的，查找hash中的数据，然后更新到数据库中
     */
    public function actionGatewayLocation()
    {
        // 搜索所有还没有位置的网关，然后从hash中获取位置，并更新到数据库中
        $data = Gateway::find()->where(['=', 'longitude', 0])->all();
        $ok = $error = 0;
        foreach ($data as $model) {
            /* @var $model Gateway */
            $location = Yii::$app->redis->hget('hash:gateway:'.$model->gateway_id, 'location');
            if (!$location) {
                continue;
            }

            // 将gps坐标转换为高德坐标
            $loc = Gps::gps_gcj02($location);
            $model->longitude = $loc['lon'];
            $model->latitude = $loc['lat'];
            if ($model->save()) {
                $ok ++;
            } else {
                $error ++;
            }
        }

        echo 'GatewayLocation,Ok:'.$ok.',Error:'.$error;
    }

    /**
     * 如果数据更新时间距离现在一定时间，那么自动更新网关的状态为离线
     */
    public function actionGatewayState()
    {
        // 搜索所有数据时间小于当前时间5分组的并且状态是正常的，设置为离线状态
        // 网关离线时间间隔,单位：分钟，如果不设置，默认5分钟；
        $offline_interval = Yii::$app->systemConfig->getValue('GATEWAY_OFFLINE_INTERVAL', 5);
        $timestamp = time()-$offline_interval*60; // 时间戳
        $data = Gateway::find()
            ->where(['<', 'data_update_at', $timestamp])
            ->andWhere(['state' => Gateway::STATE_NORMAL])
            ->all();
        //print_r($data);
        $ok = $error = 0;
        foreach ($data as $item) {
            /* @var $item Gateway*/
            //echo $item->gateway_id;echo "\r\n";
            $item->state = Gateway::STATE_OFFLINE;
            if ($item->save()) {
                $ok ++;
            } else {
                $error ++;
            }
        }

        echo 'GatewayState,Ok:'.$ok.',Error:'.$error;
    }
    

}