<?php

namespace system\modules\user\models;

use yii\helpers\ArrayHelper;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "tab_attention".
 *
 * @property integer $id
 * @property integer $follow_id
 * @property integer $user_id
 * @property integer $create_at
 */
class Attention extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_attention';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['follow_id', 'user_id', 'create_at'], 'integer'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'ID',
            'follow_id' => '被关注人',
            'user_id' => '关注人',
            'create_at' => '关注时间',
        ], parent::attributeLabels());
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                if($this->follow_id == Yii::$app->user->id){
                    return false;
                }else{
                    $this->user_id = Yii::$app->user->id;
                    $this->create_at = time();
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub

        if($insert){
            Yii::$app->systemMessage->send('attention_notify', $this->follow_id, [
                'user' => User::getInfo($this->user_id),
                'code' => 'user',
                'params' => Json::encode(['id' => $this->user_id])
            ]);
        }
    }

    /**
     * 关联用户表
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * 获取关注人数
     * @return int|string
     */
    public static function getFollowerNum($user_id)
    {
        return intval(self::find()->where(['user_id' => $user_id])->asArray()->count());
    }

    /**
     * 获取粉丝人数
     * @return int|string
     */
    public static function getFansNum($user_id)
    {
        return intval(self::find()->where(['follow_id' => $user_id])->asArray()->count());
    }

    /**
     * 判断是否关注
     * @param $user_id
     * @return bool
     */
    public static function isAttention($user_id)
    {
        return Attention::findOne(['user_id' => Yii::$app->user->getId(),'follow_id' => $user_id]) ? true : false;
    }

    /**
     * 判断当前用户是否为此用户粉丝
     * @param $user_id
     * @return bool
     */
    public static function isFans($user_id)
    {
        $fans_ids = Attention::find()->select('user_id')->where(['follow_id' => $user_id])->asArray()->column();

        if(in_array(Yii::$app->user->getId(),$fans_ids)){
            return true;
        }else {
            return false;
        }
    }
}