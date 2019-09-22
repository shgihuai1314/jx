<?php
/**
 * Created by PhpStorm.
 * User: BixChen
 * Date: 2018/5/10
 * Time: 17:34
 */
namespace system\modules\user\widgets;

use system\modules\user\models\Group;
use system\modules\user\models\User;
use yii\base\Widget;

class UserSelectFlowWidget extends Widget
{
    public $default_user;                   //默认用户
    public $input_name = 'user_select';     //字段名
    public $input_class = '';               //字段class
    public $required = true;                //是否必填
    public $input_label;                    //label名称


    public function init()
    {

    }

    public function run()
    {
        $user_arr = [];
        $user = explode(',', $this->default_user);
        foreach($user as $v){
            if(substr($v, 0 , 1) == 'G'){
                $group = Group::findOne(['id' => substr($v,1)]);
                $userID = $group['manager'];
            }else{
                $userID = substr($v,1);
            }

            $user_arr[]=[
                'user_id' => $userID,
                'name' => User::getInfo($userID, 'realname'),
            ];
        }

        return $this->render('userSelectFlowwidget',[
            'user_arr' => $user_arr,
            'input_name' => $this->input_name,
            'input_class' => $this->input_class,
            'required' => $this->required,
            'input_label' => $this->input_label,
        ]);
    }
}