<?php

namespace system\modules\cron\migrations;

use console\models\Migration;

class M180814031750Add_system_monitoring extends Migration
{
    // 所属模块
    public $module_id = 'cron';
    
    // 更新说明
    public $description = '添加系统监控';
    
    // 版本号
    public $version = '1.2';
    
    public $config = [
        'cron' => [
            [
                'name' => '获取实时流量',
                'command' => 'click/flow',
                'desc' => '获取实时流量'
            ],
            [
                'name' => '获取cpu使用率',
                'command' => 'click/cpu-record',
                'desc' => '获取cpu使用率'
            ],
            [
                'name' => '获取系统负载率',
                'command' => 'click/load-record',
                'desc' => '获取系统负载率'
            ],
            
            [
                'name' => '获取内存使用率',
                'command' => 'click/memory-record',
                'desc' => '获取内存使用率'
            ],
            [
                'name' => '服务器性能变化',
                'command' => 'click/record',
                'desc' => '服务器性能变化'
            ],
        ],
    ];
    
    public function up()
    {
        parent::up();
    }
    
    public function down()
    {
        parent::down();
    }
}
