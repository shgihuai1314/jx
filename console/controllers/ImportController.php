<?php
/**
 * Created by PhpStorm.
 * User: THINKPAD
 * Date: 2017/11/6
 * Time: 9:39
 */

namespace console\controllers;

use system\modules\course\models\CourseLesson;
use system\modules\course\models\CourseStudent;
use system\modules\course\models\CourseTeam;
use yii;
use yii\console\Controller;
use system\modules\logs\models\IsImport;
use system\modules\logs\models\OnlineRadius;
use system\modules\logs\models\SrunDetail;
use system\modules\logs\models\Detail;
use system\modules\logs\models\Online;

class ImportController extends Controller
{
    //mongodb测试
    public function actionTest2()
    {
        $res=CourseStudent::join(317,5);
        print_r($res);die;
       $data=CourseTeam::getlensson('exam_review');
        print_r($data);
        die;
        //直接使用
        $collection = Yii::$app->mongodb->getCollection('test');

        $doc = [
            "address" => [
                "city" => "Los Angeles",
                "state" => "California",
                "pincode" => "123",
            ],
            "tags" => ["music", "cricket", "blogs"],
            "name" => "Tom Benzamin"
        ];

        $infos = $collection->insert($doc);
        print_r($infos);
        die;

        //activerecord类使用
        //$infos=test::findOne(['name'=>'test']);
        foreach ($infos as $val) {
            var_dump($val);
        }
        die;

    }

    //redis测试
    public function actionTest()
    {
        echo time() . "\n";
        $obj = Yii::$app->redis;
        //在线记录表数据
        $datas = $obj->lrange('list:rad_online', 0, -1);
        //查询数据库
        $sql = "select rad_online_id from test";
        $res = Yii::$app->db->createCommand($sql)->queryAll();

        $res = yii\helpers\ArrayHelper::getColumn($res, 'rad_online_id');

        $data = [];

        foreach ($datas as $v) {

            if (in_array($v, $res)) {
                continue;
            }
            $result = $obj->hmget("hash:rad_online:" . $v, 'rad_online_id', 'user_name', 'ip', 'add_time', 'vlan_id');


            $data[] = $result;
        }
        $filed = ['rad_online_id', 'user_name', 'ip', 'add_time', 'vlan_id'];

        Yii::$app->db->createCommand()->batchInsert('test', $filed, $data)->execute();

        //删除下线的数据
        $detail = $obj->lrange('list:detail', 0, -1);

        foreach ($detail as $val) {
            $sql = "delete from test WHERE rad_online_id={$val}";
            Yii::$app->db->createCommand($sql)->execute();
        }
        echo time() . "\n";

    }

    /**
     * 同步深澜的在线记录表
     */
    public function actionOnlineData()
    {
        //清空在线记录表
        $sql = "truncate table online_radius";
        Yii::$app->db->createCommand($sql)->execute();

        //查询深澜的所有在线记录并插入在线记录表里面
        $onlineDataCount = Online::find()->asArray()->count();

        $offset = 0;
        while ($offset <= $onlineDataCount) {
            $onlineData = Online::find()->asArray()->offset($offset)->limit(1000)->all();

            //获取在线表的所有字段，放入一个数组里面
            $onlineKey = (new OnlineRadius())->attributes();

            $res = Yii::$app->db->createCommand()->batchInsert('online_radius', $onlineKey, $onlineData)->execute();
            $offset += 1000;
            if ($res > 0) {
                echo $offset . " import success\r\n";
            } elseif ($res == 0) {
                echo "no data import";
            } else {
                echo "import fail";
            }
        }
        echo "import complete";

    }

    /**
     * 同步深澜在线明细表
     */
    public function actionDetailData()
    {
        //当天的0点的时间戳和第二天的0点时间戳
        $start_time = strtotime(date('Y-m-d', strtotime("-1 day")));
//        print_r($start_time);die;
        //查询深澜当天的上网记录并插入明细表里面
        $detailDataCount = Detail::find()->where(['and', ['>=', 'add_time', $start_time]])->asArray()->count();
        $offset = 0;
        while ($offset <= $detailDataCount) {
            $detailData = Detail::find()->where(['and', ['>=', 'add_time', $start_time]])->asArray()->offset($offset)->limit(1000)->all();

            //获取上网明细表的所有字段，放入一个数组里面
            $detailKey = (new SrunDetail())->attributes();
            foreach ($detailData as $key => $val) {

                $info = SrunDetail::find()->where(['detail_id' => $val['detail_id']])->asArray()->one();
                if ($info) {
                    unset($detailData[$key]);
                }
            }
            $res = Yii::$app->db->createCommand()->batchInsert('srun_detail', $detailKey, $detailData)->execute();
//            echo 222;die;
            $offset += 1000;
            if ($res > 0) {
                echo $offset . " import success\r\n";
            } elseif ($res == 0) {
                echo "no data import\r\n";
            } else {
                echo "import fail\r\n";
            }
        }
        echo "import complete";
    }

