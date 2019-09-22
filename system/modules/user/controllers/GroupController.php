<?php

namespace system\modules\user\controllers;

use system\modules\user\models\Group;
use yii\web\NotFoundHttpException;
use yii\helpers\Json;
use Yii;

/**
 * Default controller for the `group` module
 */
class GroupController extends BaseController
{
    // 禁用csrf的action
    public $disableCsrfAction = ['update'];

    //忽略权限
    public $ignoreList = [
        'user/group/ajax'
    ];

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * AJAX 请求一下数据.
     * @return string
     */
    public function actionAjax()
    {
        return Group::getNodesByIdentity(true);
    }

    /**
     * 部门详情及修改
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {
        $model = $this->findModel($id);

        $model->log_flag = true; // 开启记录日志

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model->load($post, '');
            $model->manager = $post['manager'] ? substr($post['manager'], 1) : 0;
            $model->assistant = $post['assistant'] ? substr($post['assistant'], 1) : 0;
            $model->leader = $post['leader'] ? substr($post['leader'], 1) : 0;
            $model->sub_leader = $post['sub_leader'] ? substr($post['sub_leader'], 1) : 0;
            $res = $model->save();
            if ($res) {
                $this->flashMsg('ok', '保存成功');
            } else {
                $this->flashMsg('error', '保存失败');
            }
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * 更新部门织架构，包括新增，编辑，拖拽和删除
     * @throws \Exception
     */
    public function actionUpdate()
    {
        $data = Yii::$app->request->getRawBody();
        $data = json_decode($data, true);
        $model = null;

        if (!isset($data['type'])) {
            echo '缺少参数';
            exit;
        }
        if (!in_array($data['type'], ['add', 'edit', 'delete', 'drag', 'sort'])) {
            echo '参数错误';
            exit;
        }

        $res = false;
        $content = ''; // 日志

        //增加节点
        if ($data['type'] == 'add') {
            if (!isset($data['name'], $data['pid'])) {
                echo '缺少参数';
                exit;
            }
            $model = new Group();
            $model->loadDefaultValues();
            $model->name = $data['name'];
            $model->pid = $data['pid'];
            $res = $model->save();
            if ($res) {
                $pName = Group::getNameById($model->pid);
                $content = '新增了 部门：' . $model->id . '、' . $model->name .'; 父节点：' . $model->pid . '、' . $pName;
            }
        } //编辑节点，智能编辑名称
        else if ($data['type'] == 'edit') {
            // 根节点时pid=null
            if (!isset($data['id'], $data['name'])) {
                echo '缺少参数';
                exit;
            }
            $model = $this->findModel($data['id']);
            // 记录原始数据
            $oldName = $model->name;
            if (!$model) {
                echo '参数错误';
                exit;
            }
            if ($model->name == $data['name']) {
                echo '名称一致，无需编辑';
                exit;
            }
            $model->name = $data['name'];
            $res = $model->save();
            if ($res && $model->name != $oldName) {
                $content = '修改了 部门：' . $model->id . '、' . $oldName . '; 新名称: ' . $model->name . ';';
            }
        } //删除节点
        else if ($data['type'] == 'delete') {
            if (!isset($data['id'])) {
                echo '缺少参数';
                exit;
            }
            $model = $this->findModel($data['id']);
            if (!$model) {
                echo '参数错误';
                exit;
            }
            $res = $model->delete();
            if (!$res) {
                echo '删除失败！请检查该部门下是否有其他部门';
                exit;
            }
            $pName = Group::getNameById($model->pid);
            $content = '删除了 部门：' . $model->id . '、' . $model->name . '; 父节点：' . $model->pid . '、' . $pName;
        } //拖拽节点
        else if ($data['type'] == 'drag') {
            if (!isset($data['id'], $data['target_id'])) {
                echo '缺少参数';
                exit;
            }
            $model = $this->findModel($data['id']);
            if (!$model) {
                echo '参数错误';
                exit;
            }
            if ($model->pid == $data['target_id']) {
                echo '无需拖拽';
                exit;
            }

            $res = Group::dragGroup($data);
            if ($res) {
                $oldPName = Group::getNameById($model->pid);
                $newPName = Group::getNameById($data['target_id']);
                $content = '拖拽了 部门：' . $model->id . '、' . $model->name . '; 父部门：' . $model->pid . '、' . $oldPName  . ' => ' . $data['target_id'] . '、' . $newPName;
            }
        } // 排序
        else if ($data['type'] == 'sort') {
            // 判断参数
            if (!isset($data['id'], $data['target_id'], $data['moveType'])) {
                return false;
            }
            // 判断id和target_id 是否存在
            $data['pid'] = $this->findModel($data['id'])->pid;
            // 排序
            $res = $this->_sort($data);
            // 写日志
            if ($res) {
                if($data['moveType'] == 'prev'){
                    $content = '排序了 部门：' . $data['id'] . '、' . Group::getNameById($data['id']).' 拖拽到 ' . $data['target_id'] . '、' . Group::getNameById($data['target_id']).' 上面';
                }else{
                    $content = '排序了 部门：' . $data['id'] . '、' . Group::getNameById($data['id']).' 拖拽到 ' . $data['target_id'] . '、' . Group::getNameById($data['target_id']).' 下面';
                }
            }
        }

        if ($res) {
            Yii::$app->systemOperateLog->write([
                'module' => 'group',
                'target_name' => '部门',
                'target_id' => $model ? $model->id : $data['id'],
                'model_class' => Group::className(),
                'template' => $content,
            ]);
            echo '操作成功';
        } else {
            echo '操作失败' . Json::encode($model->errors);
        }
        exit;
    }

    /**
     * 拖拽修改排序
     * @param $data array
     * @return bool
     */
    private function _sort($data)
    {
        //获取初始化的所有数据
        $allData = Group::find()->orderBy(['sort' => SORT_DESC, 'name' => SORT_ASC])->where(['pid' => $data['pid']])->all();
        $keys = [];
        foreach ($allData as $one) {
            /** @var Group $one */
            $keys[] = $one->id;
        }
        $ids = array_flip($keys);
        unset($ids[$data['id']]);//删除拖的那个id；
        $oldId = array_flip($ids);
        $target_id[] = $data['id'];
        $new = array_values($oldId);
        //判断拖拽的目标
        if ($data['moveType'] == 'prev') {
            //拖的id；拖到指定的地方；生成一个需要的数部门
            array_splice($new, array_keys($new, $data['target_id'])[0], 0, $target_id);
        } else {
            array_splice($new, array_keys($new, $data['target_id'])[0] + 1, 0, $target_id);
        }
        $newArr = [];
        $num = count($new);
        foreach ($new as $newId) {
            $newArr[$newId] = $num;
            $num--;
        }
        //循环改变sort排序
        foreach ($allData as $one) {
            /** @var Group $one */
            $one->sort = $newArr[$one->id];
            $one->save();
        }
        return true;
    }

    /**
     * 获取模型对象.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Group the loaded model
     * @throws yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Group::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}
