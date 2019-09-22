<?php

namespace system\modules\cron\models;

use system\core\utils\Tool;
use system\modules\main\models\Modules;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_cron_tasks".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property string $module_id
 * @property string $command
 * @property string $desc
 * @property integer $sort
 * @property integer $create_by
 * @property integer $create_time
 */
class CronTasks extends \system\models\Model
{
    //是否记录日志
    public $log_flag = true;
    //日志信息配置
    public $log_options = [
        'model_name' => '计划任务',//模型名称
        'except_field' => ['id'],
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_cron_tasks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['name', 'command'], 'required'],
            [['name', 'module_id', 'command', 'desc'], 'string'],
            [['type', 'sort'], 'integer'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'Id',
            'name' => '任务名称',
            'type' => '执行方式',
            'module_id' => '所属模块',
            'command' => '执行指令',
            'desc' => '任务说明',
            'sort' => '排序',
            'create_by' => '创建人',
            'create_time' => '创建时间',
        ], parent::attributeLabels());
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
            'type' => [0 => '控制器方法', 1 => '控制台命令', 2 => 'php脚本文件'],
            'module_id' => Modules::getModuleMap(),
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCron()
    {
        return $this->hasMany(Cron::className(), ['task_id' => 'id']);
    }

    /**
     * 获取@extension/cron/文件夹中的文件列表
     * @return array
     */
    public static function getTaskFiles()
    {
        $data = [];

        $path = Yii::getAlias('@extension') . DIRECTORY_SEPARATOR . 'cron';
        if (!file_exists($path)) {
            return $data;
        }
        $handle = opendir($path);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $src = $path . DIRECTORY_SEPARATOR . $file;
            if (is_file($src)) {
                $data[$src] = $file;
            }
        }
        closedir($handle);
        return $data;
    }

    public static $cacheData = true;
    public static $cacheDataOption = [
        'orderBy' => ['sort' => SORT_DESC]
    ];

    /**
     * @return array
     */
    public static function getTaskMap()
    {
        $data = self::getAllDataCache();
        return ArrayHelper::map($data, 'id', 'name');
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        Cron::deleteAll(['task_id' => $this->id]);
        parent::afterDelete();
    }

    /**
     * @param $data
     * @return bool
     */
    public static function setTask($data)
    {
//        $data = [
//            'name' => '',// 任务名称
//            'type' => '',// 执行方式
//            'module_id' => '',// 所属模块
//            'command' => '',// 执行命令
//            'desc' => '',// 任务说明
//        ];
        $model = new self();
        $model->loadDefaultValues();
        if ($model->load($data, '')) {
            return $model->save();
        }

        return false;
    }
}
