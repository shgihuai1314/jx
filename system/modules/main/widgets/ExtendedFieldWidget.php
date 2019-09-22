<?php

namespace system\modules\main\widgets;

use system\modules\main\models\ExtendsField;
use yii\base\Widget;
use yii\db\ActiveRecord;

/**
 * 扩展字段自动增加表单部件
 * 1.根据显示类型自动渲染html
 * 2.自动获取当前模型的扩展字段
 * 3.提交修改方式
 * 4.扩展后有增加，修改，显示的操作对应部件方法
 * Class ExtendedFieldWidget
 * @package system\modules\main\widgets
 * 使用
 * 1.表单中使用小部件
 * <?=\system\modules\main\widgets\ExtendedFieldWidget::widget(['model'=>$model])?>
 * 2.在模型rules方法的return上面加上以下代码
 * //扩展字段
 * return parent::getRule($rules);
 */
class ExtendedFieldWidget extends Widget
{
    /** @var ActiveRecord $model */
    public $model;

    public function run()
    {
        parent::run();
        // 获取要显示的字段
        $extendsFieldData = ExtendsField::getShowDataByTable($this->model->tableName());
        if (!$extendsFieldData) {
            return '';
        }

        foreach ($extendsFieldData as $v) {
            // 判断字段在模型中是否存在，如果不存在，那么不在解析
            if (!$this->model->hasAttribute($v['field_name'])) {
                continue; // 跳过本次循环
            }

            $viewFile = $v['show_type'];
            // 判断模版是否存在
            if (!is_file($this->getViewPath().'/controls/'.$v['show_type'].'.php')) {
                $viewFile = 'text';
            }

            echo $this->render('controls/' . $viewFile, [
                'data' => $v,
                'model' =>$this->model,
            ]);
        }
    }
}