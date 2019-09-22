<?php

namespace system\modules\main\controllers;

use system\core\utils\Tool;
use system\modules\user\models\Group;
use system\modules\main\models\Modules;
use system\modules\main\models\RealTimeFlow;
use system\modules\main\models\RealTimeLoad;
use system\modules\main\models\RealTimeMemory;
use system\modules\main\models\RealTimeCpu;
use system\modules\user\models\Position;
use system\modules\user\models\User;
use yii\filters\AccessControl;

/**
 * 默认控制器，负责：主布局，默认的错误页面，欢迎页面
 */
class DefaultController extends BaseController
{
    public $ignoreList = [
        'main/default/index', // 布局页面
        'main/default/welcome', //欢迎页面
        //'main/default/unsupported-browser', //根据id获取列表选项
    ];

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'except' => ['unsupported-browser'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    /**
     * 主页布局
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = 'main-frame';
        return $this->render('index');
    }

    /**
     * 欢迎页面，包含基本系统信息
     * @return string
     */
    public function actionWelcome()
    {

        //判断服务器类型
        $systemName = Tool::getSystemName();
        if($systemName == 'linux'){
            return $this->render('welcome');
        }else{
            // 页面展示的数据
            // 系统数据：用户总数，部门总数，职位总数，管理员数量，应用总数
            // 系统信息：服务器信息，cpu负载，剩余磁盘，数据库大小，等信息

            $userCount = User::find()->count();
            $adminCount = User::find()->where(['is_admin' => 1])->count();
            $positionCount = Position::find()->count();
            $groupCount = Group::find()->count();

            $onlineAt = date('Y-m-d', Modules::findOne(['module_id' => 'main'])->create_at);
            return $this->render('welcome2', [
                'data' => [
                    'userCount' => $userCount,
                    'adminCount' => $adminCount,
                    'positionCount' => $positionCount,
                    'groupCount' => $groupCount,
                    'systemName' => $systemName,
                    'onlineAt' => $onlineAt
                ],
            ]);
        }
    }
    

    public function actionSystemConfig()
    {
        //内存
        $memory_id = RealTimeMemory::find()->max('id');
        $memory_data = RealTimeMemory::findOne($memory_id);

        //cpu
        $cpu_id = RealTimeCpu::find()->max('id');
        $cpu_data = RealTimeCpu::findOne($cpu_id);

        //负载率
        $load_id = RealTimeLoad::find()->max('id');
        $load_data = RealTimeLoad::findOne($load_id);


        //内存
        if (false === ($str = @file("/proc/meminfo"))) return false;
        $str = implode("", $str);
        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
        preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

        $res['memTotal'] = round($buf[1][0]/1024, 2);
        $res['memFree'] = round($buf[2][0]/1024, 2);
        $res['memBuffers'] = round($buffers[1][0]/1024, 2);
        $res['memCached'] = round($buf[3][0]/1024, 2);
        $res['memRealUsed'] = $res['memTotal'] - $res['memFree'] - $res['memCached'] - $res['memBuffers']; //真实内存使用
        $memRealUsed = round($res['memRealUsed']/1024,2)." GB"; //真实内存使用
        $memRealTotal = round($res['memTotal']/1024,2)." GB"; //真实内存空闲

        //硬盘空间
        $fp = popen('df -lhP | grep -E "^(/)"', "r");//popen('df -lhP | gre -E "^(/)"',"r");
        $rs = fread($fp, 1024);
        pclose($fp);
        $rs = preg_replace("/\s{2,}/", ' ', $rs);  //把多个空格换成 “_”
        $hd = explode(" ", $rs);

        $total_space = trim($hd[1], 'G');//磁盘总容量
        $use_space = trim($hd[2], 'G'); //磁盘已使用 单位G
        $user_percent = substr($hd[4], 0, strlen($hd[4]) - 1);;//已使用的百分比

        $home_total_space = trim($hd[11]);
        $home_use_space = trim($hd[12]);
        $home_percent = substr($hd[14], 0, strlen($hd[14]) - 1);


        //网卡流量
        $flow_id = RealTimeFlow::find()->max('id');
        $flow_data = RealTimeFlow::findOne($flow_id);
        
        //单位转换
        $NetInput = round($flow_data['net_out_speed'] / 1024 / 1024 / 1024, 2);
        $NetOut = round($flow_data['net_input_speed'] / 1024 / 1024 / 1024, 2);

        $data = [
            'pie' => [
                [
                    "value" => intval($load_data['load_usage']),
                    "name" => "运行流程",
                    "title" => "负载状态",
                    "color" => [
                        "#1988fa",
                        "#fff"
                    ],
                    "fontSize" => 16,
                    "domEle" => "pie-chart1"
                ],
                [
                    "value" => intval($cpu_data['cpu_usage']),
                    "name" => "核心",
                    "title" => "CPU使用率",
                    "color" => [
                        "#ff226b",
                        "#fff"
                    ],
                    "fontSize" => 16,
                    "domEle" => "pie-chart2"
                ],

                [
                    "value" => intval($memory_data['memory_usage']),
                    "name" => $memRealUsed . '/' . $memRealTotal,
                    "title" => "内存使用率",
                    "color" => [
                        "#00ca5b",
                        "#fff"
                    ],
                    "fontSize" => 16,
                    "domEle" => "pie-chart3"
                ],
                [
                    "value" => $user_percent,
                    "name" => $use_space . 'G/' . $total_space . 'G',
                    "title" => "/",
                    "color" => [
                        "#1988fa",
                        "#fff"
                    ],
                    "fontSize" => 16,
                    "domEle" => "pie-chart4"
                ],
                [
                    "value" => $home_percent,
                    "name" => $home_use_space . '/' . $home_total_space,
                    "title" => "HOME",
                    "color" => [
                        "#ffbb00",
                        "#fff"
                    ],
                    "fontSize" => 16,
                    "domEle" => "pie-chart5"
                ],
            ],
            "bar" => [

                "title" => "网络流量",
                "yAxis" => ["下行速度(" . $flow_data['down_side']."kb)", "上行速度(" . $flow_data['up_side']."kb)", "总发送(" . $NetOut . 'GB)', "总接收(" . $NetInput . 'GB)'],
                "data" => [$flow_data['up_side'], $flow_data['down_side'], $NetOut, $NetInput],
            ],
            "totalBar" => [
                "title" => "网络流量",
                "yAxis" => ["下行速度(" . $flow_data['down_side']."kb)", "上行速度(" . $flow_data['up_side']."kb)", "总发送(" . $NetOut . 'GB)', "总接收(" . $NetInput . 'GB)'],
                "data" => [$flow_data['up_side'], $flow_data['down_side'], $NetOut, $NetInput],
            ],
            "line" => [
                "title" => "实时流量",
                "legend" => ["上行", "下行"],
                "xAxis" => ["6:00AM", "6:30AM", "7:00AM", "7:30AM", "8:00AM", "8:30AM", "9:00AM"],
                "unit" => "单位(MB/30m)",
                "data1" => [820, 932, 901, 934, 1290, 1330, 1320],
                "data2" => [15, 585, 5244, 2, 1290, 2, 1920],
            ],
            "speed" => [
                'NetOutSpeed' => $flow_data['up_side'],
                'NetInputSpeed' => $flow_data['down_side'],
                'AlwaysSend' => $NetOut,
                'AlwaysReceive' => $NetInput,

            ],
        ];

        return $this->ajaxReturn([
            'code' => 0,
            'data' => $data,
        ]);
    }

    // 不支持的浏览器
    public function actionUnsupportedBrowser()
    {
        return $this->renderPartial('browserUpgrade');
    }

    // Linux 获取服务器信息
    private function get_used_status()
    {
        $fp = popen('top -b -n 2 | grep -E "^(Cpu|Mem|Tasks)"', "r");//获取某一时刻系统cpu和内存使用情况
        $rs = "";
        while (!feof($fp)) {
            $rs .= fread($fp, 1024);
        }
        pclose($fp);
        $sys_info = explode("\n", $rs);
        $task_info = explode(",", $sys_info[3]);//进程 数组
        $cpu_info = explode(",", $sys_info[4]);  //CPU占有量  数组
        $mem_info = explode(",", $sys_info[5]); //内存占有量 数组
        //正在运行的进程数
        $task_running = trim(trim($task_info[1], 'running'));

        //CPU占有量
        $cpu_usage = trim(trim($cpu_info[0], 'Cpu(s): '), '%us');  //百分比

        //内存占有量
        $mem_total = trim(trim($mem_info[0], 'Mem: '), 'k total');
        $mem_used = trim($mem_info[1], 'k used');
        $mem_usage = round(100 * intval($mem_used) / intval($mem_total), 2);  //百分比

        /*硬盘使用率 begin*/
        $fp = popen('df -lh | grep -E "^(/)"', "r");
        $rs = fread($fp, 1024);
        pclose($fp);
        $rs = preg_replace("/\s{2,}/", ' ', $rs);  //把多个空格换成 “_”
        $hd = explode(" ", $rs);
        $hd_avail = trim($hd[3], 'G'); //磁盘可用空间大小 单位G
        $hd_usage = trim($hd[4], '%'); //挂载点 百分比
        //print_r($hd);
        /*硬盘使用率 end*/

        //检测时间
        $fp = popen("date +\"%Y-%m-%d %H:%M\"", "r");
        $rs = fread($fp, 1024);
        pclose($fp);
        $detection_time = trim($rs);

        /*获取IP地址  begin*/
        /*
        $fp = popen('ifconfig eth0 | grep -E "(inet addr)"','r');
        $rs = fread($fp,1024);
        pclose($fp);
        $rs = preg_replace("/\s{2,}/",' ',trim($rs));  //把多个空格换成 “_”
        $rs = explode(" ",$rs);
        $ip = trim($rs[1],'addr:');
        */
        /*获取IP地址 end*/
        /*
        $file_name = "/tmp/data.txt"; // 绝对路径: homedata.dat
        $file_pointer = fopen($file_name, "a+"); // "w"是一种模式，详见后面
        fwrite($file_pointer,$ip); // 先把文件剪切为0字节大小， 然后写入
        fclose($file_pointer); // 结束
        */

        return array('cpu_usage' => $cpu_usage, 'mem_usage' => $mem_usage, 'hd_avail' => $hd_avail, 'hd_usage' => $hd_usage, 'tast_running' => $task_running, 'detection_time' => $detection_time);
    }
}