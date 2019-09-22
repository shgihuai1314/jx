<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2018-3-6
 * Time: 12:32
 */

namespace system\modules\main\controllers;

use Yii;
use system\modules\role\models\AuthAssign;
use system\modules\main\models\Migration;
use yii\web\ForbiddenHttpException;
use yii\helpers\ArrayHelper;

class MigrateController extends BaseController
{
    public $dependIgnoreValueList = [
        'main/migrate/index' => [
            'main/migrate/ajax',
        ]
    ];

    public function init()
    {
        parent::init();
        // 模块管理只允许超级管理员操作
        if (!AuthAssign::isSuper(Yii::$app->user->getId())) {
            throw new ForbiddenHttpException('只有超级管理员可以处理');
        }
    }

    /**
     * 数据库更新记录
     * @return string
     */
    public function actionIndex()
    {
        $data = Migration::find()->joinWith('module')
            ->where(['!=', 'class_name', Migration::BASE_MIGRATION])
            ->search(['search' => ['or', ['like', 'class_name', ':val'], ['like', 'tab_modules.name', ':val']]])
            ->orderBy(['apply_time' => SORT_DESC, 'class_name' => SORT_DESC])
            ->paginate()
            ->all();

        return $this->render('index', compact('data'));
    }

    /**
     * 数据库操作
     */
    public function actionOperate()
    {
        $params = Yii::$app->request->queryParams;
        $action = ArrayHelper::getValue($params, 'action', 'up');// up：数据库升级；down：数据库还原；
        $class = ArrayHelper::getValue($params, 'class');

        echo "<style>body {margin: 20px 40px 40px; line-height: 24px;}</style>";

        $model = new Migration();
        // 输出日志到浏览器
        $model->printType = 2;

        if ($action == 'up') {// 数据库升级
            $model->upGrade(null, explode(',', $class));
        } else {// 数据库还原
            $model->downGrade(null, explode(',', $class));
        }
        exit();
    }


    /**
     * 检查更新
     */
    public function actionCheckUpdate()
    {
        $model = new Migration();
        $list = $model->getNewMigrations();

        return $this->ajaxReturn($list);
    }
}