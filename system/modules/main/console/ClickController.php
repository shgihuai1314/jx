<?php
/**
 * yd-service.
 * User: ligang
 * Date: 2018/1/26 下午5:57
 */

namespace system\modules\main\console;

use system\modules\main\models\ClickCount;
use system\modules\main\models\ClickLog;
use system\modules\main\models\CpuRecord;
use system\modules\main\models\Flow;
use system\modules\main\models\LoadRecord;
use system\modules\main\models\MemoryRecord;
use system\modules\main\models\RealTimeFlow;
use system\modules\main\models\RealTimeMemory;
use system\modules\main\models\RealTimeCpu;
use system\modules\main\models\RealTimeLoad;
use yii\helpers\ArrayHelper;
use yii\console\Controller;

class ClickController extends Controller
{
    public $time_record;

    //周统计点击量
    public function actionWeek()
    {
        $start_at = mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'));
        $end_at = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        $log = ClickLog::find()
            ->where([
                'and',
                ['>=', 'data_time', $start_at],
                ['<', 'data_time', $end_at],
            ])
            ->asArray()
            ->all();

        $click_count = new ClickCount();
        $click_count->addCount($log, 1, $start_at, $end_at);

    }

    //月统计点击量
    public function actionMonth()
    {
        $start_at = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
        $end_at = mktime(0, 0, 0, date('m'), 1, date('Y'));

        $log = ClickLog::find()
            ->where([
                'and',
                ['>=', 'data_time', $start_at],
                ['<', 'data_time', $end_at],
            ])
            ->asArray()
            ->all();

        $click_count = new ClickCount();
        $click_count->addCount($log, 2, $start_at, $end_at);

    }

    //年统计点击量
    public function actionYear()
    {
        //开始与截止时间戳
        $start_at = mktime(0, 0, 0, 1, 1, date('Y') - 1);
        $end_at = mktime(0, 0, 0, 1, 1, date('Y'));

        $log = ClickLog::find()
            ->where([
                'and',
                ['>=', 'data_time', $start_at],
                ['<', 'data_time', $end_at],
            ])
            ->asArray()
            ->all();

        $click_count = new ClickCount();
        $click_count->addCount($log, 3, $start_at, $end_at);

    }

    /**
     * 实时流量
     * @return bool
     *
     */
    public function actionFlow()
    {
        $startTime = strtotime(date('Y-m-d 00:00:00'));
        $endTime = strtotime(date('Y-m-d 23:59:59'));
        
        //网卡流量
        $argv = $this->GetNetWork();
        $dev =  isset($argv['NetWorkName'][3])?$argv['NetWorkName'][3]:"eth0";
        
        $result = file_get_contents("/proc/net/dev");
        preg_match("/{$dev}:[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $result, $info_arr);
        $NetOutSpeed = $info_arr[1];//上行总流量
        $NetInputSpeed = $info_arr[9];//下行总流量

        $model = new RealTimeFlow();

        //查询最新的流量数据
        $id = RealTimeFlow::find()->max('id');
        $item = RealTimeFlow::findOne($id);

        $params['up_side'] = $this->formatBytes($item ? intval($NetInputSpeed) - $item['net_input_speed'] : '0');
        $params['down_side'] = $this->formatBytes($item ? intval($NetOutSpeed) - $item['net_out_speed'] : '0');
        $params['net_out_speed'] = $NetOutSpeed;
        $params['net_input_speed'] = $NetInputSpeed;
        $params['create_at'] = time();

        if ($model->load($params, '') && $model->save()) {
            if (time() == strtotime(date('Y-m-d 23:59:59'))) {//4 1533312000 5 1533398400 6 1533484800 7 1533571200 8  1533657599
                $data = RealTimeFlow::find()
                    ->select(['round(avg(up_side),2) as  up_side','round(avg(down_side),2) as  down_side'])
                    ->where(['between','create_at' , $startTime,$endTime])
                    ->groupBy("`create_at`-`create_at`% (10*60)")
                    ->asArray()
                    ->all();
    
                //解析数据
                $up_side = ArrayHelper::getColumn($data , 'up_side');
                $down_side = ArrayHelper::getColumn($data , 'down_side');
            
                $flow = new Flow();
                $flow->up_side = json_encode($up_side);
                $flow->down_side = json_encode($down_side);
                $flow->create_at = $startTime;
                $flow->save();
            }
        }
    }

    /**
     * 单位转换
     * @param $size
     * @return float
     *
     */
    function formatBytes($size)
    {
        $size = round($size / 1024 * 100) / 100;
        return $size;
    }

    /**
     * cup使用率
     * @return bool
     */
    public function actionCpuRecord()
    {
        $mode = "/(cpu)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)/";
        $string = shell_exec("more /proc/stat");
        preg_match_all($mode, $string, $arr);

        $total1 = $arr[2][0] + $arr[3][0] + $arr[4][0] + $arr[5][0] + $arr[6][0] + $arr[7][0] + $arr[8][0] + $arr[9][0];
        $time1 = $arr[2][0] + $arr[3][0] + $arr[4][0] + $arr[6][0] + $arr[7][0] + $arr[8][0] + $arr[9][0];

        sleep(1);
        $string = shell_exec("more /proc/stat");
        preg_match_all($mode, $string, $arr);
        $total2 = $arr[2][0] + $arr[3][0] + $arr[4][0] + $arr[5][0] + $arr[6][0] + $arr[7][0] + $arr[8][0] + $arr[9][0];
        $time2 = $arr[2][0] + $arr[3][0] + $arr[4][0] + $arr[6][0] + $arr[7][0] + $arr[8][0] + $arr[9][0];
        $time = $time2 - $time1;
        $total = $total2 - $total1;

        $percent = bcdiv($time, $total, 3);
        $percent = round($percent * 100, 2);

        $cpu_model = new RealTimeCpu();
    
        $cpu_model->cpu_usage = $percent;
        $cpu_model->create_at = time();
        $cpu_model->save();
        
    }

    /**
     * 内存使用率
     * @return bool
     */
    public function actionMemoryRecord()
    {
        //内存
        if (false === ($str = @file("/proc/meminfo"))) return false;
        $str = implode("", $str);
        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
        preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

        $res['memTotal'] = round($buf[1][0] / 1024, 2);
        $res['memFree'] = round($buf[2][0] / 1024, 2);
        $res['memBuffers'] = round($buffers[1][0] / 1024, 2);
        $res['memCached'] = round($buf[3][0] / 1024, 2);
        $res['memRealUsed'] = $res['memTotal'] - $res['memFree'] - $res['memCached'] - $res['memBuffers']; //真实内存使用
        $res['memRealPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memRealUsed'] / $res['memTotal'] * 100, 2) : 0; //真实内存使用率

        $memory_model = new RealTimeMemory();
        $memory_model->memory_usage = $res['memRealPercent'];
        $memory_model->create_at = time();
     
        $memory_model->save();
    }