    //获取深澜在线记录，并放到一个数组里面
    public function getLocalOnlineData()
    {
        ini_set('memory_limit', '-1');
        $all_online_data = [];
        $online_info = OnlineRadius::find()->select(['user_name', 'add_time', 'ip', 'nas_ip'])->asArray()->all();
        foreach ($online_info as $k => $v) {
            $all_online_data[$v['ip']] = $v;
        }
        return $all_online_data;
    }

    //获取深澜上网明细，并放到一个数组里面
    public function getLocalDetailData()
    {
        ini_set('memory_limit', '-1');
        $all_detail_data = [];
        $surf_info = SrunDetail::find()->select(['user_name', 'add_time', 'drop_time', 'user_ip', 'nas_ip'])->asArray()->all();
        foreach ($surf_info as $k => $v) {
            $all_detail_data[$v['user_ip']][] = $v;
        }
        return $all_detail_data;
    }

    /**
     * 解压AD日志文件
     */
    public function actionDecompressFile()
    {
        var_dump(time());
        $dir = "D:/AD/complete/";
        $gz_dir = "D:/AD/";
        $gz_allFile = $this->getDirAllFile($gz_dir);
//        print_r($gz_allFile);die;
        $this->decompressFile($gz_allFile, $dir, $gz_dir);//解压当天的文件
        //读取文件数据
        echo "正在压缩";
        echo "\n";
        var_dump(time());
    }

    /**
     * 解压压缩包,并存表
     * @param $gz_allFile
     * @param $dir
     * @param $gz_dir
     */
    public function decompressFile($gz_allFile, $dir, $gz_dir)
    {
        $now = date('Y-m-d', time());//获取当天的时间

        foreach ($gz_allFile as $item) {
            if (strpos($item, $now) !== false) {
                $info = IsImport::find()->where(['file_name' => $item, 'status' => 2])->one();
                if ($info) {
                    continue;
                } else {
                    $model = new IsImport();
                    $model->file_name = $item;
                    $model->status = 1;
                    $model->save();
                }
            }
            $inf = IsImport::find()->where(['file_name' => $item, 'status' => 1])->one();
//            print_r($inf);die;
            if ($inf) {
                $out_file_name = $dir . str_replace('.gz', '', $inf->file_name);
                //判断是否是个文件
                if (!is_file($out_file_name)) {
                    $buffer_size = 4096;//字符串长度
                    $file_name = gzopen($gz_dir . $inf->file_name, 'rb');//解压压缩包
                    $out_file = fopen($out_file_name, 'wb');//打开文件
                    while (!gzeof($file_name)) {//是否压缩完
                        fwrite($out_file, gzread($file_name, $buffer_size));//写入
                    }
                    fclose($out_file);//关闭打开的文件
                    gzclose($file_name);//关闭压缩包
                }
                //修改文件导入状态
                $info_new = IsImport::find()->where(['file_name' => $inf->file_name])->one();
                $info_new->status = 2;
                $info_new->save();
            }
        }
    }

    /**
     * 获取目录下的所目录及文件
     * @param $dir
     * @return array
     */
    public function getDirAllFile($dir)
    {
        $all = scandir($dir);
        $allFile = [];
        foreach ($all as $v) {
            if (is_file($dir . $v)) {
                $allFile[] = $v;
            }
        }
        return $allFile;
    }

    //同步AF日志数据
    public function actionAfLogData()
    {
        var_dump("start time:" . time());
        $yesterday = date("Y-m-d", strtotime("-1 day"));
        $afDir = "D:/AF/";
        $afNewDir = "D:/AF/complete/";

        $allFile = $this->getDirAllFile($afDir);
//        var_dump($allFile);die;

        foreach ($allFile as $file) {
//            echo 222;die;
            $sub = substr($file, -14, -4);
            if ($sub == $yesterday) {
                $fileName = $afDir . $file;
                $h = fopen($fileName, "r+");
                $this->addData($h);
                $res = copy($fileName, $afNewDir . $file);
                if ($res) {
                    unlink($fileName); //删除旧目录下的文件
                }
                var_dump("file time:" . time());
            }
        }
        var_dump("import time:" . time());

    }

