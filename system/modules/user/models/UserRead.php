<?php

namespace system\modules\user\models;

use system\modules\user\components\UserWithGroup;
use Yii;

/**
 * This is the model class for table "tab_user_read".
 *
 * @property integer $id
 * @property string $target_type
 * @property integer $target_id
 * @property integer $user_id
 * @property integer $read_at
 * @property integer $is_read
 */
class UserRead extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_user_read';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target_id', 'user_id', 'read_at', 'is_read'], 'integer'],
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
            'target_type' => 'Target Type',
            'target_id' => 'Target ID',
            'user_id' => 'User ID',
            'read_at' => 'Read At',
            'is_read' => 'Is Read',
        ];
    }

    /**
     * 点击增加访问记录
     * @param $target_type  string  目标类型
     * @param $target_id    int     目标ID
     * @return bool
     */
    public function addRead($target_type, $target_id)
    {
        $find = \system\modules\user\models\UserRead::find()
            ->where([
                'target_type'=>$target_type,
                'target_id' => $target_id,
                'user_id' => \Yii::$app->user->getId(),
            ])
            ->one();
        if($find){
            if($find['is_read'] != 1){
                $find->read_at = time();
                $find->is_read = 1;
                $find->save();
            }
            return true;
        }
        $model = new UserRead();
        $model->target_type = $target_type;
        $model->target_id = $target_id;
        $model->user_id = \Yii::$app->user->getId();
        $model->read_at = time();
        $model->is_read = 1;
        $model->save();
        return true;
    }

    // 获取已读未读数据
    public static function getReadData($target_type, $target_id)
    {
        $data = self::find()
            ->where(['target_type' => $target_type, 'target_id' => $target_id])
            ->asArray()
            ->all();
        $yes = [];//已查看
        $no = [];//未查看
        foreach ($data as $val) {
            if ($val['is_read'] == 1) {
                $yes[] = $val;
            } else {
                $no[] = $val;
            }
        }

        return [
            'yes' => $yes,
            'no' => $no,
        ];
    }

    /**
     * 插入未读信息
     * @param $target_type
     * @param $target_id
     * @param $str
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function addAllUserRead($target_type, $target_id, $str)
    {
        $user = UserWithGroup::getIdsBySelect($str);
        foreach($user as $k=>$v){
            $arr[$k]['target_type'] = $target_type;
            $arr[$k]['target_id'] = $target_id;
            $arr[$k]['user_id'] = $v;
        }
        if (isset($arr)) {
            UserRead::deleteAll(['target_type'=>$target_type,'target_id'=>$target_id]);
            $re = Yii::$app->db->createCommand()
                ->batchInsert(UserRead::tableName(),['target_type','target_id','user_id'], $arr)
                ->execute();
            if($re){
                return true;
            }
        }
        return false;
    }
}
