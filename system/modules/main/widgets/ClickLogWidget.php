<?php
namespace system\modules\main\widgets;

use system\modules\main\models\ClickLog;
use system\modules\main\models\ClickTotal;
use yii\bootstrap\Widget;

class ClickLogWidget extends Widget
{
    public $target_type;  //目标类型
    public $target_id;    //目标ID
    public $showTotal = false;    //显示总数
    public $mode = 'normal';    // 展示方式,normal一般模式，view展示模式

    public function run()
    {
        $target_type = $this->target_type;
        $target_id = $this->target_id;

        $count = 0;

        if ($this->mode == 'normal') {
            ClickLog::addOne($target_type, $target_id);
            $count = ClickTotal::addTotal($target_type, $target_id);
        } else if ($this->mode == 'view') {
            $count = ClickTotal::getTotalNum($target_type, $target_id);
        }


        if ($this->showTotal) {
            echo $count;
        }
    }
}