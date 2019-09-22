<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/5/12
 * Time: 下午5:40
 */

namespace Api;

use system\modules\course\models\CourseStudent;
use system\modules\role\models\AuthAssign;
use system\modules\role\models\AuthRole;
use system\modules\user\components\UserIdentity as User;
use system\modules\user\models\Attention;
use Yii;
use yii\helpers\ArrayHelper;

class UserController extends BaseApiController
{
    // 不需要认证的方法，用下划线形式，如get_info
    public $notAuthAction = ['auth', 'get-info', 'get-user-home'];

    /**
     * @info 用户认证接口
     * @method POST
     * @desc 认证账号可以是用户名、手机号、邮箱中的任意一种。手机或邮箱必须经过验证后才能进行认证
     * @param string $name 用户名|手机号|邮箱 required
     * @param string $password 用户密码 required
     * @return array
     * [
     *      'code' => 0,
     *      'message' => '认证成功',
     *      'data' => [
     *          'accessToken' => 身份令牌,
     *          'baseInfo' => [
     *              'username' => 用户名,
     *              'realname' => 姓名,
     *              'group_id' => 部门ID,
     *              'group' => 部门名称,
     *              'position_id' => 职位ID,
     *              'position' => 职位名称,
     *              'avatar' => 头像,
     *              'cert_num' => 证件号码,
     *              'phone' => 手机号,
     *              'gender' => 性别,
     *              'email' => 邮箱,
     *              'validation_email' => 邮箱是否已验证,
     *              'validation_phone' => 手机号是否已验证
     *          ]
     *      ]
     * ]
     */
    public function actionAuth()
    {
        //验证用户名和密码正确
        $data = Yii::$app->request->post();

        if (!isset($data['name'], $data['password'])) {
            return $this->apiReturn(false, '参数错误');
        }

        // 查找用户名
        $userModel = User::findByUsername($data['name']);
        if (!$userModel) {
            // 手机号验证
            $userModel = User::findOne(['phone' => $data['name']]);
            if ($userModel && $userModel->validation_phone == 0) {
                return $this->apiReturn(false, '手机号未验证，请用其他方式登录！');
            }
        }

        if (!$userModel) {
            // 邮箱验证
            $userModel = User::findOne(['email' => $data['name']]);
            if ($userModel && $userModel->validation_email == 0) {
                return $this->apiReturn(false, '邮箱未验证，请用其他方式登录！');
            }
        }

        //本地验证
        if (!$userModel) {
            return $this->apiReturn(false, '账号不存在');
        }

        // 获取用户认证接口列表
        $authList = Yii::$app->systemConfig->getValue('USER_AUTH_API_LIST', []);
        // 需要到第三方认证的入口
        $gateways = Yii::$app->systemConfig->getValue('API_AUTH_GATEWAYS', []);

        // 管理员认证、认证接口为空、入口类型不在API_AUTH_GATEWAYS配置中 只用系统验证
        if (empty($authList) || !in_array('app', $gateways)) {
            $loginRes = $userModel->validatePassword($data['password']);
        } else {
            $loginRes = false;
            // 循环遍历验证接口列表
            foreach ($authList as $auth) {
                if ($auth == 'local') {// 系统验证
                    $loginRes = $userModel->validatePassword($data['password']);
                } elseif ($auth == 'no-local') {// 不做验证
                    continue;
                } else { // 第三方接口，如common\api\Srun4k
                    $auth = str_replace('/', '\\', $auth);
                    if (method_exists($auth, 'login')) {
                        $arr = [
                            'username' => $userModel->username,
                            'password' => $data['password'],
                        ];
                        $loginRes = call_user_func([$auth, 'login'], $arr);
                    } else {
                        $loginRes = $this->apiReturn(false, '认证接口无法调用');
                    }
                }

                // 如果验证成功，不再继续验证
                if ($loginRes['code'] == 0) {
                    break;
                }
            }
        }

        if (!$loginRes) {
            return $this->apiReturn(false, '用户名或密码错误');
        } else {
            Yii::$app->user->login($userModel);
            //刷新 access_token
           // $userModel->refreshToken();
            return $this->apiReturn(true, '认证成功', [
                    'accessToken' => $userModel->access_token,//访问token
                    'baseInfo' => $userModel->getBaseInfo(false),
                    'roles' => AuthAssign::getRoleFiledByUser(\Yii::$app->user->getId())
                ]
            );
        }
    }

