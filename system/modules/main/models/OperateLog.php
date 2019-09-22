<?php

namespace system\modules\main\models;

use system\modules\user\models\User;
use yii\helpers\ArrayHelper;
use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "tab_operate_log".
 *
 * @property integer $ID
 * @property string $action_type
 * @property string $module
 * @property string $target_name
 * @property string $target_id
 * @property string $template
 * @property string $data
 * @property string $content
 * @property integer $type
 * @property string $model_class
 * @property integer $operator
 * @property integer $opt_time
 * @property string $opt_ip
 */
class OperateLog extends \system\models\Model
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_operate_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge([
            [['action_type', 'operator', 'opt_time', 'opt_ip'], 'required'],
            [['template', 'data', 'content'], 'string'],
            [['target_id', 'type', 'operator', 'opt_time'], 'integer'],
            [['action_type', 'module', 'opt_ip'], 'string', 'max' => 32],
            [['model_class'], 'string', 'max' => 255],
            [['target_name'], 'safe'],
        ], parent::attributeLabels());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return parent::getRule([
            'ID' => '日志ID',
            'action_type' => '操作类型',
            'module' => '模块名称',
            'target_name' => '操作目标',
            'target_id' => '操作ID',
            'template' => '显示模板',
            'data' => '日志数据',
            'content' => '日志内容',
            'type' => '数据类型',
            'model_class' => '模型类',
            'operator' => '操作人',
            'opt_time' => '操作时间',
            'opt_ip' => '操作人IP',
        ]);
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
			'action_type' => Yii::$app->systemConfig->getValue('OPERATE_LOG_ACTION_TYPE', []),
			'type' => [0 => '格式数据', 'edit' => '文本数据'],
			'module' => Modules::getModuleMap()
		];
		
		return self::getAttributeValue($list, $field, $key, $default);
	}
	
	/**
	 * 获取操作人信息
	 * @return \yii\db\ActiveQuery
	 */
	public function getOperatorInfo()
	{
		return $this->hasOne(User::className(), ['user_id' => 'operator']);
	}
	
	/**
	 * 获取日志内容
	 * @return mixed|string
	 */
	public function getLogContent()
	{
		if ($this->type == 0) {//格式数据、根据模板进行解析
			//先获取内容模板
			if (empty($this->template)) {
				$templates = Yii::$app->systemConfig->getValue('OPERATE_LOG_CONTENT_TEMPLATE', []);//配置管理中取出日志内容模板列表
				//找出对应action_type的模板,没有则使用默认模板
				$this->template = ArrayHelper::getValue($templates, $this->action_type, ArrayHelper::getValue($templates, 'default', '{$operator} {$action_type}了 {$model_name}【{$target_name}】 {$data}'));
			}
			
			$content = $this->template;
			
			//替换操作人
			$content = str_replace('{$operator}', User::getInfo($this->operator), $content);
			//替换操作类型
			$content = str_replace('{$action_type}', self::getAttributesList('action_type', $this->action_type, $this->action_type), $content);
			//替换目标名称
			$content = str_replace('{$target_name}', $this->target_name, $content);

			// 判断类存在
			$model = !empty($this->model_class) && class_exists($this->model_class) ? new $this->model_class : null;
			if ($model != null) {
				//替换模型名称
				$content = str_replace('{$model_name}', isset($model->log_options['model_name']) ? $model->log_options['model_name'] : substr($this->model_class, strrpos($this->model_class, '\\') + 1), $content);
			}
			
			$data = empty($this->content) ? [] : ArrayHelper::toArray(json_decode($this->content));
			$details = [];
			foreach ($data as $key => $val) {
				if (isset($val['old']) && isset($val['new'])) {//既有旧数据也有新数据
					$details[] = $key . ':' . (is_array($val['old']) ? implode(',', $val['old']) : $val['old']) . '=>' . (is_array($val['new']) ? implode(',', $val['new']) : $val['new']);
				} elseif (isset($val['new'])) {//只有新数据
					$details[] = $key . ':' . $val['new'];
				}
			}
			
			//替换详情
			$content = str_replace('{$data}',
				empty($details) ? '' : Html::tag('span', '{ ' . implode('；', $details) . ' }', ['class' => 'system-tip', 'data-flag' => 3, 'data-tip' => $this->contentTip]),
				$content);
		} else {//文本数据，直接返回数据内容
			$content = $this->content;
		}
		
		return $content;
	}
	
	/**
	 * 获取内容的tip标签
	 * @return string
	 */
	public function getContentTip()
	{
		$tip = '';
		$data = empty($this->content) ? [] : ArrayHelper::toArray(json_decode($this->content));
		$th = '';
		foreach ($data as $key => $val) {
			$th = Html::tag('th', '属性', ['width' => 300, 'class' => 'label-tip']);
			$td = Html::tag('td', $key, ['class' => 'label-tip']);
			if (isset($val['old'])) {
				$th .= Html::tag('th', '原值', ['width' => 350]);
				$td .= Html::tag('td', is_array($val['old']) ? implode(',', $val['old']) : $val['old']);
			}
			if (isset($val['new'])) {
				$th .= Html::tag('th', '值', ['width' => 350]);
				$td .= Html::tag('td', is_array($val['new']) ? implode(',', $val['new']) : $val['new']);
			}
			$tip .= Html::tag('tr', $td);
		}
		
		$tip = Html::tag('table', $th.$tip, ['class' => 'content-tip word-break', 'style' => 'width:500px', 'border' => 1]);
		return $tip;
	}
}
