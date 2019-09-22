<?php

namespace system\modules\wallet\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_order_logs".
 *
 * @property integer $id
 * @property integer $order_id
 * @property string $content
 * @property integer $create_at
 * @property integer $create_by
 * @property string $create_ip
 */
class OrderLogs extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_order_logs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['order_id', 'create_at', 'create_by'], 'integer'],
            [['content', 'create_ip'], 'string'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '主键',
            'order_id' => '订单id',
            'content' => '日志内容',
            'create_at' => '创建时间',
            'create_by' => '创建人',
            'create_ip' => '创建ip',
        ], parent::attributeLabels());
    }


    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                //是不是控制台程序
                if (!Yii::$app instanceof \yii\console\Application) {
                    $this->create_ip = Yii::$app->request->userIP;
                    $this->create_by = Yii::$app->user->id;
                }

                $this->create_at = time();

            }

            return true;
        }

        return false;
    }


    /**
     * 添加数据
     * @param $data
     * @return array
     */
    public static function SaveRecord($data)
    {
        if (!isset($data['order_id'], $data['content'])) {
            return ['code' => 1, ',message' => '数据缺失'];
        }
        $model = new self;
        $model->loadDefaultValues();
        //填充数据
        if ($model->load($data, '') && $model->save()) {
            return ['code' => 0, 'message' => '数据添加成功'];
        }

        return ['code' => 1, 'message' => '数据添加失败'];

    }
}
