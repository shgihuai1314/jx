<?php

namespace system\modules\cron\models;

use system\core\utils\Tool;
use system\modules\main\models\Modules;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_cron".
 *
 * @property integer $id
 * @property integer $task_id
 * @property integer $start_time
 * @property integer $interval_time
 * @property integer $status
 * @property integer $create_by
 * @property integer $create_time
 * @property integer $update_time
 */
class Cron extends \system\models\Model
{
    //是否记录日志
    public $log_flag = true;
    //日志信息配置
    public $log_options = [
        'target_name' => 'id',//日志目标对应的字段名，默认name
        'model_name' => '定时器',//模型名称
        'except_field' => ['id', 'update_time'],
    ];
    public $convertList = [
        'start_time' => 'datetime',
        'create_time' => 'datetime',
        'create_by' => 'user'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_cron';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['task_id', 'start_time', 'interval_time'], 'required'],
            [['task_id', 'interval_time', 'status'], 'integer'],
            [['start_time'], 'safe']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'Id',
            'task_id' => '任务',
            'start_time' => '开始时间',
            'interval_time' => '间隔时间',
            'status' => '状态',
            'create_by' => '创建人',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
        ], parent::attributeLabels());
    }

    /**
     * 选择性属性列表
     * @param string $field 字段名
     * @param string $key 查找的key
     * @param string $default 默认值(未查到结果的情况下返回)
     * @return array|bool|string
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
        $list = [
            'task_id' => CronTasks::getTaskMap(),
            'status' => [
                '1' => '开启',
                '0' => '关闭',
            ],
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->create_by = Yii::$app->user->id;
                $this->create_time = time();
            }

            $this->start_time = is_numeric($this->start_time) ? $this->start_time : strtotime($this->start_time);
            $this->update_time = time();
            return true;
        }

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(CronTasks::className(), ['id' => 'task_id']);
    }

    public static $cacheData = true;
    public static $cacheDataOption = [
        'with' => 'task',
        'where' => ['status' => 1],
        'orderBy' => ['update_time' => SORT_DESC]
    ];

    /**
     * @return array
     */
    public static function getCronList()
    {
        $data = self::getAllDataCache(true);
        foreach ($data as $key => $val) {
            // 如果开始时间在当前时间之前，根据时间间隔计算下一次开始时间
            if ($val['start_time'] < time() && $val['interval_time'] > 0) {
                $n = ceil((time() - $val['start_time']) / $val['interval_time']);
                $data[$key]['start_time'] = $val['start_time'] + $val['interval_time'] * $n;
            }
        }

        return ArrayHelper::index($data, 'update_time');
    }
}
