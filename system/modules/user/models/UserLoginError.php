<?php

namespace system\modules\user\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_user_login_error".
 *
 * @property integer $id
 * @property string $type
 * @property string $target
 * @property integer $times
 * @property integer $update_at
 * @property integer $total
 */
class UserLoginError extends \system\models\Model
{
    private static $_error_times = 5;  // 预设错误次数
    private static $_error_interval = 15; // 预设暂停间隔时间; 15分钟

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_user_login_error';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['total', 'update_at', 'times'], 'integer'],
            [['type'], 'string', 'max' => 64],
            [['target'], 'string', 'max' => 255],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '流水id',
            'type' => '类型',
            'target' => '目标',
            'times' => '次数',
            'update_at' => '最后更新时间',
            'total' => '错误总数',
        ], parent::attributeLabels());
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            $this->update_at = time();
            $this->total += 1;

            return true;
        }

        return false;
    }


    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // 错误达到一定次数后，写日志
        $error_times = Yii::$app->systemConfig->getValue('USER_LOGIN_ERROR_TIMES', self::$_error_times);
        $error_interval = Yii::$app->systemConfig->getValue('USER_LOGIN_ERROR_INTERVAL', self::$_error_interval);
        if ($this->times >= $error_times) {
            $typeMap = [
                'ip' => 'IP',
                'username' => '用户名',
            ];

            // IP：xxx 本次连续登录错误次数达到xx次，已锁定xx分钟，错误总数达到xx次;
            $content = "{$typeMap[$this->type]}:{$this->target} 本次连续登录错误次数达到{$this->times}次，已锁定{$error_interval}分钟，此{$typeMap[$this->type]}错误总数达到{$this->total}次；";

            Yii::$app->systemLog->write([
                'type' => 'login',
                'target_id2' => $this->id,
                'content' => $content
            ]);

            // 判断是否需要禁用用户
            if ($this->type == 'username') {
                $error_lock = Yii::$app->systemConfig->getValue('USER_LOGIN_ERROR_LOCK', 0);
                if ($error_lock) {
                    User::lockUser(['username' => $this->target]);
                }
            }
        }
    }

    /**
     * 保存错误记录
     * @param $type
     * @param $target
     * @return bool
     */
    public static function saveData($type, $target)
    {
        $error_interval = Yii::$app->systemConfig->getValue('USER_LOGIN_ERROR_INTERVAL', self::$_error_interval);
        $error_interval = (int)$error_interval;

        /** @var self $model */
        $model = self::find()->where(['type' => $type, 'target' => $target])->one();
        if ($model) {
            // 如果时间+间隔时间 小于 当前时间，那么说明数据已经过期，把次数置为0
            if ($model->update_at + ($error_interval*60) < time()) {
                $model->times = 0;
            }
        } else {
            $model = new self();
        }

        $model->times += 1; // 次数+1
        $model->type = $type;
        $model->target = $target;

        return $model->save();
    }

    private static $_cache_times_map;

    /**
     * 获取当前错误次数
     * @param $type
     * @param $target
     * @return bool
     */
    public static function getErrorTimes($type, $target)
    {
        if (!isset(self::$_cache_times_map[$type][$target])) {
            $times = 0;
            /** @var self $model */
            $model = self::find()->where(['type' => $type, 'target' => $target])->one();
            if ($model) {
                // 用户登录错误间隔
                $error_interval = Yii::$app->systemConfig->getValue('USER_LOGIN_ERROR_INTERVAL', self::$_error_interval);
                $error_interval = (int)$error_interval;
                // 如果时间+间隔时间 小于 当前时间，那么说明数据已经过期，把次数置为0
                if ($model->update_at + ($error_interval*60) > time()) {
                    $times = $model->times;
                }
            }

            self::$_cache_times_map[$type][$target] = $times;
        }

        return self::$_cache_times_map[$type][$target];
    }

    /**
     * 判断ip或者用户是否可以登录
     * @param $type
     * @param $target
     * @return bool
     */
    public static function canLogin($type, $target)
    {
        // 错误次数
        $error_times = Yii::$app->systemConfig->getValue('USER_LOGIN_ERROR_TIMES', self::$_error_times);
        // 如果错误次数未设置或者设置为0，那么可以登录
        if (!$error_times) {
            return true;
        }

        $times = self::getErrorTimes($type, $target);

        if ($times > 0 && $times >= $error_times){
            return false;
        }

        return true;
    }

    /**
     * 是否要显示验证码
     * @return bool
     */
    public static function showCaptcha()
    {
        // 判断错误次数达到了多少要显示验证码，0代表无论多少次错误都不显示验证码
        $error_captcha = Yii::$app->systemConfig->getValue('USER_LOGIN_ERROR_CAPTCHA', 3);
        if ($error_captcha == 0) {
            return false;
        }

        // 当前ip已经登录错误的次数
        $error_total = UserLoginError::getErrorTimes('ip', Yii::$app->request->getUserIP());
        if ($error_total == 0 || $error_total < $error_captcha) {
            return false;
        }

        return true;
    }

}
