<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/9/5
 * Time: 22:08
 */

namespace system\modules\main\components;

use system\modules\main\models\Options;
use yii\base\Component;
use Yii;
use yii\helpers\ArrayHelper;

class Option extends Component
{
    /**
     * 返回指定的选项
     * @param $name string 名称
     * @param string $default 不存在时返回此默认值
     * @return null|string
     */
    public static function getValue($name, $default = '')
    {
        $data = Yii::$app->cache->get('allOptions');
        if (!$data) {
            $all = Options::find()->asArray()->all();
            $data = ArrayHelper::map($all, 'name', 'value');
        }

        //如果key存在，返回对应的数据
        if (isset($data[$name])) {
            $value = json_decode($data[$name]);
            return is_string($value) ? $value : ArrayHelper::toArray($value);
        }

        //返回默认值
        return $default;
    }

    /**
     * 设置选项
     * @param $name string 键
     * @param string $value 值
     * @return bool
     */
    public static function setValue($name, $value = '')
    {
        if ($name == '') {
            return false;
        }

        //从数据中查询key是否存在
        $model = Options::findOne(['name' => $name]);

        //将数据处理成json格式
        $newValue = json_encode($value);

        if ($model) {
            $model->value = $newValue;
        } else {
            $model = new Options();
            $model->name = $name;
            $model->value = $newValue;
        }

        if ($model->save()) {
            $data = Yii::$app->cache->get('allOptions');
            $data[$name] = $newValue;
            Yii::$app->cache->set('allOptions', $data);
            return true;
        } else {
            return false;
        }
    }
}