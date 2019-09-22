<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2018-3-12
 * Time: 12:36
 */

namespace console\models;

use system\modules\main\models\ExtendsField;
use system\modules\cron\models\CronTasks;
use system\modules\main\models\Menu;
use yii\db\ColumnSchemaBuilder;
use yii\helpers\ArrayHelper;
use Yii;

class Migration extends \yii\db\Migration
{
    /**
     * 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
     * @var array
     */
    public $config = [];

    /**
     * 版本号
     * @var string
     */
    public $version = '1.0';

    // 输出类型 0:不输出；1：输出到控制台；2：输出到浏览器
    public $printType = 0;

    public function up()
    {
        // 增加配置项
        if (isset($this->config['systemConfig']) && $this->config['systemConfig']) {
            foreach ($this->config['systemConfig'] as $value) {
                // 如果没有这个配置项，那么设置
                if (is_null(Yii::$app->systemConfig->getValue($value['name'], null))) {
                    Yii::$app->systemConfig->set($this->module_id, $value);
                }
            }
        }

        //添加拓展字段
        if (isset($this->config['extendsField']) && $this->config['extendsField']) {
            foreach ($this->config['extendsField'] as $val) {
                $model = new ExtendsField();
                $model->loadDefaultValues();
                $model->setAttributes($val);
                $model->addField();
            }
        }

        //安装成功配置菜单
        if (isset($this->config['menu'])) {
            Menu::installMenuByModule($this->module_id, $this->config['menu']);
        }
        
        if (isset($this->config['cron'])) {
            if (count($this->config['cron']) == count($this->config['cron'], 1)) {
                $this->config['cron']['module_id'] = $this->module_id;
                CronTasks::setTask($this->config['cron']);
            } else {
                foreach ($this->config['cron'] as $value) {
                    $value['module_id'] = $this->module_id;
                    CronTasks::setTask($value);
                }
            }
           
        }
    }

    public function down()
    {
        // 删除扩展字段
        if (isset($this->config['extendsField']) && $this->config['extendsField']) {
            foreach ($this->config['extendsField'] as $val) {
                ExtendsField::deleteFieldByField($val['table_name'], $val['field_name']);
            }
        }

        // 删除配置项
        if (isset($this->config['systemConfig']) && $this->config['systemConfig']) {
            foreach ($this->config['systemConfig'] as $value) {
                Yii::$app->systemConfig->remove($value);
            }
        }

        //安装成功配置菜单
        if (isset($this->config['menu'])) {
            //删除模块的菜单
            Menu::removeMenu($this->config['menu']);
        }

        if (isset($this->config['cron'])) {
            if (count($this->config['cron']) == count($this->config['cron'], 1)) {
                $this->config['cron']['module_id'] = $this->module_id;
                CronTasks::deleteAll(['name' => $this->config['cron']['name']]);
            } else {
                foreach ($this->config['cron'] as $value) {
                    $value['module_id'] = $this->module_id;
                    CronTasks::deleteAll(['name' =>$value['name']]);
                }
            }
        }
    }

    /**
     * 插入一条数据
     * @param string $table 表名
     * @param array $columns 插入的数据['字段' => '值', ……]
     * @throws \yii\db\Exception
     */
    public function insert($table, $columns)
    {
        $this->printLog("    > insert into $table ...");
        if ($this->checkExist($table)) {
            $time = microtime(true);
            $this->db->createCommand()->insert($table, $columns)->execute();
            $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
        } else {
            $this->printLog(" table $table is not exist...\n");
        }
    }

    /**
     * 批量插入数据
     * @param string $table 表名
     * @param array $columns 插入的字段数组 ['id', 'name', 'title'……]
     * @param array $rows 二维数组，插入的数据，和columns中的字段对应[[1, 'yudear', '测试'],……]
     * @throws \yii\db\Exception
     */
    public function batchInsert($table, $columns, $rows)
    {
        $this->printLog("    > insert into $table ...");
        if ($this->checkExist($table)) {
            $time = microtime(true);
            $this->db->createCommand()->batchInsert($table, $columns, $rows)->execute();
            $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
        } else {
            $this->printLog(" table $table is not exist...\n");
        }
    }

