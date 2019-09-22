<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/7/14
 * Time: 上午11:09
 */
namespace system\modules\user\components;

use Yii;
use system\modules\user\models\Group;
use system\modules\user\models\Position;
use system\modules\role\models\AuthRole;
use system\modules\user\models\User as UserModel;
use system\modules\user\models\User;
use yii\helpers\ArrayHelper;
use system\core\utils\Tool;
use yii\base\Model;

class UserWithGroup extends Model
{
    private static $_userModel = null; // 用户模型
    private static $_groupModel = null; // 组模型

    /**
     * 判断user_id是否在给定的id串中，可以用来判断：用户的权限等
     * @param $user_id integer 用户id
     * @param $ids string|array id串
     * @param $flag bool 如果id串不设置，那么返回flag
     * @return bool
     */
    public static function isUserIn($user_id, $ids, $flag = true)
    {
        // 如果id串不设置，根据flag判断返回值
        if (!$ids) {
            return $flag;
        }

        // 先判断用户是否存在
        if (is_null(self::$_userModel)) {
            self::$_userModel = UserModel::findOne($user_id);
        }
        $user = self::$_userModel;

        if (!$user) {
            return false;
        }

        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        if (is_null(self::$_groupModel)) {
            self::$_groupModel = Group::findOne($user->group_id);
        }
        $userGroup = self::$_groupModel;

        $userGroupPath = [];
        if ($userGroup) {
            $path = trim($userGroup->path, '-');
            $userGroupPath = explode('-', $path);
            $userGroupPath[] = $user->group_id;
        }

        // 判断用户是否存在
        foreach ($ids as $id) {
            // 直接判断用户id
            if (substr($id, 0, 1) == 'U' && is_numeric(substr($id, 1))) {
                if ($user_id == substr($id, 1)) {
                    return true;
                }
            }
            // 判断用户组
            if (substr($id, 0, 1) == 'G' && is_numeric(substr($id, 1))) {
                if (in_array(substr($id, 1), $userGroupPath)) {
                    return true;
                }
            }
            // 判断职位
            if (substr($id, 0, 1) == 'P' && is_numeric(substr($id, 1))) {
                if ($user->position_id == substr($id, 1)) {
                    return true;
                }
            }
            // 判断角色
            if (substr($id, 0, 1) == 'R' && is_numeric(substr($id, 1))) {
                if ($user->role_id == substr($id, 1)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 根据id串获取对应的名称;包括用户姓名，组名称，角色名称，职位名称
     * @param $ids array|string id串或者id数组
     * @param $asArray bool 是否作为数组返回
     * @return string
     */
    public static function getNameByIds($ids, $asArray = false)
    {
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        $userIds = $groupIds = $roleIds = $positionIds = [];
        foreach ($ids as $id) {
            // 判断用户组
            if (substr($id, 0, 1) == 'G' && is_numeric(substr($id, 1))) {
                $groupIds[] = substr($id, 1);
            }
            // 判断用户
            else if (substr($id, 0, 1) == 'U' && is_numeric(substr($id, 1))) {
                $userIds[] = substr($id, 1);
            }
            // 判断角色
            else if (substr($id, 0, 1) == 'R' && is_numeric(substr($id, 1))) {
                $roleIds[] = substr($id, 1);
            }
            // 判断职位
            else if (substr($id, 0, 1) == 'P' && is_numeric(substr($id, 1))) {
                $positionIds[] = substr($id, 1);
            }
            // 判断其他
        }

        $user = $group = $role = $position = [];
        // 查询组
        if ($groupIds) {
            $group = Group::find()->select(['name'])->where(['id' => $groupIds])->asArray()->column();
        }
        // 查询用户
        if ($userIds) {
            $user = UserModel::find()->select(['realname'])->where(['user_id' => $userIds])->asArray()->column();
        }
        // 查询角色
        if ($roleIds) {
            $role = AuthRole::find()->select(['name'])->where(['role_id' => $roleIds])->asArray()->column();
        }
        // 查询职位
        if ($positionIds) {
            $position = Position::find()->select(['name'])->where(['id' => $positionIds])->asArray()->column();
        }
        // 查询其他

        // 合并所有数据
        $names = ArrayHelper::merge($user, $group, $role, $position);

        if ($asArray) {
            return $names;
        }

        return implode(',', $names);
    }

    /**
     * 获取给定数据字符串的交集，只交部门和职位下的用户
     * @param $ids array|string 范围数组
     * @return array  部门和职位下有共同用户的部门及职位
     */
    public static function getIntersection($ids)
    {
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        if (empty($ids)) {
            return [];
        }

        $userIds = $groupIds = $roleIds = $positionIds = [];
        foreach ($ids as $id) {
            // 判断用户组
            if (substr($id, 0, 1) == 'G' && is_numeric(substr($id, 1))) {
                $groupIds[] = substr($id, 1);
            }
            // 判断用户
            else if (substr($id, 0, 1) == 'U' && is_numeric(substr($id, 1))) {
                $userIds[] = substr($id, 1);
            }
            // 判断角色
            else if (substr($id, 0, 1) == 'R' && is_numeric(substr($id, 1))) {
                $roleIds[] = substr($id, 1);
            }
            // 判断职位
            else if (substr($id, 0, 1) == 'P' && is_numeric(substr($id, 1))) {
                $positionIds[] = substr($id, 1);
            }
            // 判断其他
        }

        // 如果组或者部门有一个为空，那么不需要取交集了，直接返回原数据即可
        if (empty($groupIds) || empty($positionIds)) {
            // 直接返回
            return $ids;
        }

        // 获取指定部门的所有子部门
        $allGroupIds = Group::getChildIdsByIds($groupIds);

        // 只取部门和职位有交集的用户，查到以后只要相应的部门和职位
        $query = User::find()
            ->select(['user_id', 'group_id', 'position_id'])
            ->andWhere(['status' => User::STATUS_ACTIVE]);   // 只取正常状态

        if ($groupIds) {
            $query->andWhere(['group_id' => $allGroupIds]);
        }

        if ($positionIds) {
            $query->andWhere(['position_id' => $positionIds]);
        }

        if ($userIds) {
            $query->andWhere(['user_id' => $userIds]);
        }

        $allData = $query->asArray()->all();
        // 直接拿到所有的部门和职位
        $newData = [];
        foreach ($allData as $item) {
            $newData[] = 'G' . $item['group_id'];
            $newData[] = 'P' . $item['position_id'];
            $newData[] = 'U' . $item['user_id'];
        }

        // 把角色和用户的数据放进去
        foreach ($roleIds as $roleId) {
            $newData[] = 'R'.$roleId;
        }

        $newData = array_unique($newData);

        return $newData;
    }

    /**
     * 取两个组的交集
     * @param $ids string|array 原始id串
     * @param $group_ids array
     *
     * @return array
     */
    public static function getGroupIntersect($ids, $group_ids)
    {
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        if (empty($ids)) {
            return [];
        }

        if (!is_array($group_ids)) {
            $group_ids = explode(',', $group_ids);
        }

        $groupIds2 = [];
        foreach ($group_ids as $item) {
            // 如果以G开头的，那么处理
            if (substr($item, 0, 1) == 'G' && is_numeric(substr($item, 1))) {
                $groupIds2[] = substr($item, 1);
            } else {
                $groupIds2[] = $item;
            }
        }

        $userIds = $groupIds = $roleIds = $positionIds = [];
        foreach ($ids as $id) {
            // 判断用户组
            if (substr($id, 0, 1) == 'G' && is_numeric(substr($id, 1))) {
                $groupIds[] = substr($id, 1);
            }
            // 判断用户
            else if (substr($id, 0, 1) == 'U' && is_numeric(substr($id, 1))) {
                $userIds[] = substr($id, 1);
            }
            // 判断角色
            else if (substr($id, 0, 1) == 'R' && is_numeric(substr($id, 1))) {
                $roleIds[] = substr($id, 1);
            }
            // 判断职位
            else if (substr($id, 0, 1) == 'P' && is_numeric(substr($id, 1))) {
                $positionIds[] = substr($id, 1);
            }
            // 判断其他
        }

        // 获取所有子部门
        $allGroupIds = Group::getChildIdsByIds($groupIds);

        $selectGroupIds = Group::getChildIdsByIds($groupIds2);

        // 取交集
        $intersect = array_intersect($allGroupIds, $selectGroupIds);
        $intersect = array_unique($intersect);

        $newData = [];
        foreach ($intersect as $item) {
            $newData[] = 'G'.$item;
        }

        foreach ($userIds as $userId) {
            $newData[] = 'U'.$userId;
        }

        foreach ($positionIds as $positionId) {
            $newData[] = 'P'.$positionId;
        }

        foreach ($roleIds as $roleId) {
            $newData[] = 'R'.$roleId;
        }

        return $newData;
    }
    
    /**
     * 根据用户选择器获取用户、部门、职位的名称
     * @param string $params
     * @param string $glue implode连接符号,为false则返回部门名称数组
     * @return array|string
     */
    public static function getNamesBySelect($params, $glue = ',')
    {
        $ids = empty($params) ? [] : (is_array($params) ? $params : explode(',', $params));
        foreach ($ids as $one) {
            if (substr($one, 0, 1) == 'G' && is_numeric(substr($one, 1))) {//部门ID范围
                $group[] = substr($one, 1);
            } elseif (substr($one, 0, 1) == 'P' && is_numeric(substr($one, 1))) {//职位ID范围
                $position[] = substr($one, 1);
            } elseif (substr($one, 0, 1) == 'U' && is_numeric(substr($one, 1))) {//用户ID范围
                $user[] = substr($one, 1);
            } else {
                $user[] = $one;
            }
        }
        
        $names = [];
        if (isset($group)) {
            $names = ArrayHelper::merge($names, Group::getNamesByIds($group, false));
        }
        if (isset($position)) {
            $arr = Position::getAllMap();
            $names = ArrayHelper::merge($names, Tool::array_sort_by_keys($arr, $position));
        }
        if (isset($user)) {
            $names = ArrayHelper::merge($names, User::getNameArr($user));
        }
        
        return $glue == false ? $names : implode($glue, $names);
    }
    
    /**
     * 根据用户选择器获取的参数得到满足条件的用户ID数组
     * @param string|array $ids id串
     * @param bool $includeChild 是否要包含子级用户组
     * @return array
     */
    public static function getIdsBySelect($ids, $includeChild = true)
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        foreach ($ids as $one) {
            if (substr($one, 0, 1) == 'G' && is_numeric(substr($one, 1))) {//部门ID范围
                $group[] = substr($one, 1);
            } elseif (substr($one, 0, 1) == 'P' && is_numeric(substr($one, 1))) {//职位ID范围
                $position[] = substr($one, 1);
            } elseif (substr($one, 0, 1) == 'U' && is_numeric(substr($one, 1))) {//用户ID范围
                $user[] = substr($one, 1);
            }
        }
        
        $condition = [];
        if (isset($group)) {
            $condition[]['group_id'] = $includeChild ? ArrayHelper::merge(Group::getChildIdsByIds($group), $group) : $group;
        }
        if (isset($position)) {
            $condition[]['position_id'] = $position;
        }
        if (isset($user)) {
            $condition[]['user_id'] = $user;
        }
        
        if (empty($condition)) {
            return [];
        } else {
            $users = array_keys(User::getDataByCondition(ArrayHelper::merge(['or'], $condition)));
            //过滤掉退休的人
            if(Yii::$app->systemConfig->getValue('USER_GROUP_FILTER', [])){
                $filter = explode(',', Yii::$app->systemConfig->getValue('USER_GROUP_FILTER', []));
                foreach ($filter as $i => $v){
                    $filter[$i] = substr($v, 1);
                }
                foreach ($users as $k => $user){
                    if(in_array($user, $filter)){
                        unset($users[$k]);
                    }
                }
            }

            return $users;
        }
        
    }
    
    /**
     * 获取用户选择器选择的数据的详细信息
     * @param array|string $ids 用户选择器返回的数据 U1,U2,G1,P2或[U1,U2,G1,P2]或1,2,3,4
     * @param string $type
     * @return array
     */
    public static function getSelectInfo($ids, $type='user'){
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        $list = [];
        foreach ($ids as $one) {
            $prefix = $type == 'department' ? 'G' : ($type == 'position' ? 'P' : 'U');
            if (is_numeric($one)) {
                $one = $prefix . $one;
            }
            
            if (substr($one, 0, 1) == 'G' && is_numeric(substr($one, 1))) {//部门ID范围
                $list[$one] = Group::getOneById(substr($one, 1));
            } elseif (substr($one, 0, 1) == 'P' && is_numeric(substr($one, 1))) {//职位ID范围
                $list[$one] = Position::getOneById(substr($one, 1));
            } elseif (substr($one, 0, 1) == 'U' && is_numeric(substr($one, 1))) {//用户ID范围
                $list[$one] = User::getUser(substr($one, 1));
            }
        }
        
        return $list;
    }
    
}