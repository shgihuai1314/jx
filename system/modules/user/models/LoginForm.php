<?php
namespace system\modules\user\models;

use system\modules\user\components\UserIdentity;
use yii\base\Model;
use Yii;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $verifyCode;
    public $rememberMe = false;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            //['password', 'validatePassword'],
            //['verifyCode', 'captcha'],
            //['verifyCode', 'captcha','captchaAction'=>'/user/default/captcha','message'=>'验证码不正确！'],
            ['verifyCode', 'string'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '无效的用户名或者密码');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return array whether the user is logged in successfully
     */
    public function login()
    {
        if (!$this->validate()) {
            return [
                'code' => 1,
                'message' => '数据验证失败'
            ];
        }

        $user = $this->getUser();
        if (!$user || !$user->validatePassword($this->password)) {
            // 记录错误日志
            UserLoginError::saveData('ip', Yii::$app->request->getUserIP());
            UserLoginError::saveData('username', $this->username);

            return [
                'code' => 1,
                'message' => '无效的用户名或者密码',
            ];
        }

        return [
            'code' => 0,
            'message' => '验证成功',
        ];

    }

    /**
     * Finds user by [[username]]
     * @return UserIdentity|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = UserIdentity::findByUsername($this->username);
        }

        return $this->_user;
    }


}
