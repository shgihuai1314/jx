<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2018-3-6
 * Time: 9:36
 */

namespace system\modules\main\models;

use system\core\utils\Tool;
use system\models\Model;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "migration".
 *
 * @property string $class_name
 * @property string $namespace
 * @property string $module_id
 * @property string $desc
 * @property string $version
 * @property integer $apply_time
 */
class Migration extends Model
{
    public $migrationNamespaces = [];

    // 输出类型 0:不输出；1：输出到控制台；2：输出到浏览器
    public $printType = 0;

    public $charset = null;

    const BASE_MIGRATION = 'm000000_000000_base';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'migration';
    }

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['class_name'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class_name'], 'required'],
            [['apply_time'], 'integer'],
            [['class_name', 'namespace', 'module_id', 'desc', 'version'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'class_name' => '更新文件',
            'namespace' => '命名空间',
            'module_id' => '所属模块',
            'desc' => '更新内容',
            'version' => '版本号',
            'apply_time' => '更新时间',
        ];
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
            'module_id' => Modules::getModuleMap(),
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModule()
    {
        return $this->hasOne(Modules::className(), ['module_id' => 'module_id']);
    }

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();

        if (Yii::$app->db->schema->getTableSchema(self::tableName(), true) === null) {
            $this->createMigrationHistoryTable();
        }

        $modules = Modules::getModuleMap();
        foreach ($modules as $module_id => $name) {
            $this->migrationNamespaces[] = 'system\\modules\\' . $module_id . '\\migrations';
        }
    }

    /**
     * 数据库升级
     * @param string $module_id
     * @param array $class
     */
    public function upGrade($module_id = null, $class = null)
    {
        $migrations = $this->getNewMigrations($module_id);
        // 如果$class不为空，则只执行$class中的的文件
        if (!empty($class)) {
            foreach ($migrations as $key => $val) {
                if (!in_array($val['class'], $class)) {
                    unset($migrations[$key]);
                }
            }
        }

        if (empty($migrations)) {
            $this->printLog("没有发现新的更新。您的数据库是最新的。\n");
            return true;
        }

        $this->printLog("开始更新数据库...\n");
        $n = count($migrations);
        $this->printLog("一共有以下 $n 个更新:\n");
        foreach ($migrations as $migration) {
            $this->printLog("    >【" . $migration['module'] . "】" . $migration['desc'] . "\n");
        }
        $this->printLog("\n");

        $applied = 0;
        foreach ($migrations as $migration) {
            if (!$this->migrateUp($migration)) {
                $this->printLog("\n该条更新失败，后续更新取消！\n");
                return false;
            }
            $applied++;
        }
        $this->printLog("数据库更新成功！\n");
        return true;
    }

    /**
     * 数据库降级
     * @param null $module_id
     * @param null $classList
     * @param bool $print
     * @return bool
     */
    public function downGrade($module_id = null, $classList = null, $print = true)
    {
        $migrations = $this->getMigrationHistory($module_id, $classList);
        if (empty($migrations)) {
            $this->printLog("之前没有进行过更新操作，无法还原。\n");
            return true;
        }

        $n = count($migrations);
        $this->printLog("总共有以下 $n 个更新将被还原:\n");
        foreach ($migrations as $migration) {
            $this->printLog("    >【" . $migration['module'] . "】" . $migration['desc'] . "\n");
        }
        $this->printLog("\n");

        $reverted = 0;
        foreach ($migrations as $migration) {
            if (!$this->migrateDown($migration)) {
                $this->printLog("\n该条更新还原失败，后续操作取消！\n");

                return false;
            }
            $reverted++;
        }
        $this->printLog("\n数据库还原成功.\n");
        return true;
    }

    /**
     * 获取需要进行更新的migrate文件
     * @param $module_id
     * @return array
     */
    public function getNewMigrations($module_id = null)
    {
        $applied = [];
        foreach ($this->getMigrationHistory($module_id) as $class => $migration) {
            $applied[trim($class, '\\')] = true;
        }

        $migrationPaths = [];

        // 选择了模块则只更新该模块的相关数据库
        $migrationNamespaces = empty($module_id) ? $this->migrationNamespaces : ['system\\modules\\' . $module_id . '\\migrations'];
        foreach ($migrationNamespaces as $namespace) {
            $migrationPaths[] = [
                str_replace('/', DIRECTORY_SEPARATOR, Yii::getAlias('@' . str_replace('\\', '/', $namespace))),
                $namespace,
            ];
        }

        $migrations = [];
        foreach ($migrationPaths as $n => $item) {
            list($migrationPath, $namespace) = $item;
            if (!file_exists($migrationPath)) {
                continue;
            }
            $handle = opendir($migrationPath);
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $migrationPath . DIRECTORY_SEPARATOR . $file;
                if (preg_match('/^(m(\d{6}_?\d{6})\D.*?)\.php$/is', $file, $matches) && is_file($path)) {
                    $class = $matches[1];
                    $time = str_replace('_', '', $matches[2]);
                    if (!isset($applied[$class])) {
                        $className = $namespace . "\\" . $class;
                        $model = new $className();
                        $migrations[$n . '-' . $time . '\\' . $class] = [
                            'namespace' => $namespace,
                            'class' => $class,
                            'module' => ArrayHelper::getValue(Modules::getModuleMap(), $model->module_id,
                                $model->module_id),
                            'desc' => $model->description,
                            'version' => $model->version,
                        ];
                        unset($model);
                    }
                }
            }
            closedir($handle);
        }
        ksort($migrations);
        return array_values($migrations);
    }

    /**
     * 获取历史更新记录
     * @param null $module_id
     * @param null $classList
     * @return array
     */
    private function getMigrationHistory($module_id = null, $classList = null)
    {
        $query = self::find()->asArray();

        if (!empty($module_id)) {
            $query->andWhere(['module_id' => $module_id]);
        }

        if (!empty($classList)) {
            $query->andWhere(['class_name' => $classList]);
        }

        $rows = $query->orderBy(['apply_time' => SORT_DESC, 'class_name' => SORT_DESC])->all();
        $history = [];
        foreach ($rows as $key => $row) {
            if ($row['class_name'] === self::BASE_MIGRATION) {
                continue;
            }
            if (preg_match('/m?(\d{6}_?\d{6})(\D.*)?$/is', $row['class_name'], $matches)) {
                $time = str_replace('_', '', $matches[1]);
                $row['canonicalVersion'] = $time;
            } else {
                $row['canonicalVersion'] = $row['class_name'];
            }
            $row['apply_time'] = (int)$row['apply_time'];
            $row['module'] = ArrayHelper::getValue(Modules::getModuleMap(), $row['module_id'], $row['module_id']);
            $history[] = $row;
        }

        usort($history, function ($a, $b) {
            if ($a['apply_time'] === $b['apply_time']) {
                if (($compareResult = strcasecmp($b['canonicalVersion'], $a['canonicalVersion'])) !== 0) {
                    return $compareResult;
                }
                return strcasecmp($b['class_name'], $a['class_name']);
            }
            return ($a['apply_time'] > $b['apply_time']) ? -1 : +1;
        });

        $history = ArrayHelper::index($history, 'class_name');

        return $history;
    }

    /**
     * 升级数据库
     * @param $class
     * @return bool
     */
    public function migrateUp($migration)
    {
        $namespace = ArrayHelper::getValue($migration, 'namespace', '');
        $class = ArrayHelper::getValue($migration, 'class', '');

        if ($class === self::BASE_MIGRATION) {
            return true;
        }

        $this->printLog("*** 更新内容：【" . $migration['module'] . "】" . $migration['desc'] . " ***\n");
        $start = microtime(true);

        $className = $namespace . '\\' . $class;
        $migration = new $className();
        $migration->printType = $this->printType;
        if ($migration->up() !== false) {
            $data = [
                'class_name' => $class,
                'namespace' => $namespace,
                'module_id' => $migration->module_id,
                'desc' => $migration->description,
                'version' => $migration->version,
            ];
            $this->addMigrationHistory($data);
            $time = microtime(true) - $start;
            $this->printLog("*** 更新完成。(time: " . sprintf('%.3f', $time) . "s) ***\n\n");

            return true;
        } else {
            $time = microtime(true) - $start;
            $this->printLog("*** 更新失败！(time: " . sprintf('%.3f', $time) . "s) ***\n\n");

            return false;
        }
    }

    /**
     * 降级数据库
     * @param $class
     * @return bool
     */
    private function migrateDown($migration)
    {
        $namespace = ArrayHelper::getValue($migration, 'namespace', '');
        $class = ArrayHelper::getValue($migration, 'class_name', '');

        if ($class === self::BASE_MIGRATION) {
            return true;
        }

        $this->printLog("*** 还原更新：【" . $migration['module'] . "】" . $migration['desc'] . " ***\n");
        $start = microtime(true);

        $className = $namespace . '\\' . $class;
        if (class_exists($className)) {
            $migration = new $className();
            $migration->printType = $this->printType;
            if ($migration->down() !== false) {
                $this->removeMigrationHistory($class);

                $time = microtime(true) - $start;
                $this->printLog("*** 还原成功 (time: " . sprintf('%.3f', $time) . "s) ***\n\n");

                return true;
            } else {
                $time = microtime(true) - $start;
                $this->printLog("*** 还原失败 (time: " . sprintf('%.3f', $time) . "s) ***\n\n");

                return false;
            }
        } else {
            $this->removeMigrationHistory($class);

            $time = microtime(true) - $start;
            $this->printLog("*** 该更新文件不存在，记录已删除 (time: " . sprintf('%.3f', $time) . "s) ***\n\n");
            return true;
        }
    }

    /**
     * Creates the migration history table.
     */
    private function createMigrationHistoryTable()
    {
        Yii::$app->db->createCommand()->createTable(self::tableName(), [
            'class_name' => 'varchar(255) NOT NULL DEFAULT ""', //PRIMARY KEY
            'namespace' => 'varchar(255) NOT NULL DEFAULT ""',
            'module_id' => 'varchar(255) NOT NULL DEFAULT ""',
            'desc' => 'varchar(255) NOT NULL DEFAULT ""',
            'version' => 'varchar(255) NOT NULL DEFAULT "1.0"',
            'apply_time' => 'integer',
        ])->execute();

        // 添加唯一索引
        Yii::$app->db->createCommand()->addUnique('class_name', self::tableName(), 'class_name')->execute();

        Yii::$app->db->createCommand()->insert(self::tableName(), [
            'class_name' => self::BASE_MIGRATION,
            'apply_time' => time(),
        ])->execute();
    }

    /**
     * 添加更新记录
     * @param $data
     */
    private function addMigrationHistory($data)
    {
        $model = new self();
        $model->class_name = ArrayHelper::getValue($data, 'class_name', '');
        $model->namespace = ArrayHelper::getValue($data, 'namespace', '');
        $model->module_id = ArrayHelper::getValue($data, 'module_id', '');
        $model->desc = ArrayHelper::getValue($data, 'desc', '');
        $model->version = ArrayHelper::getValue($data, 'version', '1.0');
        $model->apply_time = time();
        if ($model->save()) {
            $module = Modules::findOne(['module_id' => $model->module_id]);
            if (!empty($module) && $module->version != $model->version) {
                $module->version = $model->version;
                $module->save();
            }
        } else {
            print_r($model->errors);
        }
    }

    /**
     * 删除更新记录
     * @param $class
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    private function removeMigrationHistory($class)
    {
        $model = self::findOne(['class_name' => $class]);
        $module_id = $model->module_id;
        $model->delete();

        $maxVersion = self::find()->where(['module_id' => $module_id])->max('version');
        $module = Modules::findOne(['module_id' => $model->module_id]);
        if (!empty($module)) {
            $module->version = empty($maxVersion) ? '1.0' : $maxVersion;
            $module->save();
        }
    }

    /**
     * 输出日志
     * @param string $content 日志内容
     */
    private function printLog($content)
    {
        if ($this->printType == 1) {// 输出到控制台
            // windows系统转成gb2312格式
            if ($this->charset == null && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $content = iconv('utf-8', 'gb2312//IGNORE', $content);
            }
            echo $content;
        } elseif ($this->printType == 2) {// 输出到浏览器
            echo str_repeat(" ",1024);
            // 把换行换成<br/>
            echo str_replace("\n", "<br/>", str_replace(' ', '&nbsp;', $content));
            // 滚动条自动滚到最下
            echo '<script>window.scrollTo(0,document.body.scrollHeight);</script>';
        }
    }
}