    //添加数据到af日志的数据表
    public function addData($handle)
    {
        $all_online_data = $this->getLocalOnlineData();
        $all_detail_data = $this->getLocalDetailData();
//        var_dump($all_detail_data);die;
        $date = date("Y_m", time());
        $allTableName = ['af_web_' . $date, 'af_sys_' . $date, 'af_ips_' . $date, 'af_other_' . $date];
        $r = Yii::$app->syetemMysql->createAllTable($allTableName);

        if ($r && $handle) {
            // 文件load data
            $absoluteDir = Yii::getAlias('@webroot') . '/upload/datafile/';
            //创建文件所在的目录
            if (!yii\helpers\FileHelper::createDirectory($absoluteDir)) {
                return false;
            }

            $web_filename = $absoluteDir . 'af_web_' . $date . '.sql';
            $web_fp = fopen($web_filename, 'w');

            $sys_filename = $absoluteDir . 'af_sys_' . $date . '.sql';
            $sys_fp = fopen($sys_filename, 'w');

            $ips_filename = $absoluteDir . 'af_ips_' . $date . '.sql';
            $ips_fp = fopen($ips_filename, 'w');

            $other_filename = $absoluteDir . 'af_other_' . $date . '.sql';
            $other_fp = fopen($other_filename, 'w');

            $i = 1;
            while (!feof($handle)) {
                $buffer = fgets($handle, 4096);
                $buffer = iconv('GB2312', 'UTF-8//IGNORE', $buffer);
                if ($buffer) {
                    $res = explode("\t", $buffer);

                    if (strpos($res[3], 'URL:') !== false) {
                        $pre_off = substr(strstr($res[3], 'URL:', true), 0, -2);
                        $aft_off = strstr($res[3], 'URL:');
                        $arr = explode(",", $pre_off);
                        $arr[] = $aft_off;
                    } else {
                        $arr = explode(",", $res[3]);
                    }
                    $type = $arr[0];
                    $log_type = explode(":", $type);
//                    var_dump($log_type[4] == "IPS防护日志");die;
                    if ($log_type[4] == "僵尸网络日志" || $log_type[4] == "WAF应用防护日志" || $log_type[4] == "威胁隔离日志" || $log_type[4] == "系统操作" || $log_type[4] == "IPS防护日志" || $log_type[4] == "web威胁" || $log_type[4] == "网站访问" || $log_type[4] == "用户认证" || $log_type[4] == "服务控制或应用控制" || $log_type[4] == "病毒查杀" || $log_type[4] == "DOS攻击日志") {
                        unset($arr[0]);

                        $time = strtotime($res[0]);
                        if ($log_type[4] == "僵尸网络日志" || $log_type[4] == "WAF应用防护日志" || $log_type[4] == "威胁隔离日志" || $log_type[4] == "IPS防护日志" || $log_type[4] == "DOS攻击日志") {

                            $source_ip_arr = explode(":", $arr[1]);
                            if ($log_type[4] == "DOS攻击日志") {
                                $nat_ip_arr = explode(":", $arr[2]);
                            } else {
                                $nat_ip_arr = explode(":", $arr[3]);
                            }
                            $s_ip = $source_ip_arr[1];
                            $n_ip = $nat_ip_arr[1];

                        } elseif ($log_type[4] == "系统操作" || $log_type[4] == "web威胁" || $log_type[4] == "网站访问" || $log_type[4] == "用户认证" || $log_type[4] == "服务控制或应用控制" || $log_type[4] == "病毒查杀") {

                            $source_ip_arr = explode(":", $arr[2]);
                            if ($log_type[4] == "web威胁" || $log_type[4] == "网站访问") {
                                $nat_ip_arr = explode(":", $arr[3]);
                                $n_ip = $nat_ip_arr[1];
                            } elseif ($log_type[4] == "服务控制或应用控制" || $log_type[4] == "病毒查杀") {
                                $nat_ip_arr = explode(":", $arr[4]);
                                $n_ip = $nat_ip_arr[1];
                            } else {
                                $n_ip = null;
                            }
                            $s_ip = $source_ip_arr[1];
                        }

                        if (isset($all_detail_data[$s_ip])) {
                            foreach ($all_detail_data[$s_ip] as $one) {
                                if ($one['add_time'] <= $time && $one['drop_time'] >= $time) {
                                    $user_name = $one['user_name'];
                                }
                            }
                        } elseif (isset($all_online_data[$s_ip])) {
                            $user_name = $all_online_data[$s_ip]['user_name'];
                        } else {
                            $user_name = null;
                        }

                        if (!isset($user_name)) {
                            if (isset($all_online_data[$n_ip])) {
                                foreach ($all_detail_data[$n_ip] as $one) {
                                    if ($one['add_time'] <= $time && $one['drop_time'] >= $time) {
                                        $user_name = $one['user_name'];
                                    }
                                }
                            } elseif (isset($all_online_data[$n_ip])) {
                                $user_name = $all_online_data[$n_ip]['user_name'];
                            } else {
                                $user_name = null;
                            }
                        }

                        $val_arr = [];
                        foreach ($arr as $v) {
                            $ex_arr = explode(":", $v);
                            if (strpos($ex_arr[1], "\r\n") !== false) {
                                $new_arr = str_replace("\r\n", "", $ex_arr[1]);
                                $val_arr[] = $new_arr;
                            } else {
                                $val_arr[] = $ex_arr[1];
                            }
                        }
                        $val_str = implode(",|,", $val_arr);
                        if ($user_name !== null) {
                            $txt = "null,|," . strtotime($res[0]) . ",|," . $res[2] . ",|," . $log_type[4] . ",|," . $val_str . ",|," . $user_name . "\r\n";

                            if ($log_type[4] == "僵尸网络日志" || $log_type[4] == "WAF应用防护日志" || $log_type[4] == "威胁隔离日志") {
                                fputs($web_fp, $txt);
                            } elseif ($log_type[4] == "系统操作") {
                                fputs($sys_fp, $txt);
                            } elseif ($log_type[4] == "IPS防护日志") {
                                fputs($ips_fp, $txt);
                            }
                        }


                    } else {//其它类型的日志保存格式
                        unset($res[1]);
                        $res[0] = strtotime($res[0]);
//                        var_dump($res);die;
                        $val_arr = [];
                        foreach ($res as $v) {
//                            $v = iconv('GB2312','UTF-8',$v);
                            if (strpos($v, "\r\n") !== false) {
                                $new_arr = str_replace("\r\n", "", $v);
                                $val_arr[] = $new_arr;
                            } else {
                                $val_arr[] = $v;
                            }
                        }
                        $val_str = implode(",|,", $val_arr);
                        $txt = "null,|," . $val_str . "\r\n";
//                        var_dump($txt);die;
                        fputs($other_fp, $txt);
                    }

                }
                $i++;

            }
            foreach ($allTableName as $tableName) {
                $filename = str_replace('\\', '/', $absoluteDir . $tableName . '.sql');
//                var_dump($tableName);
//                var_dump($filename);
                Yii::$app->db->createCommand("load data infile '$filename' into table $tableName fields terminated by ',|,'")->execute();
            }
            fclose($handle);
        }
    }