    /**
     * 更新一条数据
     * @param string $table 表名
     * @param array $columns 更新的数据['字段' => '值', ……]
     * @param string $condition 更新条件
     * @param array $params 条件中的参数
     * @throws \yii\db\Exception
     */
    public function update($table, $columns, $condition = '', $params = [])
    {
        $this->printLog("    > update $table ...");
        if ($this->checkExist($table)) {
            $time = microtime(true);
            $this->db->createCommand()->update($table, $columns, $condition, $params)->execute();
            $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
        } else {
            $this->printLog(" table $table is not exist...\n");
        }
    }

    /**
     * 删除数据
     * @param string $table 表名
     * @param string $condition 删除条件
     * @param array $params 条件中的参数
     * @throws \yii\db\Exception
     */
    public function delete($table, $condition = '', $params = [])
    {
        $this->printLog("    > delete from $table ...");
        if ($this->checkExist($table)) {
            $time = microtime(true);
            $this->db->createCommand()->delete($table, $condition, $params)->execute();
            $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
        } else {
            $this->printLog(" table $table is not exist...\n");
        }
    }

    /**
     * 创建表（如果表存在则跳过）
     * @param string $table
     * @param array $columns
     * @param null $options
     * @throws \yii\db\Exception
     */
    public function createTable($table, $columns, $options = null)
    {
        $this->printLog("    > create table $table ...");
        if (!$this->checkExist($table)) {
            $time = microtime(true);
            $this->db->createCommand()->createTable($table, $columns, $options)->execute();
            foreach ($columns as $column => $type) {
                if ($type instanceof ColumnSchemaBuilder && $type->comment !== null) {
                    $this->db->createCommand()->addCommentOnColumn($table, $column, $type->comment)->execute();
                }
            }
            $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
        } else {
            $this->printLog(" table $table is exist...\n");
        }
    }

    /**
     * 给指定表重命名
     * @param string $table
     * @param string $newName
     * @throws \yii\db\Exception
     */
    public function renameTable($table, $newName)
    {
        $this->printLog("    > rename table $table to $newName ...");
        if ($this->checkExist($table)) {
            if ($this->checkExist($newName)) {
                $this->printLog("        table $newName is exist...\n");
            } else {
                $time = microtime(true);
                $this->db->createCommand()->renameTable($table, $newName)->execute();
                $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
            }
        } else {
            $this->printLog("        table $table is not exist...\n");
        }
    }

    /**
     * Builds and executes a SQL statement for dropping a DB table.
     * @param string $table the table to be dropped. The name will be properly quoted by the method.
     */
    public function dropTable($table)
    {
        $this->printLog("    > drop table $table ...");
        if ($this->checkExist($table)) {
            $time = microtime(true);
            $this->db->createCommand()->dropTable($table)->execute();
            $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
        } else {
            $this->printLog("        table $table is not exist...\n");
        }
    }

    /**
     * 清空表
     * @param string $table
     * @throws \yii\db\Exception
     */
    public function truncateTable($table)
    {
        $this->printLog("    > truncate table $table ...");
        if ($this->checkExist($table)) {
            $time = microtime(true);
            $this->db->createCommand()->truncateTable($table)->execute();
            $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
        } else {
            $this->printLog("        table $table is not exist...\n");
        }
    }

