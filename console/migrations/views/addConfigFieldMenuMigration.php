<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name without namespace */
/* @var $namespace string the new migration class namespace */
/* @var $table string the new migration table name */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}

$hasConfig = strpos($table, '_config');
$hasField = strpos($table, '_field');
$hasMenu = strpos($table, '_menu');
$hasCron = strpos($table, '_cron');
?>

use console\models\Migration;

class <?= $className ?> extends Migration
{
    // 所属模块
    public $module_id = '<?= $module ?>';

    // 更新说明
    public $description = '';

    // 版本号
    public $version = '1.0';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
<?php if ($hasConfig !== false): ?>
        //配置
        'systemConfig' => [

        ],
<?php endif; ?>
<?php if ($hasField !== false): ?>
        //拓展字段
        'extendsField' => [

        ],
<?php endif; ?>
<?php if ($hasMenu !== false): ?>
        //菜单
        'menu' => [

        ],
<?php endif; ?>
<?php if ($hasCron !== false): ?>
        //计划任务
        'cron' => [
            'name' => '',// 任务名称
            'command' => '',// 执行命令
            'desc' => '',// 任务说明
        ],
<?php endif; ?>
    ];

    public function up()
    {
        parent::up();
    }

    public function down()
    {
        parent::down();
    }
}
