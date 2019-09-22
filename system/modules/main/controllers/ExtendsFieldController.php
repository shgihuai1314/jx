<?php
/**
 * 扩展字段控制器
 */

namespace system\modules\main\controllers;

use system\modules\main\models\ExtendsField;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii;

class ExtendsFieldController extends BaseController
{
    /**
     * 列表展示，扩展字段功能的入口
     * @return string
     */
    public function actionIndex()
    {
        // 保存完毕以后刷新对应数据表的schema缓存
        Yii::$app->getDb()->getSchema()->refresh();

        $condition = [
            'table_name',
            'search' => ['or', ['like', 'field_name', ':val'], ['like', 'field_title', ':val']],
        ];
        $data = ExtendsField::find()
            ->search($condition)
            ->orderBy(['sort' => SORT_DESC, 'id' => SORT_ASC])
            ->paginate()
            ->all();

        return $this->render('index', compact('data'));
    }

    /**
     * 添加字段
     * @return string|yii\web\Response
     */
    public function actionAdd($table_name = '')
    {
        $model = new ExtendsField();
        $model->loadDefaultValues();
        if ($model->load(Yii::$app->request->post(), '')) {
            if ($model->validate()) {
                $res = $model->addField();
                $this->getSaveRes($res['code'] == 0, null, '处理成功', $res['message']);
            } else {
                $this->flashMsg('error', Json::encode($model->errors));
            }
        }

        $model->table_name = $table_name;
        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * 修改字段
     * @param null $id
     * @return string|\yii\web\Response
     * @throws Yii\web\NotFoundHttpException
     */
    public function actionEdit($id)
    {
        if (Yii::$app->request->isAjax) {//如果是通过ajax编辑
            $params = Yii::$app->request->post();
            $model = $this->findModel($id);

            $field = ArrayHelper::getValue($params, 'field', '');
            $val = ArrayHelper::getValue($params, 'val', 0);

            if (!empty($model)) {
                $model->$field = $val;
                if ($model->updateField()) {
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
                return $this->ajaxReturn([
                    'code' => 1,
                    'msg' => '处理的对象不存在！',
                ]);
            }
        }

        $id = intval($id);
        $model = $this->findModel($id);
        if (!$model) {
            throw new yii\web\NotFoundHttpException('没有找到数据');
        }

        if ($model->load(Yii::$app->request->post(), '')) {
            if ($model->validate()) {
                // 如果为勾选is_null，那么=0
                if (is_null(Yii::$app->request->post('is_null'))) {
                    $model->is_null = 0;
                }

                $res = $model->updateField();
                $this->getSaveRes($res['code'] == 0, null, '处理成功', $res['message']);
            } else {
                $this->flashMsg('error', Json::encode($model->errors));
            }

        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * 删除字段
     * @return yii\web\Response
     * @throws yii\web\NotFoundHttpException
     */
    public function actionDel()
    {
        $params = Yii::$app->request->post();

        $id = ArrayHelper::remove($params, 'id', 0);
        $model = $this->findModel($id);

        if (!$model) {
            return $this->ajaxReturn([
                'code' => 0,
                'message' => '数据不存在',
            ]);
        }

        $res = $model->deleteField();

        if ($res['code'] == 0) {
            return $this->ajaxReturn([
                'code' => 0,
                'message' => '删除成功',
            ]);
        } else {
            return $this->ajaxReturn([
                'code' => 0,
                'message' => '删除失败 ' . $res['message'],
            ]);
        }
    }

    /**
     * 获取模型对象.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ExtendsField the loaded model
     * @throws yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ExtendsField::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}
