<?php

namespace system\modules\user\models;

use system\core\utils\Tool;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_content_permission".
 *
 * @property string $id
 * @property string $user_id
 * @property string $create_time
 * @property string $update_time
 * @property string $create_by
 * @property string $update_by
 */
class ContentPermission extends \system\models\Model
{
    public $log_flag = true;
    public $log_options = [
        'target_name' => 'user_id',//日志目标对应的字段名，默认name
        'model_name' => '内容权限',//模型名称
        'except_field' => ['create_time', 'update_time', 'create_by', 'update_by'],
    ];

    public $convertList = [
        'user_id' => 'user',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_content_permission';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
	        [['create_time', 'update_time', 'create_by', 'update_by'], 'integer'],
	        [['user_id'],'safe'],
	        ['user_id', 'unique']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'ID',
            'user_id' => '用户ID',
            'create_time' => '创建时间',
            'update_time' => '修改时间',
            'create_by' => '创建人',
            'update_by' => '修改人',
        ], parent::attributeLabels());
    }
	
	public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            //转成纯数字
	        $this->user_id = Tool::toIntger($this->user_id);

            if ($insert) {
                $this->create_time = time();
                $this->create_by = Yii::$app->user->id;
            }

            $this->update_time = time();
            $this->update_by = Yii::$app->user->id;

        }
        return true;
    }

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(),['user_id'=>'user_id']);
    }

    public function getUpdateBy()
    {
        return $this->hasOne(User::className(),['user_id'=>'update_by']);
    }

    /**
     * 获取内容权限的相关数据
     * @param $user_id int 用户id
     * @param $fields string|array 字段字符串或者字段数组
     * @return array|bool
     */
    public static function getPermissionByUser($user_id, $fields)
    {
        $model = self::findOne(['user_id' => $user_id]);
        if (!$model) {
            return false;
        }

        $data = [];
        if (is_string($fields)) {
            $data[$fields] = $model->getAttribute($fields);
        } else if (is_array($fields)) {
            foreach ($fields as $field) {
                $data[$field] = $model->getAttribute($field);
            }
        }

        return $data;
    }
}
