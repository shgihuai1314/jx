<?php
/**
 * 系统配置参数组件
 * 获取形式：Yii::$app->systemConfig->get('name', []);
 * 添加形式：Yii::$app->systemConfig->add('模块名称如：user', ['title' => '用户测试', 'name' => '此处应全部大些，比如:USER_TEST', 'type' => 'string', 'value' => '张三']);
 * type：包含：number, string, text, array, enum
 * User: ligang
 * Date: 2017/3/12
 * Time: 下午3:29
 */

namespace system\modules\main\components;

use system\core\utils\Tool;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class Config extends Component
{
    /**
     * 获取value,如果是array，那么返回key=value数组
     * @param $name string 名称
     * @param string $default 默认值
     * @return mixed
     */
    public function getValue($name, $default = '')
    {
        return $this->get($name, 'value', $default);
    }

    /**
     * 然后根据name获取值
     * @param $name string 名称
     * @param string $field 字段
     * @param string $default 默认值
     * @return mixed
     */
    public function get($name, $field = '', $default = '')
    {
        $data = \system\modules\main\models\Config::getAllDataCache();
        $data = ArrayHelper::index($data, 'name');

        // 如果键不存在
        if (!isset($data[$name])) {
            return $default;
        }

        $item = $data[$name];
        // 枚举类型
        if ($item['type'] == 'enum') {
            $item['extra_ori'] = $item['extra']; // extra的原始数据
            $item['extra'] = Tool::paramsToArray($item['extra']);
        }
        // 数组类型
        else if ($item['type'] == 'array') {
            $item['value_ori'] = $item['value']; // value的原始数据
            $item['value'] = Tool::paramsToArray($item['value']);
        }

        if ($field != '') {
            if (isset($item[$field]) && $item[$field]!= '') {
                return $item[$field];
            } else {
                // 字段不存在，返回默认值
                return $default;
            }
        }

        return $item;
    }

    /**
     * 更新缓存
     */
    public function refresh()
    {
        \system\modules\main\models\Config::getAllDataCache(true);
    }

    /**
     * 添加配置，禁止配置system中都项目
     * @param $module string 所属模块
     * @param $data array 数据
     * 格式如：['title' => '标题', 'type' => '类型,包括：number,string,text,array,enum', 'value' => '值']
     * @return bool
     */
    public function set($module, $data)
    {
        if (!isset($data['name'], $data['title'])) {
            return false;
        }

        // 设置默认值
        if (!isset($data['type'])) {
            $data['type'] = 'string';
        }

        $model = \system\modules\main\models\Config::findOne(['name' => $data['name']]);

        if (!$model) {
            $model = new \system\modules\main\models\Config();
            $model->name = $data['name'];
        }

        // title, name, type, value, config_group
        $model->setAttributes([
            'type' => isset($data['type']) ? $data['type'] : '',    // 类型
            'title' => isset($data['title']) ? $data['title'] : '', // 标题
            'module' => $module, // 所属模块
            'value' => isset($data['value']) ? $data['value'] : '',   // 值
            'extra' => isset($data['extra']) ? $data['extra'] : '',   //配置值
            'remark' => isset($data['remark']) ? $data['remark'] : ''//配置说明,
        ]);

        $res = $model->save();
        $this->refresh();
        return $res;
    }

    /**
     * 删除一个配置项
     * @param $config_name string 配置项
     * @return bool|false|int
     */
    public function remove($config_name)
    {
        $model = \system\modules\main\models\Config::findOne(['name' => $config_name]);
        if ($model) {
            return $model->delete();
        }

        return true;
    }

    /**
     * 移除一个模块的配置
     * @param $module string 模块id
     * @return int
     */
    public function removeModule($module)
    {
        $res = \system\modules\main\models\Config::deleteAll(['module' => $module]);
        $this->refresh();
        return $res;
    }

    /**
     * 根据name设置value
     * @param $name
     * @param $value
     * @return bool
     */
    public function setValue($name, $value)
    {
        $model = \system\modules\main\models\Config::findOne(['name' => $name]);

        if (!$model) {
            return false;
        }

        $model->value = $value;

        $res = $model->save();
        $this->refresh();
        return $res;
    }
}