<?php

namespace system\modules\main\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "fileinfo".
 *
 * @property integer $file_id
 * @property string $file_type
 * @property string $name
 * @property string $src
 * @property string $source
 * @property string $size
 * @property integer $upload_time
 * @property string $upload_user
 * @property integer $is_del
 */
class Fileinfo extends \system\models\Model
{
    public static $cacheData = true;
    public static $cacheDataOption = [
        'indexBy' => 'file_id',
        'where' => ['is_del' => 0],
        'orderBy' => ['upload_time' => SORT_DESC]
    ];

	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_fileinfo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['file_type', 'name', 'src', 'source'], 'required'],
            [['upload_time', 'is_del'], 'integer'],
            [['file_type', 'source', 'size', 'upload_user'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 128],
            [['src'], 'string', 'max' => 255]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'file_id' => '文件ID',
            'file_type' => '文件类型',
            'name' => '文件名',
            'src' => '路径',
            'source' => '来源',
            'size' => '文件大小',
            'upload_time' => '上传时间',
            'upload_user' => '上传人',
            'is_del' => '是否删除',
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
			'is_del' => [1 => '是', 0 => '否']
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
				$this->upload_time = time();
				$this->upload_user = Yii::$app->user->id;
			}
			return true;
		}
		
		return false;
	}
	
}
