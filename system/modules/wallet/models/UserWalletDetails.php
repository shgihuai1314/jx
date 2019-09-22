<?php

namespace system\modules\wallet\models;

use system\modules\user\models\User;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_user_wallet_details".
 * @property integer $id
 * @property integer $user_id
 * @property integer $amount
 * @property integer $after_account_balance
 * @property integer $type
 * @property string $description
 * @property integer $create_by
 * @property integer $create_at
 */
class UserWalletDetails extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_user_wallet_details';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['amount'], 'required'],
            [['user_id',/* 'after_account_balance',*/
                'create_by', 'create_at', 'target_user_id', 'target_id'], 'integer'],
            [['description', 'type'], 'string'],
            [['amount', 'after_account_balance'], 'safe']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '主键',
            'user_id' => '用户id',
            'amount' => '金额，以分为单位,带符号',
            'after_account_balance' => '操作后账户余额',
            'type' => '流水类型',
            'description' => '流水详细描述',
            'create_by' => '创建人',
            'create_at' => '创建时间',
            'target_id' => '关联的编号',
            'target_user_id' => '关联的用户id'
        ], parent::attributeLabels());
    }


    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                if (!Yii::$app instanceof \yii\console\Application) {
                    $this->create_by = Yii::$app->user->id;
                }
                $this->create_at = time();
                //修改账户余额
                $model = UserWallet::findOne(['user_id' => $this->user_id]);
                if ($model) {
                    //操作后账户余额
                    $this->after_account_balance = $model->account_balance + ($this->amount);//钱包余额

                    //更新钱包的账户余额,当前余额加单笔账单的金额
                    $model->account_balance = $model->account_balance + ($this->amount);

//                    echo  $model->account_balance."\n";
                    if (!$model->save()) {
                        print_r($model->getErrors());
                        exit('添加失败');
                    }

                } else {
                    //没有绑定的时候添加钱包基本信息,相当于初始化钱包
                    $walletModel = new UserWallet();
                    $walletModel->user_id = $this->user_id;
                    if ($this->user_id == 0) {
                        $walletModel->user_name = 'pingtai';
                        $walletModel->realname = '平台';
                    } elseif ($this->user_id == -1) {
                        $walletModel->user_name = 'yunying';
                        $walletModel->realname = '运营商';
                    } else {
                        $walletModel->user_name = User::getUser($this->user_id)->username;
                        $walletModel->realname = User::getUser($this->user_id)->realname;
                    }
                    $walletModel->account_balance = $this->amount;
                    $this->after_account_balance = $this->amount;
                    //$this->after_account_balance = $this->amount;
                    if (!$walletModel->save()) {
                        print_r($walletModel->getErrors());
                        exit('添加失败');
                    }
//                    return false;
                }


            }
            return true;
        }

        return false;
    }

    /**
     * 保存数据
     * @param $data
     * @return array
     */
    public static function saveData($data)
    {
        $model = new self;
        // $model->
        $model->loadDefaultValues();
        //填充数据
        if ($model->load($data, '') && $model->save()) {
            return ['code' => 0, 'message' => '数据添加成功'];
        }

        return ['code' => 1, 'message' => '数据添加失败'];
    }
}