    /**
     * 添加字段
     * @param string $table
     * @param string $column
     * @param string $type
     * @throws \yii\db\Exception
     */
    public function addColumn($table, $column, $type)
    {
        $this->printLog("    > add column $column $type to table $table ...");
        if ($this->checkExist($table)) {
            if (!$this->checkExist($table, $column)) {
                $time = microtime(true);
                $this->db->createCommand()->addColumn($table, $column, $type)->execute();
                if ($type instanceof ColumnSchemaBuilder && $type->comment !== null) {
                    $this->db->createCommand()->addCommentOnColumn($table, $column, $type->comment)->execute();
                }
                $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
            } else {
                // 字段已存在
                $this->printLog("      column $column is exist...\n");
            }
        } else {
            // 表不存在
            $this->printLog("        table $table is not exist...\n");
        }
    }

    /**
     * 删除字段
     * @param string $table
     * @param string $column
     * @throws \yii\db\Exception
     */
    public function dropColumn($table, $column)
    {
        $this->printLog("    > drop column $column from table $table ...");
        if ($this->checkExist($table, $column)) {
            $time = microtime(true);
            $this->db->createCommand()->dropColumn($table, $column)->execute();
            $this->printLog('      done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
        } else {
            $this->printLog("      column $column is not exist...\n");
        }
    }

    /**
     * 给字段重命名
     * @param string $table
     * @param string $name
     * @param string $newName
     * @throws \yii\db\Exception
     */
    public function renameColumn($table, $name, $newName)
    {
        $this->printLog("    > rename column $name in table $table to $newName ...");
        if ($this->checkExist($table)) {
            if ($this->checkExist($table, $name)) {
                $time = microtime(true);
                $this->db->createCommand()->renameColumn($table, $name, $newName)->execute();
                $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
            } else {
                $this->printLog("        table $name is not exist...\n");
            }
        } else {
            $this->printLog("        table $table is not exist...\n");
        }
    }

    /**
     * 修改字段
     * @param string $table
     * @param string $column
     * @param string $type
     * @throws \yii\db\Exception
     */
    public function alterColumn($table, $column, $type)
    {
        $this->printLog("    > alter column $column in table $table to $type ...");
        if ($this->checkExist($table)) {
            if ($this->checkExist($table, $column)) {
                $time = microtime(true);
                $this->db->createCommand()->alterColumn($table, $column, $type)->execute();
                if ($type instanceof ColumnSchemaBuilder && $type->comment !== null) {
                    $this->db->createCommand()->addCommentOnColumn($table, $column, $type->comment)->execute();
                }
                $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
            } else {
                $this->printLog("      column $column is not exist...\n");
            }
        } else {
            $this->printLog("        table $table is not exist...\n");
        }
    }

    /**
     * Builds and executes a SQL statement for creating a primary key.
     * The method will properly quote the table and column names.
     * @param string $name the name of the primary key constraint.
     * @param string $table the table that the primary key constraint will be added to.
     * @param string|array $columns comma separated string or array of columns that the primary key will consist of.
     */
    public function addPrimaryKey($name, $table, $columns)
    {
        $this->printLog("    > add primary key $name on $table (" . (is_array($columns) ? implode(',', $columns) : $columns) . ') ...');
        if ($this->checkExist($table)) {
            if ($this->checkIndexExist($table, 'PRIMARY')) {
                $this->printLog(" table $table index $name is exist...\n");
            } else {
                $time = microtime(true);
                $this->db->createCommand()->addPrimaryKey($name, $table, $columns)->execute();
                $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
            }
        } else {
            $this->printLog(" table $table is not exist...\n");
        }
    }

    /**
     * Builds and executes a SQL statement for dropping a primary key.
     * @param string $name the name of the primary key constraint to be removed.
     * @param string $table the table that the primary key constraint will be removed from.
     */
    public function dropPrimaryKey($name, $table)
    {
        $this->printLog("    > drop primary key $name ...");
        if ($this->checkExist($table)) {
            if ($this->checkIndexExist($table, 'PRIMARY')) {
                $time = microtime(true);
                $this->db->createCommand()->dropPrimaryKey($name, $table)->execute();
                $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
            } else {
                $this->printLog(" table $table index $name is not exist...\n");
            }
        } else {
            $this->printLog(" table $table is not exist...\n");
        }
    }

    /**
     * Builds a SQL statement for adding a foreign key constraint to an existing table.
     * The method will properly quote the table and column names.
     * @param string $name the name of the foreign key constraint.
     * @param string $table the table that the foreign key constraint will be added to.
     * @param string|array $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas or use an array.
     * @param string $refTable the table that the foreign key references to.
     * @param string|array $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas or use an array.
     * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        $this->printLog("    > add foreign key $name: $table (" . implode(',', (array) $columns) . ") references $refTable (" . implode(',', (array) $refColumns) . ") ...");
        if ($this->checkExist($table)) {
            $time = microtime(true);
            $this->db->createCommand()->addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update)->execute();
            $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
        } else {
            $this->printLog(" table $table is not exist...\n");
        }
    }

    /**
     * Builds a SQL statement for dropping a foreign key constraint.
     * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
     * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
     */
    public function dropForeignKey($name, $table)
    {
        $this->printLog("    > drop foreign key $name from table $table ...");
        if ($this->checkExist($table)) {
            $time = microtime(true);
            $this->db->createCommand()->dropForeignKey($name, $table)->execute();
            $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
        } else {
            $this->printLog(" table $table is not exist...\n");
        }
    }

    /**
     * Builds and executes a SQL statement for creating a new index.
     * @param string $name the name of the index. The name will be properly quoted by the method.
     * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
     * @param string|array $columns the column(s) that should be included in the index. If there are multiple columns, please separate them
     * by commas or use an array. Each column name will be properly quoted by the method. Quoting will be skipped for column names that
     * include a left parenthesis "(".
     * @param bool $unique whether to add UNIQUE constraint on the created index.
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
        $this->printLog('    > create' . ($unique ? ' unique' : '') . " index $name on $table (" . implode(',', (array) $columns) . ') ...');
        if ($this->checkExist($table)) {
            if ($this->checkIndexExist($table, $name)) {
                $this->printLog(" table $table index $name is exist...\n");
            } else {
                $time = microtime(true);
                $this->db->createCommand()->createIndex($name, $table, $columns, $unique)->execute();
                $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
            }
        } else {
            $this->printLog(" table $table is not exist...\n");
        }
    }

    /**
     * Builds and executes a SQL statement for dropping an index.
     * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
     * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
     */
    public function dropIndex($name, $table)
    {
        $this->printLog("    > drop index $name on $table ...");
        if ($this->checkExist($table)) {
            if ($this->checkIndexExist($table, $name)) {
                $time = microtime(true);
                $this->db->createCommand()->dropIndex($name, $table)->execute();
                $this->printLog('        done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n");
            } else {
                $this->printLog(" table $table index $name is not exist...\n");
            }
        } else {
            $this->printLog(" table $table is not exist...\n");
        }
    }

    /**
     * 检查表或字段是否存在
     * @param string $table 表名
     * @param string $field 字段名
     */
    private function checkExist($table, $field = null)
    {
        // 字段为空则检查表是否存在
        if ($field == null) {
            return $this->db->getTableSchema($table, true) != null;
        } else {//检查字段是否存在
            $table = $this->db->getTableSchema($table, true);
            return empty($table) || in_array($field, $table->columnNames);
        }
    }

    /**
     * 检查表的索引名称是否存在
     * @param string $table 表名
     * @param string $name 索引名称
     * @return bool
     * @throws \yii\db\Exception
     */
    private function checkIndexExist($table, $name)
    {
        $result = $this->db->createCommand("SHOW index FROM `$table`")->queryAll();
        return in_array($name, ArrayHelper::getColumn($result, 'Key_name'));
    }

    /**
     * 输出日志
     * @param string $content 日志内容
     */
    protected function printLog($content)
    {
        if ($this->printType == 1) {// 输出到控制台
            // windows系统转成gb2312格式
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
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