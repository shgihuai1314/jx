<?php

namespace system\modules\role;

use system\modules\role\models\AuthAssign;

/**
 * role module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'system\modules\role\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // 监听用户删除事件
        //self::on('USER:DELETE', [AuthAssign::className(), 'deleteUser']);
    }
}