    /**
     * 系统负载率
     */
    public function actionLoadRecord()
    {
        // LOAD AVG 系统负载
        $load = $this->get_system_load();

        $load_model = new RealTimeLoad();
        $load_model->load_usage = $load['1min'];
        $load_model->create_at = time();
        $load_model->save();
    }
    
    /**
     * 服务器每天的性能变化
     *
     */
    public function actionRecord()
    {
        $startTime = strtotime(date('Y-m-d 00:00:00',strtotime("-1 day")));
        $endTime = strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")));
    
        //内存//17 1534435200 16 1534348800 15 1534262400 14 1534176000 13 1534089600 12  1534003200 1533916800 1533830400
        $data = RealTimeMemory::find()
            ->select(['round(avg(memory_usage),2) as  memory_usage'])
            ->where(['between','create_at' , $startTime,$endTime])
            ->groupBy("`create_at`-`create_at`% (10*60)")
            ->asArray()
            ->all();
    
        //解析数据
        $item_memory = json_encode(ArrayHelper::getColumn($data , 'memory_usage'));
    
        $memory = new MemoryRecord();
        $memory->memory = $item_memory;
        $memory->create_at = $startTime;
        $memory->save();
       
        //负载率
        $data = RealTimeLoad::find()
            ->select(['round(avg(load_usage),2) as  load_usage'])
            ->where(['between','create_at' , $startTime,$endTime])
            ->groupBy("`create_at`-`create_at`% (10*60)")
            ->asArray()
            ->all();
       
        //解析数据
        $item_load = json_encode(ArrayHelper::getColumn($data , 'load_usage'));
 
        $load = new LoadRecord();
        $load->load = $item_load;
        $load->create_at = $startTime;
        $load->save();
        
        //cpu
        $data = RealTimeCpu::find()
            ->select(['round(avg(cpu_usage),2) as  cpu_usage'])
            ->where(['between','create_at' , $startTime,$endTime])
            ->groupBy("`create_at`-`create_at`% (10*60)")
            ->asArray()
            ->all();
    
        //解析数据
        $item_cpu = json_encode(ArrayHelper::getColumn($data , 'cpu_usage'));
    
        $cpu = new CpuRecord();
        $cpu->cpu = $item_cpu;
        $cpu->create_at = $startTime;
        $cpu->save();
    }

    /**
     * 系统负载
     * @return array|string
     */
    function get_system_load()
    {
        if (strtolower(PHP_OS) != 'linux') {
            return '';
        }

        $output = '';
        $result_status = '';
        $command = 'uptime';
        exec($command, $output, $result_status);
        if ($result_status == 0) {
            $arr = [];
            preg_match("/load average:\s+(.*)$/", reset($output), $arr);
            if (is_array($arr)) {
                $loadStr = end($arr);
                $loadArr = explode(',', $loadStr);
                $load = [
                    '1min' => trim($loadArr[0]),
                    '5min' => trim($loadArr[1]),
                    '15min' => trim($loadArr[2]),
                ];
                return $load;
            }
        }
    }
    
    /**
     * 网卡信息
     * @return mixed
     */
    public function GetNetWork(){
        $strs = @file("/proc/net/dev");
        $lines = count($strs);
        for ($i=2; $i < $lines; $i++) {
            preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info );
            $res['NetWorkName'][$i] = $info[1][0];
        }
        return $res;
    }
}