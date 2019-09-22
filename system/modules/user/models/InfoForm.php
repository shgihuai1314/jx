<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/31
 * Time: 下午3:23
 */

namespace system\modules\user\models;

use yii\base\Model;

class InfoForm extends Model
{
    public $avatar;//头像
    public $autograph_img;//
    public $username;//用户名
    public $realname;//真实名字
    public $phone;//手机号
    public $email;//邮箱
    public $gender;//性别
    public $oldPassword; // 旧密码
    public $newPassword; // 新密码
    public $newPasswordRepeat; // 重复密码
    public $personal_profile; // 个人简介
    public $qq; // qq
    public $wx; // 微信

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['realname', 'avatar'], 'required', 'on' => 'update'],
            [['realname', 'phone', 'email'], 'trim', 'on' => 'update'],
            [['oldPassword', 'newPassword', 'newPasswordRepeat'], 'required', 'on' => 'password'],
            [['oldPassword', 'newPassword', 'newPasswordRepeat'], 'trim', 'on' => 'password'],
            ['email', 'email'],
            ['autograph_img', 'string'],

            [['qq','wx','personal_profile','username','gender'],'safe','on' => 'update']
            //['oldPassword', 'validatePassword', 'on' => 'password']
        ];
    }

    /**
     * 验证密码
     * @param $attribute
     * @param $params
     * @return string
     */
    /*public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->oldPassword)) {
                $this->addError($attribute, '原始密码不正确');
            }
        }
    }*/

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'avatar' => '头像',
            'autograph_img' => '个性签名',
            'username'=>'用户名',
            'realname' => '姓名',
            'phone' => '手机',
            'email' => '邮箱',
            'gender'=>'性别',
            'qq'=>'QQ',
            'wx'=>'微信',
            'personal_profile'=>'个人简介',
            'oldPassword' => '原始密码',
            'newPassword' => '新密码',
            'newPasswordRepeat' => '确认密码',
        ];
    }

    /**
     * @inheritDoc
     */
    public function scenarios()
    {
        return [
            'update' => ['gender','avatar','username','realname','qq','wx','personal_profile','phone', 'email', 'autograph_img'],
            'password' => ['oldPassword', 'newPassword', 'newPasswordRepeat'],
        ];
    }

    private $_user;

    /**
     * 查找用户
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findOne(\Yii::$app->user->identity->getId());
        }

        return $this->_user;
    }

    /**
     * 更改密码
     * @return bool|string
     */
    public function changePassword()
    {
        if (!$this->validate()) {
            //print_r($this->errors);
            $errors = $this->getFirstErrors();
            foreach ($errors as $error) {
                return $error;
            }
        }

        if ($this->newPassword != $this->newPasswordRepeat) {
            return '确认密码输入不一致！';
        }

        if ($this->oldPassword == $this->newPassword) {
            return '输入的三个密码相同！';
        }

        $user = $this->getUser();

        if (!$user->validatePassword($this->oldPassword)) {
            return '原始密码不正确！';
        }

        $user->setPassword($this->newPassword);
        return $user->save();
    }

    /**
     * 更新资料
     * @return bool
     */
    public function updateInfo($changes='')
    {
        if (!$this->validate()) {
            $errors = $this->getFirstErrors();
            foreach ($errors as $error) {
                return $error;
            }
        }

        $user = $this->getUser();
        $user->gender = $this->gender;
        $user->avatar = $this->avatar;
        //$user->username = $this->username;
        $user->realname = $this->realname;
        $user->phone = $this->phone;
        $user->email = $this->email;
        $user->qq = $this->qq;
        $user->wx = $this->wx;
        $user->personal_profile = $this->personal_profile;


        $res=$user->save();

        if($changes){
            if($res){
                return  true;
            }else{
                return false;
//               print_r($user->getErrors());die;
            }

        }
        if ($res) {
            $userExtend = UserExtend::findOne($user->user_id);
            if (!$userExtend) {
                $userExtend = new UserExtend();
                $userExtend->user_id = $user->user_id;
            }
            $userExtend->autograph_img = $this->autograph_img;
            $userExtend->save();
        }

        return $res;
    }

}