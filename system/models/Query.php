<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2018-3-20
 * Time: 9:36
 */

namespace system\models;

use Yii;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use system\modules\main\models\ExtendsField;

class Query extends ActiveQuery
{
    /**
     * @var Pagination null
     */
    public $pagination = null;

    // 表名
    public $tableName = null;

    /**
     * @param null $db
     * @return array|\yii\db\ActiveRecord[]
     */
    public function all($db = null)
    {
        return $this->pagination == null ? parent::all($db) :
            ArrayHelper::merge(['pagination' => $this->pagination], parent::all($db));
    }

    /**
     * 解析条件
     * @param $conditions
     * @param array $params
     * @return $this
     */
    public function search($conditions, $params = [])
    {
        $params = empty($params) ? Yii::$app->request->queryParams : $params;

        // 加载允许作为搜索项的扩展字段
        $fields = ExtendsField::getSearchFieldByTable($this->tableName);
        foreach ($fields as $field) {
            if (in_array($field['show_type'], ['select', 'radio'])) {
                $conditions[] = $field['field_name'];
            } else {
                $conditions[$field['field_name']] = 'like';
            }
        }

        foreach ($conditions as $key => $val) {
            if (is_numeric($key) && is_string($val)) {
                // 把['field']格式转换成 ['field' => ['field' => ':val']]
                unset($conditions[$key]);
                $conditions[$val] = [$val => ':val'];
            } elseif (is_string($key) && in_array($val, ['like', 'not in', '!='])) {
                // 把['field' => 'like|not in|!=']格式转换成 ['field' => ['like|not in|!=', 'field', ':val']]
                $conditions[$key] = [$val, $key, ':val'];
            } elseif (is_string($key) && $val == 'date_range') {
                // 把['field' => 'date_range']格式转换成 ['field' => ['between', 'field', ':val', ':val']]
                $conditions[$key] = function ($val) use ($key) {
                    list($start, $end) = explode(' - ', $val);
                    return [
                        'between',
                        $key,
                        strtotime($start . '00:00:00'),
                        strtotime($end . '23:59:59'),
                    ];
                };
            }
        }

        foreach ($params as $key => $val) {
            if ($val == '') {
                continue;
            }
            if (array_key_exists($key, $conditions)) {
                // 纯字符串条件，如“name = ':val'”把':val'替换成$val值
                if (is_string($conditions[$key])) {
                    $condition = str_replace(':val', $val, $conditions[$key]);
                }
                // 数组格式条件 ['key' => ':val'], ['!=', 'key', ':val'], ['like', 'key', ':val']
                elseif (is_array($conditions[$key])) {
                    $condition = $this->parseCondition($conditions[$key], $val);
                }
                // 闭包格式条件 ['key' => function($val) { return ……}]
                elseif ($conditions[$key] instanceof \Closure) {
                    $condition = $conditions[$key]($val);
                }
                $this->andWhere($condition);
            }
        }
        return $this;
    }

    /**
     * @param int $size 每页显示条数
     * @return $this
     */
    public function paginate($size = 0)
    {
        $size = Yii::$app->request->get('pageSize', $size);
        //分页
        $this->pagination = new Pagination([
            'defaultPageSize' => $size == 0 ?  Yii::$app->systemConfig->getValue('LIST_ROWS', 13) : $size,
            'totalCount' => $this->count(),
            'validatePage' => false
        ]);

        return $this->offset($this->pagination->offset)->limit($this->pagination->limit);
    }

    /**
     * 解析数组格式的条件
     * @param array $condition ['key' => ':val'], ['!=', 'key', ':val'], ['or', ['like', 'version', ':val'], ['like', 'module_id', ':val']]
     * @param string|integer $val
     */
    private function parseCondition($condition, $val)
    {
        foreach ($condition as $k => $v) {
            if (is_array($v)) {
                $condition[$k] = $this->parseCondition($v, $val);
            } elseif ($condition[$k] instanceof \Closure) {
                // 闭包格式条件 ['key' => function($val) { return ……}]
                $condition = [$k => $condition[$k]($val)];
            } else {
                $condition[$k] = $v == ':val' ? $val : $v;
            }
        }

        return $condition;
    }

}