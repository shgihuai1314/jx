<?php

namespace system\controllers;

use system\core\utils\Tool;
use system\modules\user\models\Group;
use system\modules\main\components\LoadModule;
use system\modules\main\models\Migration;
use system\modules\main\models\Modules;
use system\modules\user\models\User;
use system\core\utils\Excel;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use Yii;

/**
 * Site controller
 */
class InstallController extends Controller
{
    // 禁用csrf的action
    public $disableCsrfAction = ['upload'];

    public $layout = false;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        // 关闭csrf验证
        if ($this->hasProperty('disableCsrfAction') && in_array($action->id, $this->disableCsrfAction)) {
            $this->enableCsrfValidation = false;
        }

        if (parent::beforeAction($action)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'upload' => [
                'class' => \system\modules\main\extend\Upload::className(),
                'dir' => 'main/logo',
            ],
        ];
    }

    /**
     * 安装引导
     * @return string
     */
    public function actionIndex()
    {
        if (LoadModule::checkDb()) {
            header('Location: /admin.php');
            exit();
        }

        return $this->render('index');
    }

    /**
     * 安装模块和配置信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionSetup($action)
    {
        set_time_limit(0);
        $data = Yii::$app->request->post();

        switch ($action) {
            case 'db-setup':// 数据库设置
                // 关闭数据库，重新连接
                Yii::$app->db->close();
                Yii::$app->db->dsn = 'mysql:host=' . env('hostname') . ';port=' . env('port');
                Yii::$app->db->open();

                // 创建指定库
                Yii::$app->db->createCommand('create database if not exists `' . $data['db_name'] . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci')->execute();

                // 检查该管理员是否存在
                $res = Yii::$app->db->createCommand('select USER from mysql.user where USER = "' . $data['db_user'] . '" and HOST = "localhost"')->queryOne();
                if ($res) {// 存在则直接修改密码
                    Yii::$app->db->createCommand('SET PASSWORD FOR "' . $data['db_user'] . '"@"localhost" = PASSWORD("' . $data['db_password'] . '")')->execute();
                } else {// 不存在则创建管理员
                    Yii::$app->db->createCommand("CREATE USER '" . $data['db_user'] . "'@'localhost' IDENTIFIED BY '" . $data['db_password'] . "'")->execute();
                }

                Yii::$app->db->createCommand('grant all privileges on ' . $data['db_name'] . '.* to "' . $data['db_user'] . '"@"localhost"')->execute();
                // 刷新权限
                Yii::$app->db->createCommand('flush privileges')->execute();

                // 修改.env配置文件
                env_set([
                    'username' => $data['db_user'],
                    'password' => $data['db_password'],
                    'dbname' => $data['db_name']
                ]);

                return json_encode([
                    'code' => 0,
                    'message' => '修改成功'
                ]);
            case 'module-install':// 安装模块
                $module_id = ArrayHelper::getValue($data, 'module_id');

                $migration = new Migration();
                $migration->printType = 0;

                $res = Modules::install($module_id, $migration);
                return json_encode($res);
            case 'group':// 添加最上级部门
                $groupModel = new Group();
                $groupModel->loadDefaultValues();
                $groupModel->load([
                    'name' => $data['company_name'],
                    'pid' => 0,
                ], '');

                if (!$groupModel->save()) {
                    return json_encode([
                        'code' => 1,
                        'msg' => "部门添加失败" . $groupModel->firstErrors
                    ]);
                } else {
                    return json_encode([
                        'code' => 0,
                        'msg' => '部门添加成功',
                    ]);
                }
            case 'user':// 添加管理员账号
                $userModel = new User();
                $userModel->loadDefaultValues();
                $userModel->load([
                    'user_id' => 1,
                    'username' => $data['username'],
                    'realname' => $data['realname'],
                    'password' => $data['password'],
                    'avatar' => Yii::$app->request->hostInfo . '/static/images/avatar/default/10.jpg',
                    'role_id' => 1,
                    'is_admin' => 1,
                    'group_id' => 1,
                ], '');
                if (!$userModel->save()) {
                    return json_encode([
                        'code' => 1,
                        'msg' => "管理员账号添加失败：" . $userModel->firstErrors
                    ]);
                } else {
                    return json_encode([
                        'code' => 0,
                        'msg' => '部门添加成功',
                    ]);
                }
            case 'config':// 添加系统配置
                $systemConfig = [
                    [
                        'name' => 'COMPANY_NAME',
                        'title' => '企业名称',
                        'type' => 'string',
                        'value' => $data['company_name'],
                    ],
                    [
                        'name' => 'SYSTEM_NAME',
                        'title' => '系统名称',
                        'type' => 'string',
                        'value' => $data['system_name'],
                    ],
                    [
                        'name' => 'SYSTEM_LOGO',
                        'title' => '管理平台logo',
                        'type' => 'string',
                        'value' => $data['system_logo'],
                    ],
                    [
                        'name' => 'WEB_SERVER_HOST',
                        'title' => '前端服务器地址',
                        'type' => 'array',
                        'value' => str_replace(',', '\r\n', $data['web_server']),
                        'remark' => '只有在配置中的IP或域名才允许访问api接口，*表示所有地址均可访问',
                    ],
                    [
                        'name' => 'FILE_SERVER_HOST',
                        'title' => '文件服务器地址',
                        'type' => 'string',
                        'value' => $data['file_server'],
                        'remark' => '请输入文件服务器的完整地址加端口号',
                    ],
                ];

                foreach ($systemConfig as $value) {
                    // 如果没有这个配置项，那么设置
                    if (is_null(Yii::$app->systemConfig->getValue($value['name'], null))) {
                        Yii::$app->systemConfig->set('main', $value);
                    }
                }

                return json_encode([
                    'code' => 0,
                    'msg' => '安装成功'
                ]);
            default :
                break;
        }
    }

    /**
     * 导入用户信息
     * @return string
     */
    public function actionImportUsers()
    {
        $data = Yii::$app->request->post();

        $excel = ArrayHelper::getValue($data, 'excel');
        if (!empty($excel)) {
            if (file_exists(Yii::getAlias('@webroot') . $excel)) {
                $arr = Excel::set_file(Yii::getAlias('@webroot') . $excel);
                $execl_data = array_splice($arr[0], 1);
                $res = User::batchOperate($execl_data, 'create', 'username',
                    ['username', 'realname', 'password', 'gender', 'group_id', 'position_id', 'phone', 'qq', 'email']);
                if ($res) {
                    return json_encode([
                        'code' => 0,
                        'msg' => '导入成功',
                    ]);
                } else {
                    return json_encode([
                        'code' => 1,
                        'msg' => '导入失败！error：' . $res,
                    ]);
                }
            } else {
                return json_encode([
                    'code' => 1,
                    'msg' => '上传文件失败',
                ]);
            }
        }
    }

    /**
     * 获取所有模块信息
     * @return false|string
     */
    public function actionGetModules()
    {
        $moduleIds = Modules::getModuleFiles();
        $data = [];
        foreach ($moduleIds AS $value) {
            $path = Yii::getAlias('@system') . '/modules/' . $value . '/install/Config.php';
            if (file_exists($path)) {
                try {
                    $moduleVal = include $path;
                    if (isset($moduleVal['base']) && (empty($search) || strpos($moduleVal['base']['name'], $search) !== false)) {
                        $data[$moduleVal['base']['module_id']] = $moduleVal['base']['name'];
                    }
                } catch (yii\base\ErrorException $e) {
                    Yii::$app->getSession()->setFlash('error', '模块配置格式错误,请尽快修改');
                }
            }
        }

        $data = Tool::array_sort_by_keys($data, ['main', 'notify', 'role', 'user', 'cron', 'charts'], true);

        return json_encode([
            'code' => 0,
            'data' => $data
        ]);
    }
}