<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2018-3-28
 * Time: 16:07
 */

/** @var $this yii\web\View */
/** @var array $data */

use \system\core\utils\Tool;

extract($data);

$tableSchema = Yii::$app->db->getTableSchema($table_name);
echo "<?php\n";
?>

namespace system\modules\<?= $module ?>\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "<?= $table_name ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
 */
class <?= $model_class ?> extends \system\models\Model
{
<?php if ($log_flag == 1) :?>
    //是否记录日志
    public $log_flag = true;
    //日志信息配置
    public $log_options = [
<?php if ($target_name != 'name') :?>
        'target_name' => '<?= $target_name ?>',//日志目标对应的字段名，默认name
<?php endif; ?>
        'model_name' => '<?= $model_name ?>',//模型名称
<?php if (isset($normal_field)) :?>
        'normal_field' => [<?= "'" . implode("', '", $normal_field) . "'" ?>],// 要记录日志的普通字段 并非所有字段都需要记录，比如更新时间、创建人不需要记录，操作日志的作用是便于管理员排错，记录必要的信息即可。
<?php endif; ?>
<?php if (isset($except_field)) :?>
        'except_field' => [<?= "'" . implode("', '", $except_field) . "'" ?>],//日志记录时要排除的字段
<?php endif; ?>
    ];
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '<?= $table_name ?>';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
<?php if (!empty($required_rule)) :?>
            [['<?= implode("', '", $required_rule)?>'], 'required'],
<?php endif; ?>
<?php if (!empty($integer_rule)) :?>
            [['<?= implode("', '", $integer_rule)?>'], 'integer'],
<?php endif; ?>
<?php if (!empty($string_rule)) :?>
            [['<?= implode("', '", $string_rule)?>'], 'string'],
<?php endif; ?>
<?php if (!empty($safe_rule)) :?>
            [['<?= implode("', '", $safe_rule)?>'], 'safe'],
<?php endif; ?>
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
<?php foreach ($label as $field => $name): ?>
            <?= "'$field' => '" . (empty($name) ? ucwords(str_replace('_', ' ', $field)) : $name) . "',\n" ?>
<?php endforeach; ?>
        ], parent::attributeLabels());
    }

<?php if (!empty($attirbute)) :?>
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
<?php
foreach ($attirbute as $field => $arr) {
    if ($arr['type'] == 0) {
        $value = Tool::paramsToArray($arr['value'][0]);
        echo "            '$field' => [\r\n";
        foreach ($value as $key => $val) {
            echo "                '$key' => '$val',\r\n";
        }
        echo "            ],\r\n";
    } elseif ($arr['type'] == 1) {
        echo "            '$field' => Yii::\$app->systemConfig->getValue('" . $arr['value'][1] . "', []),\r\n";
    } elseif ($arr['type'] == 2) {
        echo "            '$field' => " . $arr['value'][2] . ",\r\n";
    }
}
        ?>
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }
<?php endif; ?>

<?php if (isset($label['create_by']) || isset($label['create_time'])) :?>
    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
<?php if (isset($label['create_by'])) : ?>
                $this->create_by = Yii::$app->user->id;
<?php endif; ?>
<?php if (isset($label['create_time'])) : ?>
                $this->create_time = time();
<?php endif; ?>
<?php if (isset($label['create_at'])) : ?>
                $this->create_at = time();
<?php endif; ?>
            }

            return true;
        }

        return false;
    }
<?php endif; ?>
}
