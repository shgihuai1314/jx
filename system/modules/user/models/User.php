<?php

namespace system\modules\user\models;

use system\modules\course\models\CourseTeam;
use system\modules\course\models\StudentTask;
use system\modules\role\models\AuthAssign;
use system\modules\role\models\AuthRole;
use system\core\utils\Tool;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "{{%tab_user}}".
 * @property integer $user_id                           用户id，唯一标识
 * @property string $username                           用户名
 * @property string $realname                           姓名
 * @property integer $gender                            性别
 * @property string $avatar                             头像路径
 * @property string $auth_key                           auth
 * @property string $password_hash                      密码
 * @property string $password_reset_token               重置密码token
 * @property string $access_token                       API认证令牌
 * @property string $phone                              手机号码
 * @property string $qq                                 QQ
 * @property string $email                              email
 * @property integer $role_id                           角色id，关联角色模块; 这个字段弃用，因为目前是多角色模式
 * @property integer $status                            状态：0正常，1禁用，2锁定，3删除
 * @property integer $group_id                          部门id，关联部门模块
 * @property integer $position_id                       职位id，关联职位模块
 * @property integer $validation_email                  是否验证了email，0非，1是，默认0；验证通过可以发送邮件提醒
 * @property integer $validation_phone                  是否验证了手机号，0非，1是，默认0；验证通过可以发送短信提醒
 * @property integer $last_change_password              最后一次修改密码的时间，默认0
 * @property integer $sort                              排序，数字越大优先级越靠前
 * @property string $remark                             备注
 * @property string $personal_profile                   个人简介
 */
class User extends \system\models\Model
{
    public $password;
    public $roles = [];
    //public $avatarFile;

    const STATUS_ACTIVE = 0; // 正常
    const STATUS_DISABLED = 1; // 禁用
    const STATUS_LOCK = 2; // 锁定，再认证需要使用验证码，登录成功后改为正常
    const STATUS_DELETE = 3; // 删除，不能再使用
    public $log_flag = false;  // 只有管理员操作时才开启日志
    public $log_options = [
        'target_name' => 'realname',//日志目标对应的字段名，默认name
        'model_name' => '用户',//模型名称
    ];
    public $convertList = [
        'group_id' => ['system\modules\user\models\Group', 'getNameById'],
        'last_change_password' => 'datetime',
        'password_hash' => 'password',
    ];

    //允许批量处理的字段信息
    public static $batch_operate_fields = [
        'username' => '用户名',
        'realname' => '姓名',
        'gender' => '性别',
        'password' => '密码',
        'phone' => '手机号',
        'qq' => 'QQ',
        'email' => '邮箱',
        'group_id' => '部门',
        'position_id' => '职位',
        'sort' => '排序'
    ];

    //批量处理字段特殊处理
    public static $batch_fields_convert = [
        'group_id' => ['system\modules\user\models\Group', 'updateGroupByBatch'],
        'position_id' => ['system\modules\user\models\Position', 'getPositionByBatch'],
    ];

