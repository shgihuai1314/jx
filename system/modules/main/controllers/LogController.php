<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/9
 * Time: 下午1:45
 */

namespace system\modules\main\controllers;

use system\modules\main\models\LogError;
use Yii;
use system\modules\main\models\OperateLog;
use system\modules\main\models\Log;

class LogController extends BaseController
{
    /**
     * 操作日志
     * @return string
     */
    public function actionOperate()
    {
	    $params = Yii::$app->request->queryParams;
	    
	    $query = OperateLog::find()->joinWith('operatorInfo');
	    
	    foreach ($params as $key => $val) {
		    if (!empty($val)) {//值为空,直接跳过
			    if ($key == 'search') {//日志内容、操作人、操作IP
				    $query = $query->andWhere(['or', ['like', 'realname', $val], ['like', 'opt_ip', $val], ['like', 'content', $val]]);
			    } elseif ($key == 'type') {//操作类型、操作模块
				    $query = $query->andWhere(['action_type' => $val]);
			    } elseif ($key == 'module') {//操作类型、操作模块
				    $query = $query->andWhere(['module' => $val]);
			    } elseif ($key == 'opt_time') {//操作时间
				    list($start, $end) = explode(' - ', $val);
				    $query = $query->andWhere(['between', 'opt_time', strtotime($start . ' 00:00:00'), strtotime($end . ' 23:59:59')]);
			    }
		    }
	    }
	
	    //分页
        $pagination = new \yii\data\Pagination([
            'defaultPageSize' => \Yii::$app->systemConfig->getValue('LIST_ROWS', 20),
            'totalCount' => $query->count(),
        ]);

	    $data = $query->offset($pagination->offset)
		    ->limit($pagination->limit)
		    ->orderBy(['opt_time' => SORT_DESC])
		    ->all();

        return $this->render('operate', [
            'logs' => $data,
            'params' => $params,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 除了操作日志和错误日志的一些其他日志，比如登录日志等，格式比较简单，就是一些操作的内容
     * @return string
     */
    public function actionIndex()
    {
        $keyword = \Yii::$app->request->get('keyword'); // 搜索关键字
        $type = \Yii::$app->request->get('type'); // 分组

        $query = Log::find();

        // 分组
        if ($type) {
            $query->andWhere(['type' => $type]);
        }

        // 搜索关键字
        if (trim($keyword)) {
            $query->andWhere(['or', ['like', 'content', $keyword], ['like', 'ip', $keyword], ['like', 'user_id', $keyword]]);
        }

        //分页
        $pagination = new \yii\data\Pagination([
            'defaultPageSize' => \Yii::$app->systemConfig->getValue('LIST_ROWS', 20),
            'totalCount' => $query->count(),
        ]);

        $data = $query
            ->with('user')
            ->asArray()
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy(['log_id' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'logs' => $data,
            'pagination' => $pagination,
        ]);
    }

    // 错误日志
    public function actionError()
    {
        $keyword = \Yii::$app->request->get('keyword'); // 搜索关键字
        $type = \Yii::$app->request->get('type'); // 分组

        $query = LogError::find();

        // 分组
        if ($type) {
            $query->andWhere(['level' => $type]);
        }

        // 搜索关键字
        if (trim($keyword)) {
            $query->andWhere(['or', ['like', 'message', $keyword]]);
        }

        //分页
        $pagination = new \yii\data\Pagination([
            'defaultPageSize' => \Yii::$app->systemConfig->getValue('LIST_ROWS', 20),
            'totalCount' => $query->count(),
        ]);

        $data = $query
            ->asArray()
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return $this->render('error', [
            'logs' => $data,
            'pagination' => $pagination,
        ]);
    }
}