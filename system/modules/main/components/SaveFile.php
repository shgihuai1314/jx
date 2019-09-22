<?php
/**
 * Created by PhpStorm.
 * User: luobo
 * Date: 17/5/19
 * Time: 下午3:26
 */

namespace system\modules\main\components;

use system\modules\main\models\Fileinfo;
use system\core\utils\Tool;
use yii\helpers\ArrayHelper;
use yii\base\Component;

class SaveFile extends Component
{
	/**
	 * 保存附件
	 * @param $data string|array 附件信息(多条附件是保存json格式字符串的数组，单条是json格式的字符串)
	 * @param $source string 附件来源
	 * @return int|null
	 */
    public function save($data, $source)
    {
	    //$data格式 "{'type':'文件类型','src':'文件路径','name':'文件名', 'size': '文件大小'}"或者保存该格式的数组
	    if (empty($data)) {
			return '0';
	    }

	    if (is_array($data)) {
	    	$ids = [];
		    foreach ($data as $one) {
			    if (empty($one)) {
				    continue;
			    }
			    $obj = json_decode($one);
			    $model = Fileinfo::findOne(['src' => $obj->src]);
			    if (empty($model)) {
				    $model1 = new Fileinfo();
				    $model1->file_type = $obj->type;
				    $model1->name = $obj->name;
				    $model1->src = $obj->src;
				    $model1->size = $obj->size;
				    $model1->source = $source;
				    if ($model1->save()) {
					    $ids[] = $model1->file_id;
				    }
			    } else {
				    $ids[] = $model->file_id;
			    }
			}
			return implode(',', $ids);
	    } else {
		    $obj = json_decode($data);
		    $model = Fileinfo::findOne(['src' => $obj->src]);
		    if (empty($model)) {
			    $model1 = new Fileinfo();
			    $model1->file_type = $obj->type;
			    $model1->name = $obj->name;
			    $model1->src = $obj->src;
			    $model1->size = $obj->size;
			    $model1->source = $source;
			    if ($model1->save()) {
				    return $model1->file_id;
			    } else {
				    return '0';
			    }
		    } else {
		    	return $model->file_id;
		    }
	    }
    }
	
	/**
	 * 根据文件ID获取文件路径
	 * @param array|integer $val
	 * @param bool|string $field
	 * @return array|mixed|\yii\db\ActiveRecord
	 */
	public static function get($val, $field = false)
	{
		$allFiles = Fileinfo::getAllDataCache();

		if (is_array($val)) {
			$list = Tool::array_sort_by_keys($allFiles, $val);
			return $field == false ? $list : ArrayHelper::getColumn($list, $field);
		} else {
			if (isset($allFiles[$val])) {
				return $field == false ? $allFiles[$val] : $allFiles[$val][$field];
			} else {
				return $val;
			}
		}
	}
}