    /**
     * @info 获取用户基本信息
     * @method GET
     * @param integer $id 用户ID required
     * @return array
     * [
     *      'code' => 0,
     *      'message' => 'success',
     *      'data' => [
     *          'accessToken' => 身份令牌,
     *          'baseInfo' => [
     *              'username' => 用户名,
     *              'realname' => 姓名,
     *              'group_id' => 部门ID,
     *              'group' => 部门名称,
     *              'position_id' => 职位ID,
     *              'position' => 职位名称,
     *              'avatar' => 头像,
     *              'cert_num' => 证件号码,
     *              'phone' => 手机号,
     *              'gender' => 性别,
     *              'email' => 邮箱,
     *              'validation_email' => 邮箱是否已验证,
     *              'validation_phone' => 手机号是否已验证
     *          ]
     *      ]
     * ]
     */
    public function actionGetInfo()
    {
        $params = Yii::$app->request->get();

        if (!isset($params['id'])) {
            return $this->apiReturn(false, '缺少参数！');
        }

        $model = User::findOne($params['id']);

        if (!$model) {
            return $this->apiReturn(false, '用户不存在！');
        }

        $userInfo = $model->getBaseInfo();

        return $this->apiReturn(true, 'success', $userInfo);
    }

    /**
     * @info 搜索老师用户
     * @method GET
     * @param string $keyword 搜索关键字
     * @return array
     * [
     *      'code' => 0,
     *      'message' => 'success',
     *      'data' => [
     *          [
     *              'user_id' => 用户id,
     *              'username' => 用户名,
     *              'realname' => 真实姓名,
     *              'phone' => 号码,
     *              'email' => 邮箱,
     *              'status' => 状态,
     *              'avatar' => 头像
     *          ],
     *          ...
     *      ]
     * ]
     */
    public function actionSearch()
    {
        $role = AuthRole::findOne(['code' => 'ROLE_TEACHER']);

        $teachers = AuthAssign::getUserByRole($role->role_id);

        $data = User::find()
            ->select('u.user_id, u.realname, u.avatar')
            ->from(AuthAssign::tableName() . ' r')
            ->innerJoin(User::tableName() . ' u', 'u.user_id = r.user_id')
            ->search(['keyword' => ['like', 'u.realname', ':val']])
            ->andWhere(['u.user_id' => ArrayHelper::getColumn($teachers, 'user_id')])
            ->indexBy('user_id')
            ->asArray()
            ->all();

        return $this->apiReturn(true, 'success', $data);
    }

    /**
     * @info 搜索学生用户
     * @method GET
     * @param string $keyword 搜索关键字
     * @return array
     * [
     *      'code' => 0,
     *      'message' => 'success',
     *      'data' => [
     *          [
     *              'user_id' => 用户id,
     *              'username' => 用户名,
     *              'realname' => 真实姓名,
     *              'phone' => 号码,
     *              'email' => 邮箱,
     *              'status' => 状态,
     *              'avatar' => 头像
     *          ],
     *          ...
     *      ]
     * ]
     */
    public function actionSearchStudent()
    {
        $role = AuthRole::findOne(['code' => 'ROLE_STUDENT']);

        $teachers = AuthAssign::getUserByRole($role->role_id);

        $data = User::find()
            ->select('u.user_id, u.realname, u.avatar')
            ->from(AuthAssign::tableName() . ' r')
            ->innerJoin(User::tableName() . ' u', 'u.user_id = r.user_id')
            ->search(['keyword' => ['like', 'u.realname', ':val']])
            ->andWhere(['u.user_id' => ArrayHelper::getColumn($teachers, 'user_id')])
            ->asArray()
            ->all();

        return $this->apiReturn(true, 'success', $data);
    }

    /**
     * @info 获取个人主页个人信息
     * @method GET
     * @param string $user_id 用户ID required
     * @return array
     * [
     *      'code' => 0,
     *      'message' => 'success',
     *      'data' => [
     *          [
     *              'user_id' => 用户id,
     *              'avatar' => 用户头像,
     *              'realname' => 用户姓名,
     *              'personal_profile' => 个人简介,
     *              'is_attention' => 是否关注,
     *              'follower_num' => 关注人数,
     *              'fans_num' => 粉丝人数
     *          ],
     *          ...
     *      ]
     * ]
     */
    public function actionGetUserHome($user_id)
    {
        $model = User::find()->select('user_id,realname,avatar,personal_profile')
            ->where(['user_id' => $user_id])->asArray()->one();

        if (!$model) {
            return $this->apiReturn(false, '用户不存在！');
        }

        $model['is_attention'] = Attention::isAttention($user_id);
        $model['follower_num'] = Attention::getFollowerNum($user_id);
        $model['fans_num'] = Attention::getFansNum($user_id);
        $model['integrals'] = 0;
        $model['study_time'] = floor(CourseStudent::getStudyTime($user_id)/3600);

        return $this->apiReturn(true, 'success', $model);
    }
}
