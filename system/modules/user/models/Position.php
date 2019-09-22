<?php

namespace system\modules\user\models;

use system\core\utils\Tool;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "{{%tab_user_position}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $sort
 * @property integer $number
 */
class Position extends \system\models\Model
{
	public $log_flag = true;
	public $log_options = [
		'target_name' => 'name',//日志目标对应的字段名，默认name
		'model_name' => '职位',//模型名称
	];
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tab_user_position}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['sort', 'number'], 'integer'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 20],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '职位id',
            'name' => '职位名称',
            'sort' => '排序序号',
            'number' => '在职人数',
        ], parent::attributeLabels());
    }

    /**
     * 获取所有数据
     * @param bool $refresh 是否刷新，从数据库中获取最新的数据
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public static function getAllData($refresh = false)
    {
        $cacheKey = 'position:position:all';
        $data = Yii::$app->cache->get($cacheKey);
        if (!$data || $refresh) {
            $data = self::find()->asArray()
                ->indexBy('id')
                ->orderBy(['sort' => SORT_DESC, 'id' => SORT_ASC])
                ->asArray()
                ->all();
            Yii::$app->cache->set($cacheKey, $data);
            @unlink(Yii::getAlias('@webroot').'/data/position.json');
        }

        return $data;
    }

    /**
     * 获取职位id和职位名称的map
     * @return array
     */
    public static function getAllMap()
    {
        $data = self::getAllData();
        return ArrayHelper::map($data, 'id', 'name');
    }
    
    /**
     * 根据id获取一个职位数据
     * @param $id
     * @return bool|mixed
     */
    public static function getOneById($id)
    {
        $data = self::getAllData();
        if (isset($data[$id])) {
            return $data[$id];
        }
        
        return false;
    }

    /**
     * 根据id获取name
     * @param $id
     * @return string
     */
    public static function getNameById($id)
    {
        $data = self::getOneById($id);
        if (!$data) {
            return '';
        }

        return $data['name'];
    }

    /**
     * 获取职位人数
     * @return array
     */
    public static function getPosition()
    {
        $Position = Yii::$app->db->createCommand('select position_id, count(*) as total from tab_user group by position_id')
            ->queryAll();
        $Position_count = ArrayHelper::map($Position, 'id', 'total');
        return $Position_count;
    }

    /**
     * 更改用户职位; 弃用
     * @param int $new_id 新的职位
     * @param int $old_id 旧职位
     */
    public static function changePosition($new_id, $old_id = 0)
    {
        $new = self::findOne($new_id);
        if ($new) {
            $new->number++;
            $new->save();
        }

        if ($old_id > 0) {
            $old = self::findOne($old_id);
            if ($old && $old->number > 0) {
                $old->number--;
                $old->save();
            }
        }
    }
    
    /**
     * 返回批量处理操作的职位ID（如果不存在自动添加）
     * @param string $position 职位名称
     * @return int 职位ID
     */
    public static function getPositionByBatch($position)
    {
        $model = self::findOne(['name' => trim($position)]);
    
        if (empty($model)) {
            $model = new self();
            $model->name = trim($position);
            $model->save();
        }
    
        return $model->id;
    }
    
    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        self::getAllData(true);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();
        self::getAllData(true);
    }

    public static $_position = [];

    public static function getAllPosition($condition = []){
        if(empty(self::$_position)){
            self::$_position = self::find()->asArray()->all();
        }
        return Tool::get_array_by_condition(self::$_position, $condition, $keep_key = false);
    }
}
