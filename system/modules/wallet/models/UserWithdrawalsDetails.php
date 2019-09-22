<?php

namespace system\modules\wallet\models;

use system\modules\user\models\User;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_user_withdrawals_details".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $realname
 * @property string $account
 * @property string $remark
 * @property integer $create_at
 * @property integer $create_by
 * @property integer $state
 * @property integer $auditor
 * @property integer $auditor_at
 * @property string $auditor_remark
 * @property integer $amount
 */
class UserWithdrawalsDetails extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_user_withdrawals_details';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['user_id'], 'required'],
            [['user_id', 'create_at', 'create_by', 'state', 'auditor', 'auditor_at', 'amount'], 'integer'],
            [['realname', 'account', 'remark', 'auditor_remark'], 'string'],
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
            'realname' => '姓名',
            'account' => '提现到的账户',
            'remark' => '提现备注',
            'create_at' => '创建时间',
            'create_by' => '创建人',
            'state' => '提现状态',
            'auditor' => '审核id',
            'amount' => '金额',
            'auditor_at' => '审核时间',
            'auditor_remark' => '审核备注',
        ], parent::attributeLabels());
    }


    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->create_by = Yii::$app->user->id;//创建人
                $this->create_at = time();//提现时间
            } else {
                $this->auditor = Yii::$app->user->id;//审核人
                $this->auditor_at = time();//审核时间
            }

            return true;
        }

        return false;
    }


    public function getUserMsg(){
        return $this->hasOne(User::className(), ['user_id' => 'user_id'])->select(['user_id','username','avatar']);
    }

    //获取用户钱包
    public function getUserWallet(){
        return $this->hasOne(UserWallet::className(),['user_id'=>'user_id'])->select(['bind_type','bind_bank','user_id']);
    }

    /**
     * 提现，审核
     * @param string $type
     * @param $data
     * @return bool
     */
    public static function addData($data)
    {
        if (isset($data['user_id']) && !isset($data['id'])) {
            $useWallet = UserWallet::find()->where(['user_id' => $data['user_id']])->asArray()->one();
            $model = new self;
            $model->user_id = $useWallet['user_id'];
            $model->realname = $useWallet['realname'];
            $model->account = $useWallet['bind_acount'];
            $model->remark = $data['remark'];
            $model->amount = $data['amount']*1000;
        } else {
            $model = self::findOne(['id' => $data['id']]);
            $model->auditor_remark = isset($data['auditor_remark']) ? $data['auditor_remark'] : '';
            $model->state = isset($data['state']) ? $data['state'] : 0;
        }

        if ($model->save()) {
            return true;
        } else {
            return false;
        }

    }
}
