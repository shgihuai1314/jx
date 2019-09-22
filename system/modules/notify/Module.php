<?php

namespace system\modules\notify;

/**
 * notify module definition class
 */
class Module extends \yii\base\Module
{
    // pc端url=>手机端url 对应的map
    public $pcMobileMap = [
        'notice/default/view' => '/notice/mobile/view',  // 查看工作
    ];

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'system\modules\notify\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
