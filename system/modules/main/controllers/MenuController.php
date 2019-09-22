<?php
/**
 * Created by PhpStorm.
 * User: THINKPAD
 * Date: 2017/8/11
 * Time: 11:56
 */

namespace system\modules\main\controllers;

use Yii;
use system\modules\main\models\Menu;
use yii\helpers\ArrayHelper;

class MenuController extends BaseController
{
    public $dependIgnoreValueList = [
        'main/menu/index' => ['*']
    ];

    /**
     * 列表
     * @return string
     */
    public function actionIndex()
    {
	    $params = Yii::$app->request->queryParams;
	    $id = ArrayHelper::getValue($params, 'id', 0);
        $type = ArrayHelper::getValue($params, 'type', 0);
	
	    $list = Menu::getMenusTree($type == 0 ? ['type' => 0] : [], false, $id);
	    return $this->render('index', [
		    'list' => $list,
		    'id' => $id,
            'type' => $type,
	    ]);
    }
	
	/**
	 * 添加菜单
     * @param $pid int 父级id
	 * @return string
	 */
	public function actionAdd($pid = null)
	{
		$model = new Menu();
		$model->loadDefaultValues();
		
		if ($model->load(Yii::$app->request->post(), '')) {
			$this->getSaveRes($model);
		}
		
		$model->pid = $pid;
		return $this->render('form', [
			'model' => $model
		]);
	}
	
	/**
	 * 编辑菜单
	 * @param $id integer 菜单ID
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
			$this->getSaveRes($model);
		}
		
		return $this->render('form', [
			'model' => $model
		]);
	}
	
	/**
	 * 删除菜单
	 */
    public function actionDel()
    {
        $params = Yii::$app->request->post();

        $model = new Menu();
        return $this->ajax($model, 'del', $params);
    }
	
	/**
	 * AJAX请求处理
	 */
	public function actionAjax()
	{
		$params = ArrayHelper::merge(Yii::$app->request->post(), Yii::$app->request->queryParams);
		$action = ArrayHelper::getValue($params, 'action');
		
		if ($action == 'get-nodes')	{
			$menus = Menu::getMenusByCondition(['type' => 0]);
			$data = [];
			
			foreach ($menus as $key => $val) {
				$data[] = [
					'id' => $val['menu_id'],
					'name' => $val['menu_name'],
					'pid' => $val['pid'],
					'iconSkin' => !empty($val['icon']) ? $val['icon'] : (empty($val['children']) ? 'fa fa-circle-o' : 'iconfont icon-test'),
				];
			}
			array_unshift($data, [
				'id' => 0,
				'name' => '主菜单',
				'pid' => 0,
				'open' => true
			]);
			return $this->ajaxReturn($data);
		}
	}

    /**
     * 获取模型对象.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Menu the loaded model
     * @throws yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Menu::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}