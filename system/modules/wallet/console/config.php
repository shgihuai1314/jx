<?php
/**
 * 模块中需要用到命令时需要生成此文件，最简单的方式是合并原来的配置文件，然后写新的配置文件，或者替换原来的配置
 * 用法：yii wallet/settle --appconfig=@system/modules/wallet/console/config.php
 * User: gang lee
 * Date: 2015/8/7
 * Time: 19:23
 */
return yii\helpers\ArrayHelper::merge(
    require(Yii::getAlias('@console') . '/config/console.php'), [
        //如果命令比较多，那么可以配置一个全局命名空间，如下所示：
        //'controllerNamespace' => 'console\controllers',
        //如果命令比较少，那么可以直接在controllerMap中配置一个即可，如下所示：
        'controllerMap' => [
            'wallet' => [
                'class' => 'system\modules\wallet\console\OrderSettleController',
            ],
        ]
        //注意：以上两种配置效果一样。但是controllerMap中的配置 优先级较高。
    ]
);