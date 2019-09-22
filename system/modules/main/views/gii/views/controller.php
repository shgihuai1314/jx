<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2018-3-28
 * Time: 16:07
 */

/** @var yii\web\View $this */
/** @var array $data */

extract($data);

$modelClass = "system\\modules\\$module\\models\\$model_class";
echo "<?php\n";
?>

namespace system\modules\<?= $module ?>\controllers;

use <?= $modelClass ?>;
use yii\helpers\ArrayHelper;
use Yii;

class <?= ucfirst($controller_name) ?>Controller extends BaseController
{
    public $disableCsrfAction = ['upload', 'editor-upload'];
    public $dependIgnoreValueList = [
        '<?= $module ?>/<?= $controller_name ?>/index' => ['*']
    ];

    /**
     * @return array
     */
    public function actions()
    {
        return [
            // 富文本编辑器
            'editor-upload' => [
                'class' => \xj\ueditor\actions\Upload::className(),
                'pathFormat' => [
                    'imagePathFormat' => '<?= $module ?>/image/{yyyy}/{mm}/{dd}/{time}{rand:6}',
                    'videoPathFormat' => '<?= $module ?>/video/{yyyy}/{mm}/{dd}/{time}{rand:6}',
                    'filePathFormat' => '<?= $module ?>/file/{yyyy}/{mm}/{dd}/{time}{rand:6}',
                ],
            ],
            //文件上传
            'upload' => [
                'class' => \system\modules\main\extend\Upload::className(),
                //upload/目录下的文件夹，需要指定模块名称
                'dir' => '<?= $module ?>/' . date('Y') . '/' . date('m') . '/' . date('d'),
            ],

        ];
    }

<?php if (in_array('index', $controller_action)): ?>
    /**
     * 列表
     */
    public function actionIndex()
    {
        $data = <?= $model_class ?>::find()
            ->search([
<?php if (isset($string_rule)): ?>
                'search' => ['or'<?php foreach ($string_rule as $field): ?>, ['like', '<?= $field ?>', ':val']<?php endforeach;?>]
<?php endif; ?>
            ])->paginate()
            ->orderBy(['<?= $primaryKey ?>' => SORT_DESC])
            ->all();

        return $this->render('index', compact('data'));
    }
<?php endif; ?>

<?php if (in_array('form', $controller_action)) : ?>
    /**
     * 添加
     * @return mixed|string
     */
    public function actionAdd()
    {
        $model = new <?= $model_class ?>();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post(), '')) {
            // TODO
            $this->getSaveRes($model);
        }

        return $this->render('form', compact('model'));
    }

    /**
     * 编辑
     * @param $id integer
     * @return string
     */
    public function actionEdit($id)
    {
        if (Yii::$app->request->isAjax) {//如果是通过ajax编辑
            $params = Yii::$app->request->post();
            $model = $this->findModel($id);
            return $this->ajax($model,'edit', $params);
        }

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post(), '')) {
            // TODO
            $this->getSaveRes($model);
        }

        return $this->render('form', compact('model'));
    }
<?php endif; ?>

<?php if (in_array('del', $controller_action)) : ?>
    /**
     * 删除
     */
    public function actionDel()
    {
        $params = Yii::$app->request->post();

        $model = new <?= $model_class ?>();
        return $this->ajax($model, 'del', $params);
    }
<?php endif; ?>

    /**
     * 获取模型对象.
     * Finds the ExamPaper model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return <?= $model_class ?> the loaded model
     * @throws yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = <?= $model_class ?>::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}