    public static $cacheData = true;
    public static $cacheDataOption = [
        'indexBy' => 'user_id',
        'where' => ['status' => self::STATUS_ACTIVE],
        'orderBy' => ['sort' => SORT_DESC, 'user_id' => SORT_ASC],
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['gender', 'role_id', 'status', 'group_id', 'position_id', 'validation_email', 'validation_phone', 'last_change_password', 'sort',], 'integer'],
            [['username', 'avatar', 'password_hash', 'password_reset_token', 'access_token', 'email', 'password', 'remark','personal_profile'], 'string', 'max' => 255],
            [['realname', 'email'], 'string', 'max' => 64],
            [['auth_key', 'phone', 'qq'], 'string', 'max' => 32],
            [['username'], 'unique'],
            ['roles', 'safe'],
            [['group_id'], 'default', 'value' => 0],
            [['sort'], 'default', 'value' => 0],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'user_id' => '用户id',
            'username' => '用户名',
            'realname' => '姓名',
            'gender' => '性别',
            'avatar' => '头像',
            'auth_key' => 'auth key',
            'password_hash' => '密码',
            'password_reset_token' => '密码重设token',
            'access_token' => 'API认证令牌',
            'phone' => '手机号',
            'qq' => 'QQ',
            'email' => 'email',
            'role_id' => '角色',
            'status' => '状态',
            'group_id' => '部门',
            'position_id' => '职位',
            'validation_email' => '是否验证',
            'validation_phone' => '是否验证',
            'last_change_password' => '最后更新密码',
            'sort' => '排序',
            'remark' => '备注',
            'personal_profile'=>'个人简介'
        ], parent::attributeLabels());
    }

    /**
     * 选择字段属性列表
     * @param string $field 字段
     * @param string $key 字段对应的key
     * @param bool $default 默认值
     * @return array|bool|string
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
        $list = [
            //用户状态
            'status' => Yii::$app->systemConfig->getValue('USER_STATUS_LIST', []),
            // 职位
            'position_id' => Position::getAllMap(),
            // 角色
            'role_id' => ArrayHelper::merge(['0' => '-'], AuthRole::getAllMap()),
            // 性别
            'gender' => Yii::$app->systemConfig->getValue('USER_GENDER_LIST', []),
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        $this->is_admin = 1;
        if (parent::beforeSave($insert)) {
            if ($this->password) {
                // 如果有设置密码
                $this->setPassword($this->password);
                $this->last_change_password = time(); // 最后修改密码的时间
            }

            // 如果没有头像，那么随机指定
            if (!$this->avatar) {
                $this->avatar = Yii::$app->request->hostInfo . '/static/images/avatar/default/' . rand(1, 20) . '.jpg';
            }

            return true;
        }

        return false;
    }

    /**
     * Validates password
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password, 10);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * 获取所有有效用户
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAllUser()
    {
        $data = self::find()
            ->select(['user_id', 'username', 'realname', 'phone', 'email', 'status', 'avatar'])
            ->where(['status' => self::STATUS_ACTIVE])
            ->indexBy('user_id')
            ->asArray()
            ->all();

        return $data;
    }

    /**
     * 获取所有管理员用户
     * @return array
     */
    public static function getAllAdminUser()
    {
        $data = self::find()
            ->select(['user_id', 'username', 'realname', 'phone', 'email', 'status', 'avatar'])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->asArray()
            ->all();

        return ArrayHelper::index($data, 'user_id');
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        // 处理用户角色
        if ($this->is_admin == 0) {
            AuthAssign::deleteUser($this->user_id);
        } else if ($this->roles) {
            AuthAssign::saveRolesToUser($this->roles, $this->user_id);
        }

        $changes = Yii::$app->systemOperateLog->dirtyData($this->_old, $this->getCurrentData());
        $changeFields = array_keys($changes);
        foreach ($changeFields as $one) {
            if (in_array($one, ['username', 'realname', 'avatar', 'group_id', 'position_id', 'status'])) {
                @unlink(Yii::getAlias('@webroot') . '/data/user.json');
                break;
            }
        }

    }

    /**
     * @inheritDoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        @unlink(Yii::getAlias('@webroot') . '/data/user.json');
    }

    /**
     * 获取用户的所有角色id
     * @return array
     */
    public function getRoles()
    {
        return AuthAssign::getRoleIdByUser($this->user_id);
    }

    /**
     * 锁定用户，当用户是正常状态时锁定用户
     * @param $condition
     * @return bool
     */
    public static function lockUser($condition)
    {
        $model = self::findOne($condition);
        if ($model) {
            if ($model->status == self::STATUS_ACTIVE) {
                $model->status = self::STATUS_LOCK;

                return $model->save();
            }
        }

        return false;
    }

    /**
     * 关联部门表
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
    }

    /**
     * 关联职位表
     * @return \yii\db\ActiveQuery
     */
    public function getPosition()
    {
        return $this->hasOne(Position::className(), ['id' => 'position_id']);
    }

    /**
     * 关联多角色表
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasMany(AuthAssign::className(), ['user_id' => 'user_id'])->with('role');
    }

    /**
     * 获取用户扩展信息
     * @return \yii\db\ActiveQuery
     */
    public function getExtend()
    {
        return $this->hasOne(UserExtend::className(), ['user_id' => 'user_id']);
    }

    /**
     * 根据user_id获取一个用户对象
     * @param $user_id int  用户id
     * @param $refresh bool 是否强制刷新数据
     *
     * @return bool|mixed
     */
    public static function getUser($user_id, $refresh = false)
    {
        return self::findOne($user_id);
        // 缓存单个用户对象
        /* $cacheKey = 'user:user_id:'.$user_id;
         $data = Yii::$app->cache->get($cacheKey);
         if (!$data || $refresh) {
             $data = self::findOne($user_id);
             Yii::$app->cache->set($cacheKey, $data);
         }

         return $data;*/
    }

    /**
     * 根据用户名获取用户模型
     * @param $username string 用户名
     * @return static
     */
    public static function getUserByUsername($username)
    {
        return self::findOne(['username' => $username]);
    }

    /**
     * 根据条件获取用户数据
     * @param null $condition
     * @param bool $has_key
     * @return array
     */
    public static function getDataByCondition($condition = null, $has_key = true)
    {
        $data = self::getAllDataCache();
        if (empty($condition)) {
            return Tool::get_array_by_condition($data, [], $has_key);
        } else {
            //如果condition是int，匹配ID；condition是string，匹配name；condition是数组，匹配字段
            $condition = is_numeric($condition) ? ['user_id' => $condition]
                : (is_string($condition) ? ['realname' => $condition] : $condition);

            return Tool::get_array_by_condition($data, $condition, $has_key);
        }
    }

    /**
     * 获取用户id和姓名的map
     * @param null $ids
     * @return array
     */
    public static function getNameArr($ids = null)
    {
        $list = self::getDataByCondition(empty($ids) ? $ids : ['user_id' => $ids]);
        $arr = ArrayHelper::map($list, 'user_id', 'realname');

        return $arr;
    }

    /**
     * 获取单个用户指定字段的信息
     * @param string $val 提供的值
     * @param string $field 要获取的字段
     * @param string $by 提供的字段名
     * @return string
     */
    public static function getInfo($val, $field = 'realname', $by = 'user_id')
    {
        $allUser = self::getAllDataCache();

        if ($by == 'user_id') {
            $field = (is_string($field) && !empty($field)) ? $field : 'realname';

            return isset($allUser[$val][$field]) ? $allUser[$val][$field] : '';
        } else {
            foreach ($allUser as $one) {
                if ($one[$by] == $val) {
                    return $one[$field];
                }
            }

            return $val;
        }
    }

    /**
     * 根据用户id获取对应的姓名
     * @param $user_id int|array 用户id 或 用户id数组
     * @return false|null|string
     */
    public static function getNameById($user_id)
    {
        $data = self::find()
            ->select(['realname'])
            ->where(['user_id' => $user_id])
            ->column();

        return $data ? implode(',', $data) : '';
    }

    private static $_baseField = ['user_id','username', 'realname', 'group_id', 'position_id', 'avatar', 'cert_num', 'phone', 'gender', 'email', 'validation_email', 'validation_phone', 'personal_profile'];

    /**
     * 获取用户基本资料
     * @param bool $hide 是否隐藏手机号和邮箱
     * @return mixed
     */
    public function getBaseInfo($hide = true)
    {
        $arr = ArrayHelper::toArray($this);

        $baseField = Yii::$app->systemConfig->getValue('USER_BASE_FIELD', self::$_baseField);
        foreach ($arr as $key => $val) {
            if (in_array($key, $baseField)) {
                if ($hide) {
                    if ($key == 'phone' && !empty($val)) {
                        $val = substr_replace($val, '****', 3, 4);
                    } elseif ($key == 'email' && !empty($val)) {
                        $val = explode('@', $val);
                        $val[0] = substr_replace($val[0], '****', 3);
                        $val = implode('@', $val);
                    }
                }
                $res[$key] = $val;
            }
        }

        $res['group'] = Group::getNameById(ArrayHelper::getValue($res, 'group_id'));
        $res['position'] = self::getAttributesList('position_id', $res['position_id'], '-');
        //$res['gender'] = self::getAttributesList('gender', $res['gender']);
        if (substr($res['avatar'], 0, 7) != 'http://' && substr($res['avatar'], 0, 8) != 'https://') {
            $res['avatar'] = Yii::$app->request->hostInfo . $res['avatar'];
        }

        return $res;
    }

    /*
     * 关联老师职位
     * */
    public function getTeacher(){
        return $this->hasOne(Position::className(), ['id' => 'position_id']);
    }

    /**
     * 获取数据map
     * @return array
     */
    public static function getNameMap()
    {
        $data = self::getAllDataCache();

       //获取教师
        $user_ids = AuthAssign::find()->select('user_id')->andWhere(['role_id' => 2])->asArray()->column();

        $teacher = User::find()->andWhere(['user_id' => array_unique($user_ids)])->asArray()->all();

        $teacher_ids = ArrayHelper::getColumn($teacher,'user_id');

        $user = [];
        foreach($data as $k => $v){
            if(isset($teacher_ids[$k])){
                $user[$k] = $v;
            }
        }

        return ArrayHelper::map($user, 'user_id', 'realname');
    }

    /**
     * 关联学生任务表
     * @return array
     */
    public function getStudentTask(){
        return $this->hasMany(StudentTask::className(), ['student_id' => 'user_id']);
    }

    /**
     * 根据id获取用户
     *
     * @param $ids
     * @return array|\system\modules\course\models\CourseStudent[]|\system\modules\homework\models\Homework[]|\system\modules\homework\models\HomeworkRecord[]|User[]|\yii\db\ActiveRecord[]
     */
    public static function getUserById($ids)
    {
        $data = self::find()
            ->select(['user_id', 'username', 'realname', 'phone', 'email', 'status', 'avatar'])
            ->where(['user_id' => $ids])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->asArray()
            ->all();

        return $data ? $data : [];
    }
}
