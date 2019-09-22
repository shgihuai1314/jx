<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/10/15
 * Time: 13:47
 */

namespace system\modules\user\widgets;

use yii\bootstrap\Widget;

class UserSelectWidget extends Widget
{
    //模型对象
    public $model = null;
    //指定的属性
    public $attribute = null;
    // input的name
    public $inputName = '';
    //初始默认值
    public $defaultValue = '';
    //label标签
    public $label = '请选择';
    //弹出窗口标题
    public $title = '请选择';
    //最大选择数量,默认为0不限制;
    public $select_max = '0';
    //显示tab页类型,默认为department,position,user(department:按部门;position:按职位;user:按用户);
    public $show_page = 'department,position';
    //选择范围,如G1,G2,G3,P1,P2,U1,U2,U3
    public $show_range = '';
    //选择范围交集或并集,默认为0(0:交集;1:并集;)
    public $range_type = 0;
    //选择的类型,department(部门),position(职位),user(用户)
    public $select_type = 'department,position,user';
    // 是否必填
    public $required = true;
    /**
     * @inheritDoc
     */
    public function run()
    {
        parent::run();
        
        if ($this->model != null) {
            $attribute = $this->attribute;
            $this->inputName = $attribute;
            $this->defaultValue = $this->attribute;
        }
    
        $this->select_max = $this->select_max == 0 ? \Yii::$app->systemConfig->getValue('USER_SELECT_MAX_NUM', 200) : $this->select_max;
        $this->defaultValue = is_array($this->defaultValue) ? implode(',', $this->defaultValue) : $this->defaultValue;
        return $this->render('user-group-select', [
            'inputName' => $this->inputName,
            'defaultValue' => $this->defaultValue,
            'label' => $this->label,
            'title' => $this->title,
            'select_max' => $this->select_max,
            'show_page' => $this->show_page,
            'show_range' => $this->show_range,
            'range_type' => $this->range_type,
            'select_type' => $this->select_type,
            'required' => $this->required
        ]);
    }
}