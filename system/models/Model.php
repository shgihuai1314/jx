<?php
/**
 * Created by PhpStorm.
 * User: luobo
 * Date: 17/5/19
 * Time: 下午4:08
 */

namespace system\models;

use yii;
use yii\helpers\ArrayHelper;
use system\modules\main\models\ExtendsField;

class Model extends yii\db\ActiveRecord
{
    const SYSTEM_EVENT_AFTER_SAVE = 'afterSave'; // 模型保存后触发

    //保存和删除操作之前的数据，用于记录日志时对比找出改变的字段内容
    protected $_old = [];
    //是否记录日志
    public $log_flag = false;
    //日志信息配置
    public $log_options = [
        'target_name' => 'name',//日志目标对应的字段名，默认name
        'model_name' => '',//模型名称
        'normal_field' => [],// 要记录日志的普通字段 并非所有字段都需要记录，比如更新时间、创建人不需要记录，操作日志的作用是便于管理员排错，记录必要的信息即可。
        'except_field' => [],//日志记录时要排除的字段
    ];

    /**
     * 日志字段转换字典, 如: ['create_at' => 'date'//会自动转换成日期格式, 'user_id' => ['User', 'getInfo']//会调用User类的getInfo方法]
     * @var array
     */
    public $convertList = [];

    public static $cacheData = false; // 是否缓存数据
    // 缓存数据的选项
    public static $cacheDataOption = [
        //'indexBy' => [''],    // 以指定字段为主键
        //'with' => [''],       // 关联表
        //'select' => ['*'],    // 查询字段数组，默认查询全部，可用自定义查询的字段
        //'where' => [''],      // where 条件数组，默认为空，不做过滤
        //'orderBy' => [],      // 排序数组，默认为空，不做排序,
        //'group_by' => '',     // 可用随时补充条件
    ];

    //允许批量处理的字段信息
    public static $batch_operate_fields = [];
    //批量处理字段特殊处理
    public static $batch_fields_convert = [];

    /**
     * 批量处理
     * @param array $excel_data excel导入的数据
     * @param string $action 操作类型（create:创建;update:更新;delete:删除;）
     * @param string $constraintField 约束字段
     * @param array $fields 操作的字段
     * @param bool $isClear 是否清除原有数据
     * @param bool $isContinue 遇到错误是否继续
     * @return bool
     */
    public static function batchOperate($excel_data, $action, $constraintField, $fields = [], $isClear = false, $isContinue = true)
    {
        set_time_limit(0);
        //获取字段属性列表
        $attributesList = static::getAttributesList();
        switch ($action) {//操作类型
            case 'create'://新增
                if ($isClear) {
                    static::deleteAll();
                }

                foreach ($excel_data as $data) {
                    //获取该行约束字段的值
                    $constraintValue = trim($data[array_search($constraintField, $fields)]);
                    $exist = static::find()->where([$constraintField => $constraintValue])->exists();
                    if ($exist) {//记录已存在，直接跳过
                        continue;
                    }

                    $model = new static();
                    $model->loadDefaultValues();
                    foreach ($fields as $key => $val) {
                        $value = trim($data[$key]);//表格中每行指定字段的值
                        $type = ArrayHelper::getValue(static::$batch_fields_convert, $val);//批量处理字段转换方式
                        if (empty($type)) {//不需要转换
                            //如果在属性列表中且存在对应的属性值，返回对应值的键，如gender=[0=>'保密'，1=>'男', 2=>'女']，表格填“男”，返回1
                            if (array_key_exists($val, $attributesList) && array_search($value, $attributesList[$val]) != false) {
                                $value = array_search($value, $attributesList[$val]);
                            }
                        } elseif ($type == 'date') {//如果是日期类型，返回日期的时间戳
                            $value = strtotime($value);
                        } else {//调用函数返回结果
                            $value = call_user_func($type, $value);
                        }

                        //如果值为null或空字符则不保存
                        if (!is_null($value) && $value != '') {
                            $model->$val = $value;
                        }
                    }

                    $res = $model->save();
                    //保存失败且选择遇到错误不跳过，则返回错误信息
                    if (!$res && !$isContinue) {
                        return $model->errors;
                    }
                }
                break;
            case 'update'://更新操作
                foreach ($excel_data as $data) {
                    //获取该行约束字段的值
                    $constraintValue = trim($data[array_search($constraintField, $fields)]);
                    $model = static::findOne([$constraintField => $constraintValue]);
                    if (!$model) {//记录不存在，直接跳过
                        continue;
                    }

                    foreach ($fields as $key => $val) {
                        $value = trim($data[$key]);//表格中每行指定字段的值
                        $type = ArrayHelper::getValue(static::$batch_fields_convert, $val);//批量处理字段转换方式
                        if (empty($type)) {//不需要转换
                            //如果在属性列表中且存在对应的属性值，返回对应值的键，如gender=[0=>'保密'，1=>'男', 2=>'女']，表格填“男”，返回1
                            if (array_key_exists($val, $attributesList) && array_search($value, $attributesList[$val]) != false) {
                                $value = array_search($value, $attributesList[$val]);
                            }
                        } elseif ($type == 'date') {//如果是日期类型，返回日期的时间戳
                            $value = strtotime($value);
                        } else {//调用函数返回结果
                            $value = call_user_func($type, $value);
                        }

                        //如果值为null或空字符则不保存
                        if (!is_null($value) && $value != '') {
                            $model->$val = $value;
                        }
                    }

                    $res = $model->save();
                    //保存失败且选择遇到错误不跳过，则返回错误信息
                    if (!$res && !$isContinue) {
                        return $model->errors;
                    }
                }
                break;
            case 'delete'://删除操作
                foreach ($excel_data as $data) {
                    //获取该行约束字段的值
                    $constraintValue = trim($data[array_search($constraintField, $fields)]);
                    static::deleteAll([$constraintField => $constraintValue]);
                }
                break;
            default:
                break;
        }

        return true;
    }

