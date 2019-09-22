<?php
/**
 * 扩展字段模型类
 */

namespace system\modules\main\models;

use system\core\utils\Tool;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii;

/**
 * This is the model class for table "extends_field".
 * @property integer $id
 * @property string $table_name                 表名称
 * @property string $field_name                 字段名称
 * @property string $field_title                字段描述
 * @property string $field_explain              字段说明
 * @property integer $field_type                字段类型
 * @property string $show_type                  字段展示类型
 * @property string $field_value                字段选项
 * @property integer $is_null                   是否null
 * @property string $default_value              字段默认值
 * @property integer $is_must                   是否必填
 * @property integer $is_show                   是否显示
 * @property integer $is_search                 是否加入搜索项
 * @property integer $sort                      排序
 * @property string $template                   自定义模板
 * @property string $template_parameter         展示类型模板的参数
 */
class ExtendsField extends \system\models\Model
{
    public $log_flag = true;
	public $log_options = [
		'target_name' => 'field_name',//日志目标对应的字段名，默认name
		'model_name' => '扩展字段',//模型名称
	];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_extends_field';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'ID',
            'table_name' => '数据表',
            'field_name' => '字段名',
            'field_title' => '字段描述',
            'field_type' => '字段类型',
            'show_type' => '展示类型',
            'field_value' => '字段选项',
            'is_null' => '是否null',
            'default_value' => '字段默认值',
            'is_must' => '是否必填项',
            'sort' => '字段排序',
            'is_show' => '是否显示',
            'is_search' => '是否加入搜索项',
            'template' => '自定义模板',
            'template_parameter' => '模板参数',
            'field_explain' => '字段提示',
        ], parent::attributeLabels());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['table_name', 'field_title', 'field_name'], 'required'],
            ['field_name', 'match', 'pattern' => '/^[a-zA-Z][a-zA-Z0-9_]{0,49}$/'],
            [['sort', 'is_show', 'is_must', 'is_null', 'is_search'], 'integer'],
            [['show_type', 'field_type', 'default_value', 'field_value', 'template', 'template_parameter', 'field_explain'], 'string'],
            //['table_name', 'in', 'range' => array_keys($this->getAttributesList('table_name'))],
            //['show_type', 'in', 'range' => array_keys($this->getAttributesList('show_type'))],
            //['field_type', 'in', 'range' => array_keys($this->getAttributesList('field_type'))],
        ]);
    }

    /**
     * 选择字段属性列表
     * @return array
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
	    $list = [
            'table_name' => Yii::$app->systemConfig->getValue('EXTEND_FIELD_TABLE_NAME', []),
            'is_show' => [
                '1' => '是',
                '0' => '否',
            ],
            //是否必须
            'is_must' => [
                '1' => '是',
                '0' => '否',
            ],
            'is_null' => [
                '1' => '是',
                '0' => '否',
            ],
            'is_search' => [
                '1' => '是',
                '0' => '否',
            ],
            //字段类型
            'field_type' => Yii::$app->systemConfig->getValue('EXTEND_FIELD_TYPE_LIST', []),
            //字段展示方式，只对列举类型起作用
            'show_type' => Yii::$app->systemConfig->getValue('EXTEND_FIELD_SHOW_TYPE_LIST', []),
        ];
	
	    return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * 扩展字段强制加上字段前缀
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->getFieldName();

            return true;
        }

        return false;
    }

    /**
     * 根据字段设置获取rule规则
     * @param $tableName
     * @param $rules
     * @return array
     */
    public static function getRules($tableName, $rules)
    {
	    $fieldData = self::getDataByTable($tableName);
	    
        foreach ($fieldData as $value) {
            $field = $value['field_name'];
            //必填,显示且是必填
            if ($value['is_must'] == 1 && $value['is_show'] == 1) {
                $rules[] = [$field, 'required', 'message' => $value['field_title'] . '不能为空'];
            } else {
                $rules[] = [$field, 'safe'];
            }
        }

        return $rules;
    }

    /**
     * 按照排序获取数据库中所有记录
     * @param bool $refresh 是否刷新数据
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public static function getAllData($refresh = false)
    {
        $cache_key = 'main:extend_field:all';
        $data = Yii::$app->cache->get($cache_key);
        if (!$data || $refresh) {
            $data = self::find()
                ->orderBy(['sort' => SORT_DESC, 'id' => SORT_ASC])
                ->asArray()
                ->all();
            Yii::$app->cache->set($cache_key, $data);
        }

        return $data;
    }

    /**
     * 获取table_name的所有字段
     * @param $tableName string 表名称
     * @return array
     */
    public static function getDataByTable($tableName)
    {
        $data = self::getAllData();
        $tableData = Tool::array_to_multiple_by_index($data, 'table_name');
        return isset($tableData[$tableName]) ? $tableData[$tableName] : [];
    }

    /**
     * 根据表名称获取要显示的数据
     * @param $tableName string 表名称
     * @return array
     */
    public static function getShowDataByTable($tableName)
    {
        $tableData = self::getDataByTable($tableName);
        return Tool::get_array_by_condition($tableData, ['is_show' => 1]);
    }

    /**
     * 根据表名获取允许搜索的扩展字段
     * @param $tableName
     * @return array
     */
    public static function getSearchFieldByTable($tableName)
    {
        $tableData = self::getDataByTable($tableName);
        return Tool::get_array_by_condition($tableData, ['is_search' => 1]);
    }

    /**
     * 获取扩展字段数组['字段名'=>'字段描述', ...]
     * @param string $tableName 表名
     * @return array
     */
    public static function getExtendField($tableName)
    {
        $allData = self::getDataByTable($tableName);
        if (empty($allData)) {
            return [];
        }
        return ArrayHelper::map($allData, 'field_name', 'field_title');
    }
	
	/**
	 * 获取表格的选择性字段属性列表
	 * @param string $tableName
	 * @param array $list
	 * @return array
	 */
	public static function getTableAttributes($tableName, &$list)
	{
		$all = self::getDataByTable($tableName);
		
		foreach ($all as $val) {
			if (in_array($val['show_type'], ['select', 'radio', 'checkbox'])) {
				$list[$val['field_name']] = self::valueToArray($val['field_value']);
			}
		}
		
		return $list;
	}
	
	/**
     * 获取列表类的数据
     * @param string $tableName
     * @return array
     */
    public static function getList($tableName)
    {
        $list = [];
        $allData = self::getDataByTable($tableName);
        if ($allData) {
            foreach ($allData as $one) {
                //如果是数组形式
                if (in_array($one['show_type'], ['select', 'radio', 'checkbox'])) {
                    if (!empty($one['field_value'])) {
                        $list[$one['field_name']] = self::valueToArray($one['field_value']);
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 获取列表类的数据
     * @param string $field_value
     * @return array
     */
    public static function valueToArray($field_value)
    {
        if (preg_match('/^select [\s\S]+ from [\S]+[\s\S]*$/', strtolower($field_value))) {
            $data = Yii::$app->db->createCommand($field_value)->queryAll();
            $list = [];
            foreach ($data as $one) {
                if (count($one) == 1) {
                    $list[reset($one)] = reset($one);
                } else {
                    $key = current($one);
                    next($one);
                    $list[$key] = current($one);
                }
            }
            return $list;
        } else {
            return Tool::paramsToArray($field_value);
        }
    }

    /**
     * 获取列表字段的键对应的值
     * @param $field
     * @param $value
     * @param string $tableName
     * @return string
     */
    public static function getFieldValue($field, $value, $tableName = 'tab_user')
    {
        $list = self::getList($tableName);

        if (isset($list[$field])) {
            return isset($list[$field][$value]) ? $list[$field][$value] : '';
        } else {
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // 刷新缓存
        self::getAllData(true);

        // 保存完毕以后刷新对应数据表的schema缓存
        Yii::$app->getDb()->getSchema()->refreshTableSchema($this->table_name);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        // 刷新缓存
        self::getAllData(true);

        // 保存完毕以后刷新对应数据表的schema缓存
        Yii::$app->getDb()->getSchema()->refreshTableSchema($this->table_name);
    }

    /**
     * 根据表名称和字段名称删除字段
     * @param $table_name string 表名称
     * @param $field_name string 字段名称
     * @return array
     */
    public static function deleteFieldByField($table_name, $field_name)
    {
        // 如果不是extend_开头，那么自动加上
        if (substr($field_name, 0, 7) != 'extend_') {
            /*return [
                'code' => 1,
                'message' => '非扩展字段，不允许删除',
            ];*/
            $field_name = 'extend_'.$field_name;
        }

        $model = self::findOne(['table_name' => $table_name, 'field_name' => $field_name]);

        if (!$model) {
            return [
                'code' => 1,
                'message' => '扩展字段不存在',
            ];
        }

        return $model->deleteField();
    }

    /**
     * 删除字段
     * @return array
     */
    public function deleteField()
    {
        $tableColumn = $this->getTableColumn();
        try{
            if (preg_grep("/$this->field_name/i", $tableColumn)) {
                Yii::$app->db->createCommand()->dropColumn($this->table_name, $this->field_name)->execute();
            }
        } catch (\Exception $e) {
            return [
                'code' => 1,
                'message' => '字段删除失败 ' . $e->getMessage(),
            ];
        }

        // 表字段删除后，删除扩展字段记录
        if (!$this->delete()) {
            return [
                'code' => 1,
                'message' => '数据删除失败',
            ];
        }

        return [
            'code' => 0,
            'message' => '数据删除成功',
        ];
    }

    /**
     * 检测字段是否存在
     * @return array
     */
    private function _checkField()
    {
        $this->getFieldName();

        // 先判断指定的表是否存在
        $allTable = Yii::$app->db->createCommand("SHOW TABLES")->queryColumn();
        if (!in_array($this->table_name, $allTable)) {
            return [
                'code' => 1,
                'message' => '表不存在',
            ];
        }

        //默认值检测
        if ($this->field_type == 'boolean' || $this->field_type == 'integer' || $this->field_type == 'money') {
            if ($this->default_value != '' && !preg_match("/\d/", $this->default_value)) {
                return [
                    'code' => 1,
                    'message' => '此参数默认值只能为数字',
                ];
            } else {
                $this->default_value = '0'; // 数字如果没有设置，那么默认值设置为0
            }
        }

        return [
            'code' => 0,
            'message' => '检测通过',
        ];
    }

    /**
     * 增加字段
     * 先判断目标表是否存在，如果不存在，那么直接返回；
     * 如果字段在目标表中已经存在，
     * @return array
     */
    public function addField()
    {
        $res = $this->_checkField();
        if ($res['code'] == 1) {
            return $res;
        }

        //判断是否有字段
        $tableColumn = $this->getTableColumn();

        // 字段存在
        $fieldExistFlag = false;
        // 正则判断 开头、结尾、不区分大小写
        if (preg_grep("/^$this->field_name$/i", $tableColumn)) {
            $fieldExistFlag = true;
        }

        // 判断当前字段在表中是否存在，已经存在不做更新
        if (!self::find()->where(['table_name' => $this->table_name, 'field_name' => $this->field_name])->exists()) {
            // 如果在本地不存在，那么保存记录
            if (!$this->save()) {
                return [
                    'code' => 1,
                    'message' => '数据保存失败，请重试'.Json::encode($this->errors),
                ];
            }
        }

        // 如果字段不存在，可以操作; 字段不存在，则不做更新
        if (!$fieldExistFlag) {
            $res = $this->_addNewField();
            if ($res['code'] == 1) {
                return $res;
            }
        }

        // 刷新对应数据表的schema缓存
        Yii::$app->getDb()->getSchema()->refreshTableSchema($this->table_name);

        return [
            'code' => 0,
            'message' => '字段添加成功',
        ];
    }

    /**
     * 编辑字段
     * @return array
     */
    public function updateField()
    {
        $res = $this->_checkField();
        if ($res['code'] == 1) {
            return [
                'code' => 1,
                'message' => $res['message'],
            ];
        }

        $old = $this->getOldAttributes();
        //判断新字段是否存在
        $tableColumn = $this->getTableColumn();

        //如果字段名做了更改, 需要先判断表中是否存在了字段
        if ($this->field_name != $old['field_name']) {
            // 必须匹配开头、结尾、不区分大小写
            if (preg_grep("/^{$this->field_name}$/i", $tableColumn)) {
                return [
                    'code' => 1,
                    'message' => '字段(' . $this->field_name . '})已经在表中存在！',
                ];
            }
        }

        if (!$this->save()) {
            return [
                'code' => 1,
                'message' => '数据保存失败',
            ];
        }

        try {
            // 如果原名称在表中不存在，那么需要新建更改过的字段
            if (!preg_grep("/^{$old['field_name']}$/i", $tableColumn)) {
                $res = $this->_addNewField();
                if ($res['code'] == 1) {
                    return $res;
                }
            } else {
                //字段改名
                if ($this->field_name != $old['field_name']) {
                    Yii::$app->db->createCommand()->renameColumn($this->table_name, $old['field_name'], $this->field_name)->execute();
                }

                //字段更改描述 或者 字段更改默认值
                if ($this->field_title != $old['field_title'] || $this->default_value != $old['default_value'] || $this->field_type != $old['field_type'] || $this->is_null != $old['is_null']) {
                    //text类型默认null
                    if ($this->field_type == 'text') {
                        $filed_type = $this->field_type . ' comment "' . $this->field_title . '"';
                    } else if ($this->is_null) {
                        $filed_type = $this->field_type . ' comment "' . $this->field_title . '" default NULL';
                    } else {
                        $filed_type = $this->field_type . ' not null comment "' . $this->field_title . '" default "' . $this->default_value . '" ';
                    }

                    // 更改字段
                    Yii::$app->db->createCommand()->alterColumn($this->table_name, $this->field_name, $filed_type)->execute();
                }
            }
        } catch (\Exception $e) {
            // 还原到原来的值
            $this->setAttributes($old);
            $this->save();

            return [
                'code' => 1,
                'message' => '字段更改失败 ' . $e->getMessage(),
            ];
        }

        // 保存完毕以后刷新对应数据表的schema缓存
        Yii::$app->getDb()->getSchema()->refreshTableSchema($this->table_name);

        return [
            'code' => 0,
            'message' => '更新成功'
        ];
    }

    /**
     * 创建新字段
     * @return array
     */
    private function _addNewField()
    {
        //text类型默认null
        if ($this->field_type == 'text') {
            $filed_type = $this->field_type . ' comment "' . $this->field_title . '"';
        } else if ($this->is_null) {
            $filed_type = $this->field_type . ' comment "' . $this->field_title . '" default NULL';
        } else {
            $filed_type = $this->field_type . ' not null comment "' . $this->field_title . '" default "' . $this->default_value . '" ';
        }

        try {
            //添加字段
            Yii::$app->db->createCommand()->addColumn($this->table_name, $this->field_name, $filed_type)->execute();
        } catch (\Exception $e) {
            // 字段创建失败，删除记录
            $this->delete();
            return [
                'code' => 1,
                'message' => '字段创建失败;'.$e->getMessage(),
            ];
        }

        return [
            'code' => 0,
            'message' => '字段创建成功',
        ];
    }

    /**
     * 处理字段名称，如果没有extend_，那么加上
     */
    public function getFieldName()
    {
        // 如果不是以extend_开头，那么自动添加
        if (substr($this->field_name, 0, 7) != 'extend_') {
            $this->field_name = 'extend_'.$this->field_name;
        }
    }

    /**
     * 获取表的字段数组
     * @param $table_name string 表名称
     * @return array
     */
    public function getTableColumn($table_name = null)
    {
        if (!$table_name) {
            $table_name = $this->table_name;
        }
        $table = Yii::$app->db->getSchema()->getTableSchema($table_name);
        if ($table) {
            return $table->columnNames;
        }

        return [];
    }
}