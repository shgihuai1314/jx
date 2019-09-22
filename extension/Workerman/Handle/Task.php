<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-6-7
 * Time: 11:45
 */

namespace Workerman\Handle;

use system\modules\cron\models\Cron;
use Workerman\Lib\Timer;

class Task
{
    // 定时器列表，保存正在执行的定时器ID
    public static $TimerList = [];

    /**
     * Worker启动时的回调函数
     * @param $worker
     */
    public function workerStart($worker)
    {
//        echo "Worker starting at " . date('Y-m-d H:i:s') . "...\n";

        // 每隔一秒扫描一次任务列表，检查任务是否有变动
        Timer::add(1, function () {
            $cronList = Cron::getCronList();
            foreach ($cronList as $index => $one) {
                // 任务索引是否在定时器列表中，不再则添加定时任务
                if (!in_array($index, array_keys(self::$TimerList)) && $one['start_time'] == time()) {
                    self::$TimerList[$index] = Timer::add($one['interval_time'], function () use ($one) {
                        $command = $this->getCommand($one['task']);
                        file_put_contents("./taskLog.log", date('Y-m-d H:i:s')." $command \r\n", FILE_APPEND);
                        system($command);
                    });
                }
            }
            foreach (self::$TimerList as $index => $timerId) {
                // 定时器列表中的任务ID不再任务列表中，删除该定时器
                if (!in_array($index, array_keys($cronList))) {
                    Timer::del($timerId);
                }
            }
        });
    }

    /**
     * 获取执行命令
     * @param $task
     * @return mixed|string
     */
    private function getCommand($task)
    {
        switch ($task['type']) {
            case 0:
                $command = 'php ./../../../yii ' . $task['command'];

                if (file_exists(\Yii::getAlias('@system') . '/modules/' . $task['module_id'] . '/console/config.php')) {
                    $command = $command . ' --appconfig=@system/modules/' . $task['module_id'] . '/console/config.php';
                }
                break;
            case 1:
                $command = $task['command'];
                preg_match_all("/\s*{(.*?)}\s*/is", $command, $matchs);

                $replace = [];
                if (!empty($matchs[1])) {
                    foreach ($matchs[1] as $key => $val) {
                        $str = '';
                        eval('$str = ' . $val . ";");
                        $replace["{" . $val . "}"] = $str;
                    }
                }

                foreach ($replace as $key => $val) {
                    $command = str_replace($key, $val, $command);
                }
                break;
            case 2:
                $command = 'php ' . $task['command'];
                break;
            default:
                $command = '';
                break;
        }
        echo $command.PHP_EOL;
        return $command;
    }

}