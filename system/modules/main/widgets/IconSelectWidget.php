<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-5-29
 * Time: 22:35
 */

namespace system\modules\main\widgets;

use yii\base\Widget;
use Yii;
use yii\helpers\ArrayHelper;

class IconSelectWidget extends Widget
{
    //模型对象
    public $model = null;
    //指定的属性
    public $attribute = null;
    // 提交的字段名
    public $inputName = 'icon';
    // 默认值
    public $icon = '';

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();

        if ($this->model != null) {
            $attribute = $this->attribute;
            $this->icon = $this->model->$attribute;
            $this->inputName = $attribute;
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = '';
        if (file_exists(Yii::getAlias('@webroot') . '/static/lib/iconfont/demo_fontclass.html')) {
            $content = file_get_contents(Yii::getAlias('@webroot') . '/static/lib/iconfont/demo_fontclass.html');
        }

        preg_match_all('/<div class="name">(.*?)<\/div>\s*<div class="fontclass">\.(.*?)<\/div>/i', $content, $matchs);

        $iconName = ArrayHelper::getValue($matchs, 1, []);// 字体类名
        $iconClass = ArrayHelper::getValue($matchs, 2, []);// 字体名称

        $iconMap = [];
        foreach ($iconClass as $k => $val) {
            $iconMap[$val] = $iconName[$k];
        }

        return $this->render('icon/index', [
            'icon' => $this->icon,
            'name' => $this->inputName,
            'iconMap' => $iconMap
        ]);
    }

}