    public static function getRule($rule)
    {
        return ExtendsField::getRules(static::tableName(), $rule);
    }

    public function attributeLabels()
    {
        return ExtendsField::getExtendField(static::tableName());
    }

    /**
     * @inheritdoc
     */
    public static function findOne($condition)
    {
        //从数据库中查询记录
        $model = parent::findOne($condition);

        if (!$model) {
            return false;
        }

        //将当前记录保存在临时旧数据
        $model->_old = $model->getCurrentData();

        return $model;
    }

    /**
     * 批量删除
     * @param array $condition
     * @param array $action
     * @return bool
     */
    public static function BatchDel($condition, $action = null)
    {
        $res = false;
        if (empty($condition)) {
            return $res;
        }

        // 根据条件找出所有要删除的对象，循环逐个删除
        $arr = static::findAll($condition);
        foreach ($arr as $model) {
            if ($action) {// 自定义删除操作，如$action = ['is_del' => 1]表示删除操作是把is_del置为1
                foreach ($action as $field => $value) {
                    $model->$field = $value;
                }
                $res = $model->save();
            } else {
                $res = $model->delete();
            }

            if (!$res) {
                // 删除失败直接返回false
                return false;
            }

            $res = true;
        }

        return $res;
    }

    /**
     * @inheritdoc
     * @return Query the active query used by this AR class.
     */
    public static function find()
    {
        $query = new Query(get_called_class());
        $query->tableName = static::tableName();
        return $query;
    }

    /**
     * 获取当前的日志需要记录的值
     * @return array
     */
    public function getCurrentData()
    {
        $normal_field = ArrayHelper::getValue($this->log_options, 'normal_field', []);
        $except_field = ArrayHelper::getValue($this->log_options, 'except_field', []);
        $fields = empty($normal_field) ? array_keys($this->attributes) : $normal_field;
        $list = [];
        //给普通字段赋值
        foreach ($fields as $field) {
            if (!in_array($field, $except_field)) {
                $list[$field] = $this->$field;
            }
        }
        return $list;
    }

