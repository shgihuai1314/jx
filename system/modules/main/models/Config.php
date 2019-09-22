<?php

namespace system\modules\main\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%tab_config}}".
 *
 * @property integer $id                        流水id
 * @property string $name                       配置名称
 * @property string $type                       配置类型
 * @property string $title                      配置描述
 * @property integer $module                    所属模块
 * @property string $extra                      配置项
 * @property string $remark                     配置说明
 * @property integer $create_time               创建时间
 * @property integer $update_time               更新时间
 * @property string $value                      值
 * @property integer $sort                      排序
 */
class Config extends \system\models\Model
{
    public $log_flag = true;
    public $log_options = [
        'target_name' => 'name',//日志目标对应的字段名，默认name
        'model_name' => '系统配置',//模型名称
        'except_field' => ['create_time', 'update_time'],
    ];

    public static $cacheData = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_config';
    }

    // 图片路径值
    public $value_image = '';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['create_time', 'update_time', 'sort'], 'integer'],
            [['value', 'value_image'], 'string'],
            [['name'], 'string', 'max' => 100],
            [['type', 'module', 'title'], 'string', 'max' => 64],
            [['extra'], 'string', 'max' => 255],
            [['remark'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '配置ID',
            'name' => '配置标识',
            'title' => '配置标题',
            'sort' => '排序',
            'type' => '配置类型',
            'module' => '所属模块',
            'value' => '配置值',
            'value_image' => '上传图片',
            'extra' => '配置项',
            'remark' => '配置说明',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
        ], parent::attributeLabels());
    }

    /**
     * 列表配置
     * @return array
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
        $list = [
            'type' => Yii::$app->systemConfig->getValue('CONFIG_TYPE_LIST', []),
            'module' => Modules::getModuleMap(),
        ];
	
	    return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($insert) {
                $this->create_time = time();
            }

            if ($this->type == 'image') {
                $this->value = $this->value_image;
            }

            $this->update_time = time();

            // 将\r\n转义
            $this->value = str_replace('\r\n', "\r\n", $this->value);
            $this->extra = str_replace('\r\n', "\r\n", $this->extra);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        if ($this->type == 'image') {
            $this->value_image = $this->value;
        }

    }
}
