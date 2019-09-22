<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/28
 * Time: 下午4:46
 */

namespace console\controllers;

use yii;
use yii\console\Controller;

class TestController extends Controller
{
    // 日志文件
    private $_logFile = './task.error.log';

    // 处理所有数据
    public function actionIndex()
    {
        $data = file_get_contents('https://ipsw.me/api/ios/v3/device/iPhone7,1');
        $data = json_decode($data, true);

        if (!isset($data['iPhone7,1']['firmwares'])) {
            // 发送邮件，验证失败了，退出
            echo "Failed\r\n";
            Yii::$app->systemMessage->sendEmail([
                'send_to' => 'ueek@qq.com',  // 发送给谁
                'subject' => 'iphone验证通道错误', // 邮件主题
                'content' => 'iphone验证通道错误',  // 邮件内容
            ]);
            exit;
        }

        $data = $data['iPhone7,1']['firmwares'];

        $versions = [];
        foreach ($data as $key => $value) {
            if ($value['signed'] == 1) {
                if ($value['version'] < 11) {
                    // 发送邮件，验证通道打开了
                    $versions[] = $value['version'];
                }
            }
        }

        if ($versions) {
            // 发送邮件，验证打开了
            echo "ok\r\n";
            $res = Yii::$app->systemMessage->sendEmail([
                'send_to' => 'ueek@qq.com',  // 发送给谁
                'subject' => 'iphone验证通道开启了', // 邮件主题
                'content' => 'iphone验证通道开启，可用版本：'.implode(',', $versions),  // 邮件内容
            ]);
            //var_dump($res);
            exit;
        }

        echo 'done';
    }
}