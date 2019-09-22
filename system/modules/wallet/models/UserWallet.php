<?php

namespace system\modules\wallet\models;

use system\modules\user\components\UserIdentity;
use system\modules\user\models\User;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_user_wallet".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $realname
 * @property string $user_name
 * @property integer $account_balance
 * @property string $account_pass
 * @property integer $bind_type
 * @property string $bind_acount
 * @property string $bind_bank
 * @property string $bind_remark
 * @property integer $last_update_at
 */
class UserWallet extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_user_wallet';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['user_id', 'bind_type', 'last_update_at'], 'integer'],
            [['realname', 'user_name', 'account_pass', 'bind_acount', 'bind_bank', 'bind_remark'], 'string'],
            [['account_balance'], 'safe']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '主键',
            'user_id' => '用户id,0代表系统-1代表运营商',
            'realname' => '真实姓名去用户表的realname',
            'user_name' => '账户名',
            'account_balance' => '账户余额',
            'account_pass' => '账户密码',
            'bind_type' => '绑定类型',
            'bind_acount' => '绑定账号',
            'bind_bank' => '绑定银行',
            'bind_remark' => '绑定备注',
            'last_update_at' => '最后更新时间',
        ], parent::attributeLabels());
    }

    /**
     * 选择性属性列表
     * @param string $field 字段名
     * @param string $key 查找的key
     * @param string $default 默认值(未查到结果的情况下返回)
     * @return array|bool|string
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
        $list = [
            'bind_type' => [0 => '支付宝', 1 => '微信', 2 => '银行卡'],
            'bind_bank' => [0 => '招商银行', 1 => '工商银行'],
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * 获取账户数据
     * @param $userId
     * @return array|null|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
     */
    public static function getOneData($userId)
    {
        $oneData = self::findOne(['user_id' => $userId]);
        return $oneData;
    }

    /**
     * @param bool $insert
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
           /* if ($insert) {
                // $this->last_update_at = time();
                if (!Yii::$app instanceof \yii\console\Application) {
                    $this->user_id = Yii::$app->user->id;
                    $this->realname = User::getUser(Yii::$app->user->id)->realname;
                    $this->user_name = User::getUser(Yii::$app->user->id)->username;
                }
                $this->setPassword($this->account_pass);
            }*/

            $this->setPassword($this->account_pass);
            $this->last_update_at = time();
            return true;
        }

        return false;
    }

    /**
     * 验证密码
     * @param $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->account_pass);
    }

    /**
     * 设置密码
     * @param $password
     */
    public function setPassword($password)
    {
        $this->account_pass = Yii::$app->security->generatePasswordHash($password, 10);
    }

    /**
     * 添加修改账号
     * @param string $type
     * @param $data
     */
    public static function addAccount($type = '', $data)
    {
        $model = $type ? self::findOne(['user_id' => Yii::$app->user->id]) : new self;
        $type ? false : $model->account_pass = $data['account_pass'];//账号密码
        $model->bind_type = $data['bind_type'];//绑定类型
        $model->bind_acount = $data['bind_acount'];//绑定账号
        $model->bind_bank = isset($data['bind_bank']) ? $data['bind_bank'] : '';
        $model->bind_remark = isset($data['bind_remark']) ? $data['bind_remark'] : '';

        if (!$model->save()) {
            return false;
        }

        return true;
    }
}
