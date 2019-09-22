<?php

namespace system\modules\main\models;

use system\core\utils\Tool;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_article_category".
 *
 * @property integer $id                应用分类id
 * @property string $name               分类名称
 * @property integer $image             分类图片
 * @property integer $pid               父分类
 * @property string $path               结构
 * @property string $code               代码
 * @property integer $is_display        是否显示
 * @property integer $sort              排序
 */
class AppCategory extends \system\models\Model
{
    public $log_flag = true;
    public $log_options = [
        'target_name' => 'name',//日志目标对应的字段名，默认name
        'model_name' => '应用分类',//模型名称
        'except_field' => ['path'],
    ];

    public static $cacheData = true;
    public static $cacheDataOption = [
        'orderBy' => ['pid' => SORT_ASC, 'sort' => SORT_DESC, 'id' => SORT_ASC],
        //'where' => ['is_display' => 1],
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_app_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['pid', 'is_display', 'sort'], 'integer'],
            [['name', 'path', 'code', 'image'], 'string', 'max' => 255]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'ID',
            'name' => '分类名称',
            'image' => '分类图标',
            'pid' => '上级分类',
            'path' => '结构路径',
            'code' => '代码',
            'is_display' => '是否显示',
            'sort' => '排序',
        ], parent::attributeLabels());
    }
	
	/**
	 * 选择性属性列表
	 * @param string $field 字段名
	 * @param string $key 查找的key
	 * @param string|bool $default 默认值(未查到结果的情况下返回)
	 * @return array|bool|string
	 */
	public static function getAttributesList($field = '', $key = '', $default = false)
	{
		$list = [
			'is_display' => ['1' => '是', '0' => '否'],
			'pid' => self::getNameArr(),
		];
		
		return self::getAttributeValue($list, $field, $key, $default);
	}
	
	/**
	 * @inheritDoc
	 */
	public function beforeSave($insert)
	{
		if (parent::beforeSave($insert)) {
			if ($this->pid == 0) {
				$this->path = '0-';
				return true;
			}
			
			// 非根节点
			if ($this->pid != 0) {
				$parentModel = self::findOne($this->pid);
				//父节点不存在
				if (!$parentModel) {
					return false;
				}
				$this->path = $parentModel->path . $parentModel->id . '-';
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function beforeDelete()
	{
		if (parent::beforeDelete()) {
			//判断是否存在子节点
			if (self::find()->where(['pid' => $this->id])->count()) {
				return false;
			}
			
			return true;
		}
		
		return false;
	}
	
	private static $_allData = [];
	
	/**
	 * 获取所有分类数据
	 * @param bool $refresh  是否强制刷新数据
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getAllData($refresh = false)
	{
		if (empty(self::$_allData) || $refresh) {
            self::$_allData = self::getAllDataCache($refresh);
		}

		return self::$_allData;
	}

    /**
     * 根据条件获取数据
     * @param null $condition
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDataByCondition($condition = null)
    {
        $data = self::getAllData();
        if (empty($condition)) {
            return $data;
        } else {
            //如果condition是int，匹配ID；condition是string，匹配name；condition是数组，匹配字段
            $condition = is_numeric($condition) ? ['id' => $condition] : (is_string($condition) ? ['name' => $condition] : $condition);
            return Tool::get_array_by_condition($data, $condition);
        }
	}
	
	/**
	 * 获取分类列表
	 * @param array|int $ids
	 * @return array
	 */
	public static function getNameArr($ids = null)
	{
		$list = self::getDataByCondition(empty($ids) ? null : ['id' => $ids]);
        $arr = ArrayHelper::map($list, 'id', 'name');

		return $ids == null ? ArrayHelper::merge([0 => '-'], $arr) : $arr;
	}
	
	/**
	 * 根据ID或ID数组获取所有下级分类ID
	 * @param array|int $ids ID或ID数组
	 * @param bool $includeSelf 是否包括自身
	 * @return array
	 */
	public static function getChildIds($ids, $includeSelf = true)
	{
		$list = self::getDataByCondition(['pid' => $ids]);
		$list = ArrayHelper::getColumn($list, 'id', []);
		
		if ($includeSelf) {
			if (is_array($ids)) {
				$list = ArrayHelper::merge($ids, $list);
			} else {
				$list[] = $ids;
			}
		}
		
		return $list;
	}

    /**
     * 获取当前分类的启用状态的应用
     * @return \yii\db\ActiveQuery
     */
	public function getApps()
    {
        return $this->hasMany(AppRelation::className(), ['cate_id' => 'id'])->with('apps');
    }

    /**
     * 根据code获取数据
     * @param $code string 代码
     * @return array
     */
    public static function getDataByCode($code)
    {
        $data = self::getAllData();
        return Tool::get_array_by_condition($data, ['code' => $code, 'is_display' => 1], false);
    }
}
