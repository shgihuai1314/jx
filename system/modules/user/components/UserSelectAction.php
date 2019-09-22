<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/9/26
 * Time: 19:33
 */

namespace system\modules\user\components;

use system\modules\user\models\User as Users;
use system\modules\user\models\Group;
use system\modules\user\models\Position;
use system\modules\user\models\User;
use system\core\utils\Tool;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\base\Action;
use Yii;

/**
 * 用户选择器action
 * Class SelectAction
 * @package system\components
 */
class UserSelectAction extends Action
{
    public $view = '@system/modules/user/views/default/user-group-select';
    
	/**
	 * @return string
	 */
	public function run()
	{
		$params = ArrayHelper::merge(Yii::$app->request->queryParams, Yii::$app->request->post());
		$action = ArrayHelper::getValue($params, 'action');

		$filters = Yii::$app->systemConfig->getValue('USER_GROUP_FILTER');
		$filters = is_array($filters) ? $filters : explode(',', str_replace('，', ',', $filters));
        $filterArr = [
            'department' => [],
            'position' => [],
            'user' => [],
        ];
        foreach ($filters as $one) {
            if (substr($one, 0, 1) == 'G' && is_numeric(substr($one, 1))) {//部门ID范围
                $filterArr['department'][] = substr($one, 1);
            } elseif (substr($one, 0, 1) == 'P' && is_numeric(substr($one, 1))) {//职位ID范围
                $filterArr['position'][] = substr($one, 1);
            } elseif (substr($one, 0, 1) == 'U' && is_numeric(substr($one, 1))) {//用户ID范围
                $filterArr['user'][] = substr($one, 1);
            }
        }

        if ($action == 'save-json-data') {
            $refresh = false;
            if (!is_dir(Yii::getAlias('@webroot').'/data')) {
                FileHelper::createDirectory(Yii::getAlias('@webroot').'/data');
            }

            if (!file_exists(Yii::getAlias('@webroot').'/data/group.json')) {
                $data = Group::find()->asArray()
                    ->indexBy('id')
                    ->select(['id', 'name', 'path', 'pid'])
                    ->andWhere(['not in', 'id', $filterArr['department']])
                    ->orderBy(['sort' => SORT_DESC])
                    ->all();
                file_put_contents(Yii::getAlias('@webroot').'/data/groupIds.json', Json::encode($data));
                file_put_contents(Yii::getAlias('@webroot').'/data/group.json', Json::encode(array_values($data)));
                $refresh = true;
            }
    
            if (!file_exists(Yii::getAlias('@webroot').'/data/position.json')) {
                $data = Position::getAllData();
                if (!empty($filterArr['position'])) {
                    $data = Tool::get_array_by_condition($data, ['not in', 'id', $filterArr['position']]);
                }
                file_put_contents(Yii::getAlias('@webroot').'/data/positionIds.json', Json::encode($data));
                file_put_contents(Yii::getAlias('@webroot').'/data/position.json', Json::encode(array_values($data)));
                $refresh = true;
            }
    
            if (!file_exists(Yii::getAlias('@webroot').'/data/user.json')) {
                $data = User::find()->asArray()
                    ->indexBy('user_id')
                    ->select(['user_id', 'username', 'realname', 'avatar', 'group_id', 'position_id'])
                    ->where(['status' => User::STATUS_ACTIVE])
                    ->andWhere(['not in', 'user_id', $filterArr['user']])
                    ->orderBy(['sort' => SORT_DESC])
                    ->all();
                file_put_contents(Yii::getAlias('@webroot').'/data/userIds.json', Json::encode($data));
                file_put_contents(Yii::getAlias('@webroot').'/data/user.json', Json::encode(array_values($data)));
                $refresh = true;
            }
            
            echo json_encode([
                'code' => $refresh ? 0 : 1,
            ]);
            exit();
        }
		elseif ($action == 'search-items') {
            $search = ArrayHelper::getValue($params, 'search', '');
            if (empty($search)) {
                echo json_encode([]);
                exit();
            }

            $range_type = ArrayHelper::getValue($params, 'range_type', 0);//范围取值类型 0：交集；1：并集
            $select_type = ArrayHelper::getValue($params, 'select_type', 'department,position,user');//允许选择的类型

            $show_range = ArrayHelper::getValue($params, 'show_range', null);//选择范围，G1，P2，R1，U1，U2
            $show_range = explode(',', $show_range);
            $range = [
                'department' => [],
                'position' => [],
                'user' => [],
            ];
            foreach ($show_range as $one) {
                if (substr($one, 0, 1) == 'G' && is_numeric(substr($one, 1))) {//部门ID范围
                    $range['department'][] = substr($one, 1);
                } elseif (substr($one, 0, 1) == 'P' && is_numeric(substr($one, 1))) {//职位ID范围
                    $range['position'][] = substr($one, 1);
                } elseif (substr($one, 0, 1) == 'U' && is_numeric(substr($one, 1))) {//用户ID范围
                    $range['user'][] = substr($one, 1);
                }
            }

            $data = [];
            foreach (explode(',', $select_type) as $val) {
                if ($val == 'department') {
                    $query = Group::find()->where(['like', 'name', $search])->asArray();
                    if (!empty($range['department'])) {
                        $query = $query->andWhere(['id' => Group::getChildIdsByIds($range['department'])]);
                    }
                    $groups = $query->all();

                    foreach ($groups as $one) {
                        $data[] = [
                            'id' => 'G'.$one['id'],
                            'text' => $one['name'],
                            'avatar' => '/static/images/icon/icon-department.png'
                        ];
                    }
                } elseif ($val == 'position') {
                    $query = Position::find()->where(['like', 'name', $search])->asArray();
                    if (!empty($range['position'])) {
                        $query = $query->andWhere(['id' => $range['position']]);
                    }
                    $positions = $query->all();

                    foreach ($positions as $one) {
                        $data[] = [
                            'id' => 'P'.$one['id'],
                            'text' => $one['name'],
                            'avatar' => '/static/images/icon/icon-position.png'
                        ];
                    }
                } elseif ($val == 'user') {
                    $query = User::find()->where(['or', ['like', 'username', $search], ['like', 'realname', $search]])->asArray();
                    if ($range_type == 0) {//和部门、职位范围取交集
                        if (!empty($range['department'])) {
                            $query->andWhere(['group_id' => Group::getChildIdsByIds($range['department'])]);
                        }
                        if (!empty($range['position'])) {
                            $query = $query->andWhere(['position_id' => $range['position']]);
                        }
                        if (!empty($range['user'])) {
                            $query = $query->andWhere(['user_id' => $range['user']]);
                        }
                    }
                    $users = $query->all();

                    foreach ($users as $one) {
                        $data[] = [
                            'id' => 'U'.$one['user_id'],
                            'text' => $one['realname'],
                            'avatar' => $one['avatar']
                        ];
                    }
                }
            }
            if(Yii::$app->systemConfig->getValue('USER_GROUP_FILTER', [])){
                $filter = explode(',', Yii::$app->systemConfig->getValue('USER_GROUP_FILTER', []));
                foreach ($data as $k => $one){
                    if(in_array($one['id'], $filter)){
                        unset($data[$k]);
                    }
                }
            }
            echo json_encode($data);
            exit();
        }
		
		$value = ArrayHelper::getValue($params, 'value');
		$options = json_decode(ArrayHelper::getValue($params, 'options', ''));

		$this->controller->layout = '/frame';
		return $this->controller->render($this->view, [
			'value' => $value,
			'options' => $options,
		]);
	}
}