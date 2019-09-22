<?php

namespace system\modules\main\models;

use system\modules\media\models\MediaVideo;
use Yii;

/**
 * This is the model class for table "tab_click_log".
 *
 * @property integer $id
 * @property string $target_type    目标类型
 * @property integer $target_id     唯一标识
 * @property integer $data_time     记录时间
 * @property integer $number        点击数
 */
class ClickLog extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_click_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target_id', 'data_time', 'number'], 'integer'],
            [['target_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'target_type' => '目标类型',
            'target_id' => '唯一标识',
            'data_time' => '记录时间',
            'number' => '点击数',
        ];
    }

    /**
     * 添加一条记录
     * @param $target_type
     * @param $target_id
     * @return int
     */
    public static function addOne($target_type, $target_id)
    {
        /** @var $this $log */
        $log = ClickLog::find()
            ->where([
                'target_type' => $target_type,
                'target_id' => $target_id,
                'data_time' => strtotime(date('Y-m-d')),
            ])
            ->one();

        if($log){
            $log->number = $log->number+1;
        }else{
            $log = new ClickLog();
            $log->target_type = $target_type;
            $log->target_id = $target_id;
            $log->data_time = strtotime(date('Y-m-d'));
            $log->number = 1;
        }

        $log->save();

        return $log->number;
    }

    /**
     * 根据目标类型返回所有点击数
     * @param $target_type
     * @return string
     */
    public static function getAllLogByTarget($target_type)
    {
        $data = ClickLog::find()->where(['target_type' => 'mediaVideo'])->all();
        return $data;
    }

    //关联流媒体视频表
    public function getMediaVideo()
    {
        return $this->hasOne(MediaVideo::className(), ['id' => 'target_id']);
    }
}
