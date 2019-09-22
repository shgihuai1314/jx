<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/8/19
 * Time: 13:49
 */

namespace system\modules\main\controllers;

use system\modules\main\models\Fileinfo;
use system\modules\main\models\FileTemplate;
use system\modules\user\components\UserWithGroup;
use yii\helpers\ArrayHelper;
use Yii;

class FileInfoController extends BaseController
{
    // 禁用csrf的action
    public $disableCsrfAction = ['upload'];
    
    public $dependIgnoreList = [
        'main/file-info/upload' => [
            'main/file-info/add',
            'main/file-info/edit',
        ],
    ];
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'upload' => [
                'class' => \system\modules\main\extend\Upload::className(),
                'dir' => 'main/file-info/template'
            ],
        ];
    }
    
    /**
     * 附件列表
     * @return string
     */
	public function actionIndex()
	{
		$list = Fileinfo::find()->where(['is_del' => 0])
            ->search([
                'search' => ['or', ['like', 'name', ':val'], ['like', 'source', ':val']],
                'upload_time' => 'date_range',
                'upload_user' => function ($val) {
                return ['upload_user' => UserWithGroup::getIdsBySelect($val)];
            }])->paginate()
			->orderBy(['upload_time' => SORT_DESC])
			->all();
		
		return $this->render('index', [
			'list' => $list,
		]);
	}

}