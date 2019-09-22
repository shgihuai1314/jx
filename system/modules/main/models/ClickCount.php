<?php

namespace system\modules\main\models;

use Yii;

/**
 * This is the model class for table "tab_click_count".
 *
 * @property integer $count_id        id
 * @property string $target_type      目标类型
 * @property integer $target_id       唯一标识
 * @property integer $count_type      统计类型  1周 2月 3年 4自定义
 * @property integer $start_at        统计开始时间
 * @property integer $end_at          统计截止时间
 * @property integer $click_count     点击量
 */
class ClickCount extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_click_count';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target_id', 'count_type', 'start_at', 'end_at'], 'integer'],
            [['target_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'count_id' => 'Count ID',
            'target_type' => '目标节点',
            'target_id' => '唯一标识',
            'count_type' => '统计类型',
            'start_at' => '统计开始时间',
            'end_at' => '统计截止时间',
            'click_count' => '统计点击量'
        ];
    }

    /**
     * 统计点击量
     * @param $log           array   待统计的数组
     * @param $count_type    int     统计类型，1周 2月 3年 4自定义
     * @param $start_at      int     统计开始时间
     * @param $end_at        int     统计截止时间
     */
    public function addCount($log, $count_type, $start_at, $end_at)
    {
        $arr = $count = [];
        $num = 0;

        //以目标类型为键重组数组
        foreach($log as $v){
            $arr[$v['target_type']][] = $v;
        }

        foreach($arr as $key => $item){
            //以每个目标类型为数组，区分唯一标识
            foreach($item as $v){
                $count[$v['target_id']][] = $v;
            }
            //记录入库
            foreach($count as $x=>$y){
                foreach($y as $val){
                    $num += $val['number'];
                }

                $model = new ClickCount();
                $model->target_type = $key;
                $model->target_id = $x;
                $model->count_type = $count_type;
                $model->start_at = $start_at;
                $model->end_at = $end_at;
                $model->click_count = $num;
                $model->save();
                $num = 0;
            }

            $count = [];
        }
    }


    /**
     * 自定义
     * @param $start_at      int     统计开始时间
     * @param $end_at        int     统计截止时间
     */
    public function saveCount($start_at, $end_at)
    {
        $log = ClickLog::find()
            ->where([
                'and' ,
                ['>=' , 'data_time' , $start_at] ,
                ['<' , 'data_time' , $end_at],
            ])
            ->asArray()
            ->all();

        $click_count = new ClickCount();
        $click_count->addCount($log, 4, $start_at, $end_at);
    }
}