    /**
     * 选择性属性列表
     * @param string $field 字段名
     * @param string $key 查找的key
     * @param string $default 默认值(未查到结果的情况下返回)
     * @return array|bool|string
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
        $list = [
            'is_show' => [
                0 => '否',
                1 => '是'
            ]
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * 获取字段属性列表对应值
     * @param array $list
     * @param string $field
     * @param string $key
     * @param bool $default
     * @return string|array|bool $list
     */
    public static function getAttributeValue($list = [], $field = '', $key = '', $default = false)
    {
        ExtendsField::getTableAttributes(static::tableName(), $list);

        if ($field == '') {
            return $list;
        } else if (!isset($list[$field])) {
            return $default === false ? [] : $default;
        } else if ($key === '') {
            return $default === false ? $list[$field] : $default;
        } else {
            return isset($list[$field][$key]) ? $list[$field][$key] : ($default === false ? $list[$field] : $default);
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->trigger(self::SYSTEM_EVENT_AFTER_SAVE);

        if (static::$cacheData) {
            // 刷新缓存
            self::getAllDataCache(true);
        }

        if ($this->log_flag) {
            //写日志
            $dirtyArr = Yii::$app->systemOperateLog->dirtyData($this->_old, $this->getCurrentData());//原始数据
            $dirtyContent = Yii::$app->systemOperateLog->dirtyData($this->_old, $this->getCurrentData(), $this);//解析后的数据
            if (!empty($dirtyArr)) {
                $targetName = ArrayHelper::getValue($this->log_options, 'target_name', 'name');
                if (is_array($targetName)) {
                    $target_name = call_user_func($targetName['1'], $this->$targetName['0']);
                } else {
                    $target_name = ArrayHelper::getValue($this->toArray(), ArrayHelper::getValue($this->log_options, 'target_name', 'name'));
                }

                $logData = [
                    'action_type' => $insert ? 'add' : 'edit',
                    'module' => substr(self::className(), 15, strpos(self::className(), '\\', 15) - 15),//去掉前面的\system\modules\
                    'target_name' => $target_name,
                    'target_id' => ArrayHelper::getValue($this->toArray(), ArrayHelper::getValue($this->log_options, 'primaryKey', 'id')) ?: $this->primaryKey,
                    'data' => yii\helpers\Json::encode($dirtyArr),
                    'content' => yii\helpers\Json::encode($dirtyContent),
                    'model_class' => self::className(),
                ];
                Yii::$app->systemOperateLog->write($logData);
                //print_r(Yii::$app->systemOperateLog->error());
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        if (static::$cacheData) {
            // 刷新缓存
            self::getAllDataCache(true);
        }

        if ($this->log_flag) {
            //写日志
            $dirtyArr = Yii::$app->systemOperateLog->dirtyData([], $this->_old);//原始数据
            $dirtyContent = Yii::$app->systemOperateLog->dirtyData([], $this->_old, $this);//解析后的数据
            $primaryKey = static::primaryKey();
            $primaryKey = empty($primaryKey) ? 'id' : (is_array($primaryKey) ? $primaryKey[0] : $primaryKey);
            if (!empty($dirtyArr)) {
                $logData = [
                    'action_type' => 'delete',
                    'module' => substr(self::className(), 15, strpos(self::className(), '\\', 15) - 15),//去掉前面的\system\modules\
                    'target_name' => ArrayHelper::getValue($this->toArray(), ArrayHelper::getValue($this->log_options, 'target_name', 'name'), ''),
                    'target_id' => ArrayHelper::getValue($this->toArray(), $primaryKey, 0),
                    'data' => yii\helpers\Json::encode($dirtyArr),
                    'content' => yii\helpers\Json::encode($dirtyContent),
                    'model_class' => self::className(),
                ];
                Yii::$app->systemOperateLog->write($logData);
            }
        }
    }

    private static $_DataCache = null;

    /**
     * 获取全部数据
     * @param $refresh bool 是否强制刷新数据
     * @param $cacheKey string 缓存key，为空，则使用默认key
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public static function getAllDataCache($refresh = false, $cacheKey = '')
    {
        $key = $cacheKey ?: self::className();

        // 先读取内存里面的内容；如果不强制刷新并且缓存里面有数据
        if (!$refresh && isset(self::$_DataCache[$key]) && self::$_DataCache[$key]) {
            return self::$_DataCache[$key];
        }

        $data = Yii::$app->cache->get($key);
        if (!$data || $refresh) {
            $query = self::find();
            // with
            if (isset(static::$cacheDataOption['with'])) {
                $query->with(static::$cacheDataOption['with']);
            }
            // index by
            if (isset(static::$cacheDataOption['indexBy'])) {
                $query->indexBy(static::$cacheDataOption['indexBy']);
            }
            // select
            if (isset(static::$cacheDataOption['select'])) {
                $query->select(static::$cacheDataOption['select']);
            }
            // where
            if (isset(static::$cacheDataOption['where'])) {
                $query->andWhere(static::$cacheDataOption['where']);
            }
            // order by
            if (isset(static::$cacheDataOption['orderBy'])) {
                $query->orderBy(static::$cacheDataOption['orderBy']);
            }
            // group by
            if (isset(static::$cacheDataOption['groupBy'])) {
                $query->groupBy(static::$cacheDataOption['groupBy']);
            }

            $data = $query->asArray()->all();
            Yii::$app->cache->set($key, $data);
        }

        // 写到内存中，防止重复读取硬盘
        self::$_DataCache[$key] = $data;

        return $data;
    }

}