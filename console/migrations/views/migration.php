<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name without namespace */
/* @var $namespace string the new migration class namespace */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
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

    public function up()
    {

    }

    public function down()
    {

    }
}
