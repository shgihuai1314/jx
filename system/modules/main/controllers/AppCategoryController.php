<?php

namespace system\modules\main\controllers;

use system\modules\main\models\AppCategory;
use system\modules\user\models\ContentPermission;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;
use Yii;

class AppCategoryController extends BaseController
{
    // 禁用csrf的action
    public $disableCsrfAction = ['upload'];

    public $dependIgnoreValueList = [
        'main/app-category/index' => ['*']
    ];

	/**
	 * @inheritdoc
	 */
	public function actions()
	{
		return [
			'upload' => [
				'class' => \system\modules\main\extend\Upload::className(),
				'dir' => 'app/category',
                'resetSize' => 100, // 重置大小
			],
		];
	}
	
	/**
     * 首页
     * @return string
     */
	public function actionIndex()
	{
		$params = Yii::$app->request->queryParams;
		$id = isset($params['id']) && !empty($params['id']) ? $params['id'] : 0;
		
		$query = AppCategory::find()->asArray();
		foreach ($params as $key => $val) {
			if (!empty($val)) {
				if ($key == 'id') {
					$query = $query->andWhere(['id' => AppCategory::getChildIds($val)]);
				} elseif ($key == 'name') {
					$query = $query->andWhere(['like', 'name', $val]);
				}
			}
		}
		
		// 允许显示的分类
		$contentPermission = ContentPermission::find()->asArray()->where(['user_id' => Yii::$app->user->id])->one();
		$arr = ArrayHelper::getValue($contentPermission, 'extend_app_category_list');
		$arr = empty($arr) ? [] : explode(',', $arr);

		if (!empty($arr)) {
			$query = $query->andWhere(['id' => AppCategory::getChildIds($arr)]);
		}
		
		//分页
		$pagination = new Pagination([
			'defaultPageSize' => Yii::$app->systemConfig->getValue('LIST_ROWS', 10),
			'totalCount' => $query->count(),
		]);
		
		$list = $query->offset($pagination->offset)
			->limit($pagination->limit)
			->orderBy(['pid' => SORT_ASC, 'sort' => SORT_DESC, 'id' => SORT_ASC])
			->all();
		
		return $this->render('index', [
			'list' => $list,
			'pagination' => $pagination,
			'params' => $params,
			'id' => $id,
		]);
	}
	
	/**
	 * 添加应用分类
	 * @return string
	 */
	public function actionAdd()
	{
		$model = new AppCategory();
		$model->loadDefaultValues();

		if ($model->load(Yii::$app->request->post(), '')) {
			$this->getSaveRes($model);
		}
		
		return $this->render('form', [
			'model' => $model,
		]);
	}
	
	/**
	 * 编辑应用分类
	 * @param $id integer 文章分类ID
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
			'model' => $model,
		]);
	}
	
	/**
	 * 删除应用分类
	 */
	public function actionDel()
	{
		$params = Yii::$app->request->post();

        $model = new AppCategory();
        return $this->ajax($model, 'del', $params);
	}
	
	/**
	 * AJAX请求处理
	 */
	public function actionAjax()
	{
		$params = ArrayHelper::merge(Yii::$app->request->post(), Yii::$app->request->queryParams);
		$action = ArrayHelper::getValue($params, 'action');
		
		if ($action == 'get-nodes') {
			$list = AppCategory::find()->asArray()
				->orderBy(['sort' => SORT_DESC, 'id' => SORT_ASC])
				->all();
			
			$contentPermission = ContentPermission::find()->asArray()->where(['user_id' => Yii::$app->user->id])->one();
			$arr = ArrayHelper::getValue($contentPermission, 'extend_App_category_list');
			$arr = empty($arr) ? [] : explode(',', $arr);
			$data = [];
			foreach ($list as $key => $val) {
				if (empty($arr) || in_array($val['id'], AppCategory::getChildIds($arr))) {
					$data[] = [
						'id' => $val['id'],
						'name' => $val['name'],
						'pid' => $val['pid'],
					];
				}
			}
			array_unshift($data, [
				'id' => 0,
				'name' => '全部分类',
				'pid' => 0,
                'open' => true,
			]);
			
			return $this->ajaxReturn($data);
			
		}
	}

    /**
     * 获取模型对象.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AppCategory the loaded model
     * @throws yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AppCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}
