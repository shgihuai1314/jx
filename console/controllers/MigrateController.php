<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2018-3-1
 * Time: 22:27
 */

namespace console\controllers;

use \yii\console\controllers\MigrateController as MgController;
use system\modules\main\models\Migration;
use yii\helpers\FileHelper;
use yii\helpers\Console;
use yii\console\Exception;
use yii\db\Connection;
use yii\di\Instance;
use Yii;

class MigrateController extends MgController
{
    public $migrationPath = null;

    public $migrationNamespaces = ['system\\migrations'];

    public $templateFile = '@console/migrations/views/migration.php';

    public $generatorTemplateFiles = [
        'create_table' => '@console/migrations/views/createTableMigration.php',
        'drop_table' => '@console/migrations/views/dropTableMigration.php',
        'add_column' => '@console/migrations/views/addColumnMigration.php',
        'drop_column' => '@console/migrations/views/dropColumnMigration.php',
        'create_junction' => '@console/migrations/views/createTableMigration.php',
        'add_config_field_menu_cron' => '@console/migrations/views/addConfigFieldMenuMigration.php',
    ];

    /**
     * 创建Migration文件
     * @param string $name
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public function actionCreate($name)
    {
        if (!preg_match('/^[\w\\\\\:]+$/', $name)) {
            throw new Exception('The migration name should contain letters, digits, underscore and/or backslash characters only.');
        }

        if (strpos($name, ':') !== false) {
            list($module, $name) = explode(':', $name);
        } else {
            $module = $this->prompt('Please enter the module_id :');
            while (!$module) {
                $module = $this->prompt('Module_id could not be null or ""! Please enter the module_id :');
            }
        }

        $namespace = 'system\\modules\\' . $module . '\\migrations';

        $className = $class = 'M' . gmdate('ymdHis') . ucfirst($name);
        $migrationPath = str_replace('/', DIRECTORY_SEPARATOR, Yii::getAlias('@' . str_replace('\\', '/', $namespace)));

        $file = $migrationPath . DIRECTORY_SEPARATOR . $className . '.php';
        $content = $this->generateMigrationSourceCode([
            'name' => $name,
            'module' => $module,
            'className' => $className,
            'namespace' => $namespace,
        ]);
        FileHelper::createDirectory($migrationPath);
        file_put_contents($file, $content);
        $this->stdout("New migration created successfully.\n", Console::FG_GREEN);
    }

    // 升级模块
    public function actionUp($modules = null)
    {
        $migration = new Migration();
        $migration->printType = 1;
        $migration->upGrade($modules);
    }

    /**
     * @inheritdoc
     * @since 2.0.8
     */
    protected function generateMigrationSourceCode($params)
    {
        $parsedFields = $this->parseFields();
        $fields = $parsedFields['fields'];
        $foreignKeys = $parsedFields['foreignKeys'];

        $name = $params['name'];
        $templateFile = $this->templateFile;
        $table = null;
        if (preg_match('/^create_junction(?:_table_for_|_for_|_)(.+)_and_(.+)_tables?$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['create_junction'];
            $firstTable = $matches[1];
            $secondTable = $matches[2];

            $fields = array_merge(
                [
                    [
                        'property' => $firstTable . '_id',
                        'decorators' => 'integer()',
                    ],
                    [
                        'property' => $secondTable . '_id',
                        'decorators' => 'integer()',
                    ],
                ],
                $fields,
                [
                    [
                        'property' => 'PRIMARY KEY(' .
                            $firstTable . '_id, ' .
                            $secondTable . '_id)',
                    ],
                ]
            );

            $foreignKeys[$firstTable . '_id']['table'] = $firstTable;
            $foreignKeys[$secondTable . '_id']['table'] = $secondTable;
            $foreignKeys[$firstTable . '_id']['column'] = null;
            $foreignKeys[$secondTable . '_id']['column'] = null;
            $table = $firstTable . '_' . $secondTable;
        } elseif (preg_match('/^add_(.+)_columns?_to_(.+)_table?$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['add_column'];
            $table = $matches[2];
        } elseif (preg_match('/^drop_(.+)_columns?_from_(.+)_table?$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['drop_column'];
            $table = $matches[2];
        } elseif (preg_match('/^create_(.+)_table?$/', $name, $matches)) {
            $this->addDefaultPrimaryKey($fields);
            $templateFile = $this->generatorTemplateFiles['create_table'];
            $table = $matches[1];
        } elseif (preg_match('/^drop_(.+)_table?$/', $name, $matches)) {
            $this->addDefaultPrimaryKey($fields);
            $templateFile = $this->generatorTemplateFiles['drop_table'];
            $table = $matches[1];
        } elseif (preg_match('/^add(?:_config|_field|_menu|_cron)*$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['add_config_field_menu_cron'];
            $table = $matches[0];
        }

        foreach ($foreignKeys as $column => $foreignKey) {
            $relatedColumn = $foreignKey['column'];
            $relatedTable = $foreignKey['table'];
            // Since 2.0.11 if related column name is not specified,
            // we're trying to get it from table schema
            // @see https://github.com/yiisoft/yii2/issues/12748
            if ($relatedColumn === null) {
                $relatedColumn = 'id';
                try {
                    $this->db = Instance::ensure($this->db, Connection::className());
                    $relatedTableSchema = $this->db->getTableSchema($relatedTable);
                    if ($relatedTableSchema !== null) {
                        $primaryKeyCount = count($relatedTableSchema->primaryKey);
                        if ($primaryKeyCount === 1) {
                            $relatedColumn = $relatedTableSchema->primaryKey[0];
                        } elseif ($primaryKeyCount > 1) {
                            $this->stdout("Related table for field \"{$column}\" exists, but primary key is composite. Default name \"id\" will be used for related field\n", Console::FG_YELLOW);
                        } elseif ($primaryKeyCount === 0) {
                            $this->stdout("Related table for field \"{$column}\" exists, but does not have a primary key. Default name \"id\" will be used for related field.\n", Console::FG_YELLOW);
                        }
                    }
                } catch (\ReflectionException $e) {
                    $this->stdout("Cannot initialize database component to try reading referenced table schema for field \"{$column}\". Default name \"id\" will be used for related field.\n", Console::FG_YELLOW);
                }
            }
            $foreignKeys[$column] = [
                'idx' => $this->generateTableName("idx-$table-$column"),
                'fk' => $this->generateTableName("fk-$table-$column"),
                'relatedTable' => $this->generateTableName($relatedTable),
                'relatedColumn' => $relatedColumn,
            ];
        }

        return $this->renderFile(Yii::getAlias($templateFile), array_merge($params, [
            'table' => $this->generateTableName($table),
            'fields' => $fields,
            'foreignKeys' => $foreignKeys,
        ]));
    }

}