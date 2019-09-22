<?php

namespace system\modules\user\models;

use system\modules\course\models\Course;
use system\modules\notes\models\Notes;
use system\modules\topic\models\Topic;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_collection".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $target_type
 * @property integer $target_id
 * @property integer $create_at
 */
class Collection extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_collection';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['user_id', 'target_id', 'create_at'], 'integer'],
            [['target_type'], 'string'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'ID',
            'user_id' => '收藏人ID',
            'target_type' => '收藏目标的类型',
            'target_id' => '收藏目标的ID',
            'create_at' => '创建时间',
        ], parent::attributeLabels());
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->user_id = Yii::$app->user->id;
                $this->create_at = time();
            }

            return true;
        }

        return false;
    }

    /**
     * 关联笔记表
     * @return \yii\db\ActiveQuery
     */
    public function getNotes()
    {
        return $this->hasOne(Notes::className(), ['id' => 'target_id'])
            ->Where([Collection::tableName() . '.target_type' => 'notes']);
    }

    /**
     * 关联课程表
     * @return \yii\db\ActiveQuery
     */
    public function getCourse()
    {
        return $this->hasOne(Course::className(), ['id' => 'target_id'])
            ->Where([Collection::tableName() . '.target_type' => 'course']);
    }

    /**
     * 关联话题表
     * @return \yii\db\ActiveQuery
     */
    public function getTopic()
    {
        return $this->hasOne(Topic::className(), ['id' => 'target_id'])
            ->Where([Collection::tableName() . '.target_type' => 'topic']);
    }

    /**
     * 判断当前用户是否已收藏
     * @param $target_type
     * @param $target_id
     * @return bool
     */
    public static function is_collection($target_type,$target_id)
    {
        $model =  self::findOne(['user_id' => Yii::$app->user->getId(),'target_type' => $target_type, 'target_id' => $target_id]);

        if($model){
            return true;
        }

        return false;
    }
}