    /**
     * 释放磁盘容量
     */
    public function actionDelFile()
    {
        //获取磁盘总容量
        $all = disk_total_space("E:");
        //获取磁盘剩余容量
        $surplus = disk_free_space("E:");

        //获取磁盘使用容量
        $use = $all - $surplus;
        //使用容量大于总容量的80%就删除最早一天的文件
        if ($use >= $all * 0.8) {
            $route = "E:/adLog/";
            //年的文件夹
            $all = $this->getDir($route);
            //月的文件夹
            $all1 = $this->getDir($route . min($all) . '/');
            //日的文件夹
            $all2 = $this->getDir($route . min($all) . '/' . min($all1) . '/');
            if (!$all2) {
                rmdir($route . min($all) . '/' . min($all1));
                die;
            }
            //日下面的所有db文件
            $all3 = $this->getDirAllFile($route . min($all) . '/' . min($all1) . '/' . min($all2) . '/');
//            print_r($route . min($all) . '/' . min($all1) . '/' . min($all2) . '/');die;
            if (!$all3) {
                rmdir($route . min($all) . '/' . min($all1) . '/' . min($all2));
//                $all3 = $this->getDirAllFile($route . min($all) . '/' . min($all1) . '/' . min($all2) . '/');
            } else {
                //遍历删除最早一天的数据文件
                foreach ($all3 as $v) {
                    unlink($route . min($all) . '/' . min($all1) . '/' . min($all2) . '/' . $v);
                }
            }
        } else {
            echo "磁盘容量足够";
        }
    }

    /**
     * @param $dir
     * @return array
     * 获取目录下的所有目录
     */
    public function getDir($dir)
    {
        $all = scandir($dir);
        $allFile = [];
        foreach ($all as $v) {
            if (is_dir($dir . $v)) {
                if ($v != '.' && $v != "..")
                    $allFile[] = $v;
            }
        }
        return $allFile;
    }

    /**
     * 删删除穿过来的压缩包
     */
    public function actionDel()
    {
        //获取磁盘总容量
        $all = disk_total_space("D:");
        //获取磁盘剩余容量
        $surplus = disk_free_space("D:");
        //获取磁盘使用容量
        $use = $all - $surplus;

        $allZip = $this->getDirAllFile("D:/AD/");
        //print_r(substr($allZip[0],0,16));
        if ($use >= $all * 0.96) {
            foreach ($allZip as $k => $v) {
                if (strpos($v, substr($allZip[0], 0, 16)) !== false) {
                    $e = unlink("D:/AD/" . $v);
                    echo $e;
                }
            }
        }
    }

}



