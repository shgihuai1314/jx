<?php

namespace system\modules\main\models;

use Yii;

/**
 * This is the model class for table "tab_click_total".
 *
 * @property integer $total_id
 * @property string $target_type
 * @property integer $target_id
 * @property integer $click_total
 */
class ClickTotal extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_click_total';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target_id', 'click_total'], 'integer'],
            [['click_total'], 'required'],
            [['target_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'total_id' => 'Total ID',
            'target_type' => 'Target Type',
            'target_id' => 'Target ID',
            'click_total' => 'Click Total',
        ];
    }

    /**
     * 获取点击总数
     * @param $target_type string  目标类型
     * @param $target_id  int 目标id
     * @return int
     */
    public static function getTotalNum($target_type, $target_id)
    {
        /** @var $this $model */
        $model = ClickTotal::find()
            ->where([
                'target_type' => $target_type,
                'target_id' => $target_id,
            ])
            ->one();
        return $model ? $model->click_total : 0;
    }

    /**
     * @param $target_type    string  目标类型
     * @param $target_id      int     唯一标识
     * @return int
     */
    public static function addTotal($target_type, $target_id)
    {
        /** @var $this $model */
        $model = ClickTotal::find()
            ->where([
                'target_type' => $target_type,
                'target_id' => $target_id,
            ])
            ->one();
        if($model){
            $model->click_total = $model->click_total+1;
        }else{
            $model = new ClickTotal();
            $model->target_type = $target_type;
            $model->target_id = $target_id;
            $model->click_total = 1;
        }
        $model->save();

        return $model->click_total;
    }

}
