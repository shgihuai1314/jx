<?php

echo "<?php\n";
?>

$module_id = '<?= $module_id ?>';
return [
    //基本属性
    'base'=>[
        'module_id' => $module_id,
        'name' => '', //模块名称
        'describe' => '',//模块描述
        'version' => '1.0',
        'core' => 0,
        'author' => '雨滴科技',
    ],
    //模块
    'modules' => [
        $module_id => [
            'class' => 'system\modules\\' . $module_id . '\Module',
        ],
    ],
];
