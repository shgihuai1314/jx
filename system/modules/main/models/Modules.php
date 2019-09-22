<?php

namespace system\modules\main\models;

use system\core\utils\Tool;
use system\modules\notify\models\NotifyNode;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tab_modules".
 *
 * @property integer $id                    模块id
 * @property string $name                   模块名称
 * @property string $module_id              模块id
 * @property string $icon                模块版本号
 * @property string $version                模块版本号
 * @property string $describe               模块描述
 * @property integer $status                模块状态：0关闭，1开启
 * @property integer $core                  核心模块，0非核心，1核心
 * @property string $author                 作者
 * @property string $create_at              创建时间
 * @property string $update_at              更新时间
 */
class Modules extends \system\models\Model
{
    public $log_flag = true;

    public $log_options = [
        'target_name' => 'module_id',//日志目标对应的字段名，默认name
        'model_name' => '模块',//模型名称
        'except_field' => ['create_at', 'update_at'],
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_modules';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['status', 'core', 'create_at', 'update_at'], 'integer'],
            [['name', 'module_id', 'icon', 'version', 'describe', 'author'], 'string', 'max' => 255],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'ID',
            'name' => '模块名称',
            'module_id' => '模块ID',
            'icon' => '模块图标',
            'version' => '版本号',
            'describe' => '功能简介',
            'status' => '状态',
            'core' => '核心模块',
            'author' => '作者',
            'create_at' => '创建时间',
            'update_at' => '更新时间',
        ], parent::attributeLabels());
    }

    /**
     * 选择性属性列表
     * @param string $field
     * @param string $key
     * @param bool $default
     * @return array|bool|string
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
        $list = [
            'status' => ['1' => '正常', '2' => '禁用'],
            'core' => ['1' => '是', '0' => '否'],
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->create_at = time();
            }

            $this->update_at = time();

            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // 刷新缓存
        self::getAllModule(true);
    }

    // migrate表是否存在
    private static $tableExist = false;

    /**
     * 获取所有模块
     * @param bool $refresh 是否刷新缓存
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public static function getAllModule($refresh = false)
    {
        $cache_key = 'main:module:all';
        $data = Yii::$app->cache->get($cache_key);
        // 判断表是否存在；在console里面初始化install的时候，因为还没有tab_modules表，会导致报错无法安装，所以先判断表是否存在
        $cache_table_key = 'main:module:table:exit';
        if (Yii::$app->cache->get($cache_table_key)) {
            self::$tableExist = true;
        } else {
            self::$tableExist = Yii::$app->db->schema->getTableSchema(self::tableName(), true) !== null;
            self::$tableExist && Yii::$app->cache->set($cache_table_key, self::$tableExist);
        }
        if (self::$tableExist && (!$data || $refresh)) {
            $data = self::find()->orderBy(['core' => SORT_DESC, 'id' => SORT_ASC])->asArray()->all();
            Yii::$app->cache->set($cache_key, $data);
        }

        return empty($data) ? [] : $data;
    }

    /**
     * 获取核心模块
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public static function getCoreModule()
    {
        $allData = self::getAllModule();
        $coreModule = Tool::get_array_by_condition($allData, ['core' => 1]);
        return $coreModule;
    }

    /**
     * 加载核心组件
     */
    public static function loadCoreComponent()
    {
        $coreModule = self::getCoreModule();
        foreach ($coreModule as $item) {
            $path = Yii::getAlias('@system') . '/modules/' . $item['module_id'] . '/install/Config.php';
            //print_r($path);die;
            //配置模型
            if (file_exists($path)) {
                try {
                    $config = require $path;
                    if (isset($config['components']) && $config['components']) {
                        Yii::$app->setComponents($config['components']);
                    }
                } catch (\Exception $e) {
                    //var_dump($path);exit;
                }
            }
        }
    }

    /**
     * 根据app获取有效模块
     * @return array
     */
    public static function getModulesByApp()
    {
        // 如果没有设置APP_NAME，那么是非法应用，不服务
        $app_name = defined('APP_NAME') ? APP_NAME : '';
        if (!$app_name) {
            return [];
        }

        // 先加载核心模块的所有组件
        self::loadCoreComponent();

        $allModule = self::getAllModule();
        $modules = [];

        foreach ($allModule as $item) {
            // 过滤关闭的模块
            if ($item['status'] == 0) {
                continue;
            }

            // 管理后台和命令行可以使用所有模块
            if ($app_name == 'admin' || Yii::$app instanceof \yii\console\Application) {
                $modules[$item['module_id']] = $item;
            }
        }

        return $modules;
    }

    /**
     * 获取模块id和名称的对应关系
     * @return array
     */
    public static function getModuleMap()
    {
        $data = self::getAllModule();
        return ArrayHelper::map($data, 'module_id', 'name');
    }

    /**
     * 安装模块
     * @param $module_id
     * @param $migration
     * @return array
     */
    public static function install($module_id, $migration)
    {
        // 获取所有的模块
        $allModules = self::getModuleFiles();

        // 要安装的模块在目录中是否存在
        if (!in_array($module_id, $allModules)) {
            return [
                'code' => 1,
                'message' => '模块不存在'
            ];
        }

        $moduleInstallPath = Yii::getAlias('@system') . '/modules/' . $module_id . '/install';
        // 必须存在配置项
        if (!is_file($moduleInstallPath . '/Config.php')) {
            return [
                'code' => 1,
                'message' => '配置不存在'
            ];
        }

        // 读取配置
        $config = require $moduleInstallPath . '/Config.php';
        if (!isset($config['base']) || empty($config['base'])) {
            return [
                'code' => 1,
                'message' => '配置文件不正确'
            ];
        }

        $baseConfig = $config['base'];
        if (!isset($baseConfig['name'], $baseConfig['module_id'])) {
            return [
                'code' => 1,
                'message' => '配置文件不正确'
            ];
        }

        try {
            // 加载模块组件
            if (isset($config['components']) && $config['components']) {
                Yii::$app->setComponents($config['components']);
            }

            // 执行数据库更新文件
            $migration->upGrade($module_id);

            if ($module_id == 'main') {
                self::$tableExist = true;
            }

        } catch (\Exception $e) {
            // 安装过程失败，执行卸载程序
            self::uninstall($module_id);

            return [
                'code' => 1,
                'message' => $e->getMessage(),
            ];
        }

        $class = 'system\modules\\' . $module_id . '\install\Handle';
        if (class_exists($class)) {
            $handel = new $class();
            if (method_exists($handel, 'install')) {
                $res = $handel->install();
                if ($res['code'] == 1) {
                    // 安装过程失败，执行卸载程序
                    self::uninstall($module_id);

                    return [
                        'code' => 1,
                        'message' => $res['msg']
                    ];
                }
            }
        }

        // 写入数据库
        $model = new self();
        // 模块数据写入数据库
        $model->setAttributes([
            'name' => isset($baseConfig['name']) ? $baseConfig['name'] : '',
            'module_id' => isset($baseConfig['module_id']) ? $baseConfig['module_id'] : '',
            'version' => isset($baseConfig['version']) ? $baseConfig['version'] : '',
            'describe' => isset($baseConfig['describe']) ? $baseConfig['describe'] : '',
            'core' => isset($baseConfig['core']) ? $baseConfig['core'] : 0,
            'author' => isset($baseConfig['author']) ? $baseConfig['author'] : '',
            'status' => 1, // 默认启用模块
        ]);

        if (!$model->save()) {
            // 安装过程失败，执行卸载程序
            self::uninstall($module_id);

            return [
                'code' => 1,
                'message' => json_encode($model->errors)
            ];
        }

        return [
            'code' => 0,
            'message' => '安装成功'
        ];
    }

    /**
     * 卸载模块
     * @param $module_id string 模块标识
     * @return array|bool
     */
    public static function uninstall($module_id, $migration = null)
    {
        // 数据库还原
        if ($migration == null) {
            $migration = new Migration();
        }
        $migration->downGrade($module_id);

        Menu::deleteMenuByModule($module_id);
        if (self::$tableExist && $model = self::findOne(['module_id' => $module_id])) {
            if (!$model->delete()) {
                return [
                    'code' => 1,
                    'message' => '卸载失败',
                ];
            }
            self::getAllModule(true);
        }


        $class = 'system\modules\\' . $module_id . '\install\Handle';
        if (class_exists($class)) {
            $handel = new $class();
            if (method_exists($handel, 'Uninstall')) {
                $handel->Uninstall();
            }
        }

        return [
            'code' => 0,
            'message' => '卸载成功'
        ];
    }

    /**
     * 遍历目录获取模型名称
     * @param null|string $module
     * @return array
     */
    public static function getModuleFiles($module = NULL)
    {
        if ($module == NULL) {
            $path = Yii::getAlias('@system') . '/modules/';
        } else {
            $path = Yii::getAlias('@system') . '/modules/' . $module;
        }

        $moduleFiles = [];
        if (is_dir($path)) {
            if ($dh = opendir($path)) {
                while (($file = readdir($dh)) !== false) {
                    if ($module) {
                        if ($file != "." && $file != "..") {
                            $moduleFiles[] = $file;
                        }
                    } else {
                        if ((is_dir($path . "/" . $file)) && $file != "." && $file != "..") {
                            $moduleFiles[] = $file;
                        }
                    }
                }
            }
        }

        return $moduleFiles;
    }

    /**
     * 获取已安装的模块
     * @return array
     */
    public static function getInstallModule()
    {
        $module = Modules::find()->select(['module_id'])->asArray()->column();

        return $module;
    }
}
