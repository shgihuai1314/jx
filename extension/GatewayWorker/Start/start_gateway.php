<?php

use \Workerman\Worker;
use \GatewayWorker\Gateway;

// 自动加载类
require_once __DIR__ . '/../autoload.php';

// gateway 进程
$gateway = new Gateway("Websocket://0.0.0.0:8905");
// gateway名称，status方便查看
$gateway->name = 'Gateway';
// gateway进程数
$gateway->count = 4;
// 本机ip，分布式部署时使用内网ip
$gateway->lanIp = '127.0.0.1';
// 内部通讯起始端口。假如$gateway->count=4，起始端口为2300
// 则一般会使用2300 2301 2302 2303 4个端口作为内部通讯端口
$gateway->startPort = 2300;
// 服务注册地址
$gateway->registerAddress = '127.0.0.1:8904';

// 心跳间隔
$gateway->pingInterval = 10;
// 心跳数据
$gateway->pingData = '{"type":"ping"}';

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

