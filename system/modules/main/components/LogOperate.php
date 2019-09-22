<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/9/25
 * Time: 11:11
 */

namespace system\modules\main\components;

use system\modules\main\models\OperateLog;
use system\modules\user\models\User;
use yii\helpers\Json;
use yii\web\Application;
use yii\helpers\ArrayHelper;
use yii\base\Component;
use Yii;

class LogOperate extends Component
{
    private $_error = [];

    /**
     * 获取错误
     * @return string
     */
    public function error()
    {
        return $this->_error ? Json::encode($this->_error) : '';
    }

	/**
	 * 写日志
	 * @param $data array 日志数据
	 * [
	 *      'action_type' => 'other',//操作类型(add、edit、delete、other)，默认为other
	 *      'module' => '', //模块名称，如main,group,email,contacts
	 *      'target_name' => '', //目标名称
	 *      'target_id' => 0, //目标ID
	 *      'template' => '{$operator} {$action_type}了 {$model_name}【{$target_name}】 {$data}' //内容显示模板，会根据如下规则把数据替换
	 *                      {$operator}//操作人姓名
	 *                      {$action_type}//操作类型
	 *                      {$model_name}//模型名称
	 *                      {$target_name}//目标名称
	 *                      {$data}//数据(add、edit、delete类型的数据会被转换成固定格式，other类型不会转换)
	 *      'data' => '', //数据
	 *      'model_class' => '', //模型名称
	 *      'type' => 0, //数据类型(0:格式数据;1:文本数据)，默认为0
	 * ]
	 * @return bool
	 */
	public function write($data)
	{
		$model = new OperateLog();

        if (Yii::$app instanceof \yii\console\Application) {
            $user_id = 0;
            $user_ip = '::1';
        } else {
            try {
                if (!Yii::$app->user->isGuest) {
                    $user_id = Yii::$app->user->id;
                } else {
                    $user_id = -1; // 未知用户
                }
            } catch (\Exception $e) {
                $user_id = -1; // 未知用户
            }
            $user_ip = Yii::$app->request->userIP;
        }

		$data['action_type'] = isset($data['action_type']) ? $data['action_type'] : 'other';
		$data['operator'] = $user_id;
		$data['opt_time'] = time();
		$data['opt_ip'] = $user_ip;

		foreach ($data as $var => $value) {
			if ($model->hasAttribute($var)) {
				$model->$var = $value;
			}
		}

		if ($model->save()) {
			return true;
		} else {
		    $this->_error[] = $model->errors;
			return false;
		}
	}
	
	/**
	 * 获取脏数组数组
	 * @param $oldData array 原始数据数组
	 * @param $valueData array 新数据数组
	 * @param $model \system\models\Model 数据对象
	 * @return array 脏数组数据：新数据的格式['字段名1'=>'值', '字段名2'=>'值'], 修改数据的格式['字段名1'=>['原值', '新值'], '字段名2'=>['原值', '新值'], ]
	 */
	public static function dirtyData($oldData, $valueData, $model = null)
	{
		if ($model != null) {
			$labels = $model->attributeLabels();//字段标签列表
			$convert = $model->convertList;//字段值转换列表
		} else {
			$labels = [];
			$convert = [];
		}
		
		$dirtyArr = [];
		foreach($valueData as $key => $value) {
			$oldValue = ArrayHelper::getValue($oldData, $key, '');//旧数据
			if ($value != $oldValue) {
				$label = ArrayHelper::getValue($labels, $key, $key);//字段标签
				$type = ArrayHelper::getValue($convert, $key);//字段值转换
				
				if (!empty($oldData)) {
					$dirtyArr[$label]['old'] = is_null($model) ? $oldValue : self::getConvertValue($model, $type, $key, $oldValue);
				}
				$dirtyArr[$label]['new'] = is_null($model) ? $value : self::getConvertValue($model, $type, $key, $value);
			}
		}
		return $dirtyArr;
	}
	
	/**
	 * 获取转换后的字段值
	 * @param \system\models\Model $model 数据对象
	 * @param string $type 转换方式
	 * @param string $key 字段名
	 * @param string $value 字段值
	 * @return false|mixed|string
	 */
	private static function getConvertValue($model, $type, $key, $value)
	{
		if ($value !== '' && $value !== null && !is_array($value)) {//值不为空
			if (empty($type)) {//默认从属性列表中取值
				$value = $model::getAttributesList($key, $value, $value);
			} elseif ($type == 'datetime') {//转换成日期
				$value = date('Y-m-d H:i:s', $value);
			}elseif ($type == 'date') {//转换成年月日
				$value = date('Y-m-d', $value);
			} elseif ($type == 'user') {//转换成用户姓名
				$value = User::getInfo($value);
			} elseif ($type == 'password') {//转换成用户姓名
				$value = '******';
			} else {//通过类方法转换
				$value = call_user_func($type, $value);
			}
		}
		
		return $value;
	}
}