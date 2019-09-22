<?php
/**
 * 模块管理
 * Created by PhpStorm.
 * User: THINKPAD
 * Date: 2017/8/17
 * Time: 15:17
 */

namespace system\modules\main\controllers;

use system\core\utils\Tool;
use system\modules\main\models\Menu;
use system\modules\main\models\Migration;
use system\modules\main\models\Modules;
use system\modules\role\models\AuthAssign;
use system\modules\main\extend\SaveUpload;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use yii;

class ModulesController extends BaseController
{
    // 禁用csrf的action
    public $disableCsrfAction = ['upload'];

    public function init()
    {
        parent::init();
        // 模块管理只允许超级管理员操作
        if (!AuthAssign::isSuper(Yii::$app->user->getId())) {
            throw new yii\web\ForbiddenHttpException('只有超级管理员可以处理');
        }
    }

    /**
     * 已安装模块
     * @return string
     */
    public function actionIndex()
    {
        //请求的参数
        $list = Modules::find()
            ->search(['search' => ['or', ['like', 'name', ':val'], ['like', 'module_id', ':val']]])
            ->orderBy(['core' => SORT_DESC])
            ->asArray()
            ->all();

        return $this->render('index', compact('list'));
    }

    /**
     * 未安装的模块
     * @return string
     */
    public function actionNotInstall()
    {
        $params = Yii::$app->request->queryParams;
        $search = ArrayHelper::getValue($params, 'search');

        $module_id = Modules::getModuleFiles();
        //获取未安装模块
        $module_id2 = Modules::find()
            ->select(['module_id'])
            ->asArray()
            ->column();
        $moduleIds = array_diff($module_id, $module_id2);
        $data = [];
        foreach ($moduleIds AS $value) {
            $path = Yii::getAlias('@system') . '/modules/' . $value . '/install/Config.php';
            if (file_exists($path)) {
                try {
                    $moduleVal = include $path;
                    if (isset($moduleVal['base']) && (empty($search) || strpos($moduleVal['base']['name'], $search) !== false)) {
                        $data[] = $moduleVal['base'];
                    }
                } catch (yii\base\ErrorException $e) {
                    Yii::$app->getSession()->setFlash('error', '模块配置格式错误,请尽快修改');
                }
            }
        }

        return $this->render('not-install', [
            'data' => $data,
        ]);
    }

    /**
     * 编辑模块，包括ajax形式，关闭开启
     * @return yii\web\Response|string
     * @throws NotFoundHttpException
     */
    public function actionEdit()
    {
        $params = Yii::$app->request->post();
        $id = ArrayHelper::remove($params, 'id', 0);
        $model = $this->findModel($id);

        $field = ArrayHelper::getValue($params, 'field');
        if ($field == 'status') {
            $model->$field = (int)ArrayHelper::getValue($params, 'val'); // 更改状态；0正常，非0不正常
            if ($model->save()) {
                // 更改菜单的状态
                Menu::setModuleStatus($model->module_id, $model->status);

                return $this->ajaxReturn([
                    'code' => 0,
                ]);
            } else {
                return $this->ajaxReturn([
                    'code' => 1,
                    'msg' => $model->errors,
                ]);
            }
        } else {
            return $this->ajax($model,'edit', $params);
        }
    }

    /**
     * 安装应用
     * @param $module_id
     * @return yii\web\Response
     */
    public function actionInstall($modules)
    {
        set_time_limit(0);
        echo "<style>body {margin: 20px 40px 40px; line-height: 24px;}</style>";

        $migration = new Migration();
        $migration->printType = 2;

        $modules = explode(',', $modules);
        foreach ($modules as $module_id) {
            echo "开始安装 $module_id 模块：<br/>";
            $res = Modules::install($module_id, $migration);
            if ($res['code'] == 0) {
                echo "<br/>$module_id 模块安装成功！<br/>";
            } else {
                echo "<br/><span style='font-weight: bold; color: red'>$module_id 模块安装失败!!! error：" . $res['message'] . "</span><br/>";
            }
            echo '<script>window.scrollTo(0,document.body.scrollHeight);</script>';
        }
        exit();
    }

    /**
     * 卸载
     * @param $id
     * @return yii\web\Response
     */
    public function actionUninstall($id)
    {
        echo "<style>body {margin: 20px 40px 40px; line-height: 24px;}</style>";

        $model = $this->findModel($id);
        if (empty($model)) {
            echo "模块不存在！<br/>";
        }

        if ($model->core == 1) {
            echo "核心模块无法卸载！<br/>";
        }

        $migration = new Migration();
        $migration->printType = 2;// 不输出内容
        echo "开始卸载模块 " . $model->module_id . " ：<br/>";
        $res = Modules::uninstall($model->module_id, $migration);
        if ($res['code'] == 0) {
            echo "模块卸载成功！<br/>";
        } else {
            echo '模块卸载失败!!!' . ArrayHelper::getValue($res, 'message', '') . "<br/>";
        }
        echo '<script>window.scrollTo(0,document.body.scrollHeight);</script>';
        exit();
    }

    /**
     * 获取模块的更新记录
     * @param $module_id
     */
    public function actionGetRecord($module_id)
    {
        $record = Migration::find()->asArray()
            ->where(['module_id' => $module_id])
            ->orderBy(['apply_time' => SORT_ASC])
            ->all();

        $record = Tool::array_to_multiple_by_index($record, 'version');
        krsort($record);

        return $this->ajaxReturn($record);
    }

    /**
     * 上传图标
     * @param $id
     * @return string
     */
    public function actionUpload($id)
    {
        $file = UploadedFile::getInstanceByName('icon');

        if (is_object($file)) {
            if (SaveUpload::saveFile($file, ['dir' => 'modules/icon', 'resetSize' => 100])) {
                $model = self::findModel($id);
                $model->icon = @iconv('gb2312', 'utf-8//IGNORE', SaveUpload::$relativePath);
                if ($model->save()) {
                    return $this->ajaxReturn([
                        'code' => 0,
                        'data' => $model->icon,
                    ]);
                }
            }
        }

        return $this->ajaxReturn([
            'code' => 1,
        ]);
    }

    /**
     * 获取模型
     * @param $id
     * @return object
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Modules::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('模块不存在');
        }
    }
}