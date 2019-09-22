<?php

namespace system\modules\user\models;

use system\core\utils\Tool;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "{{%tab_group}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $path
 * @property integer $pid
 * @property integer $manager
 * @property integer $assistant
 * @property integer $leader
 * @property integer $sub_leader
 * @property string $tel
 * @property string $fax
 * @property string $address
 * @property string $func
 * @property integer $sort
 * @property string $code
 */
class Group extends \system\models\Model
{
    public $log_flag = false; // 默认关闭，等待开启
    public $log_options = [
        'target_name' => 'name',//日志目标对应的字段名，默认name
        'model_name' => '部门',//模型名称
        'except_field' => ['path'],
    ];

    public $convertList = [
        'manager' => 'user',
        'assistant' => 'user',
        'leader' => 'user',
        'sub_leader' => 'user',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tab_user_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
	        [['pid', 'assistant', 'manager', 'leader', 'sub_leader', 'sort'], 'integer'],
	        [['name', 'address'], 'string', 'max' => 100],
	        [['path', 'func'], 'string', 'max' => 255],
	        [['tel', 'fax'], 'string', 'max' => 64],
	        [['code'], 'string', 'max' => 15],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '组id',
            'name' => '组织结构名称',
            'path' => '结构路径',
            'pid' => '父ID',
            'manager' => '部门负责人',
            'assistant' => '主管助理',
            'leader' => '上级领导',
            'sub_leader' => '上级分管主任',
            'tel' => '联系电话',
            'fax' => '部门传真',
            'address' => '地址',
            'func' => '部门职能',
            'sort' => '排序',
            'code' => '部门代码',
        ], parent::attributeLabels());
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            // 根节点
            /*if ($this->id == 1) {
                $this->path = '0-';
                $this->pid = 0;
                return true;
            }*/

            if ($this->pid == 0) {
                $this->path = '0-';
                //$this->pid = 0;
                return true;
            }

            // 非根节点
            if ($this->pid != '') {
                $parentModel = self::findOne($this->pid);
                //父节点不存在
                if (!$parentModel) {
                    return false;
                }
                $this->path = $parentModel->path . $parentModel->id . '-';
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            //判断是否存在子节点
            if (self::find()->where(['pid' => $this->id])->count()) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * 获取所有记录
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAllTreeData()
    {
        $data = self::getAllData();

        $newData = [];
        foreach ($data as $item) {
            $newData[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'pid' => $item['pid'],
                'path' => $item['path'], // 可以用来判断是否编辑数据
            ];
        }

        return $newData;
    }

    // 缓存所有数据
    private static $_allData;

    /**
     * 获取所有的组数据
     * @param $refresh bool 是否刷新缓存
     * @return array|mixed
     */
    public static function getAllData($refresh = false)
    {
        if (!self::$_allData || $refresh) {
            $_dataCacheKey = 'group:data:all';
            // 取缓存
            $data = Yii::$app->cache->get($_dataCacheKey);
            if (!$data || $refresh) {
                @unlink(Yii::getAlias('@webroot').'/data/group.json');
                $data = self::find()->orderBy(['sort' => SORT_DESC, 'name' => SORT_ASC])->asArray()->all();
                $data = ArrayHelper::index($data, 'id');
                Yii::$app->cache->set($_dataCacheKey, $data); // 写缓存
            }

            self::$_allData = $data;
        }

        return self::$_allData;
    }
    
    /**
     * 根据group_id获取一个组数据
     * @param $group_id
     * @return bool|mixed
     */
    public static function getOneById($group_id)
    {
        $data = self::getAllData();
        if (isset($data[$group_id])) {
            return $data[$group_id];
        }
        
        return false;
    }

    /**
     * 根据id获取name
     * @param $group_id
     * @return string
     */
    public static function getNameById($group_id)
    {
        $data = self::getOneById($group_id);
        if (!$data) {
            return '';
        }

        return $data['name'];
    }
    
    /**
     * 根据ID数组获取部门名称
     * @param array $ids 部门ID数组
     * @param string $glue implode连接符号,为false则返回部门名称数组
     * @return array|string
     */
    public static function getNamesByIds($ids, $glue = ',')
    {
        $names = [];
        foreach ($ids as $id) {
            $name = self::getNameById($id);
            if (!empty($name)) {
                $names[] = $name;
            }
        }
    
        return $glue == false ? $names : implode($glue, $names);
    }
    
    /**
     * 根据用户id获取所有权限组及子组
     * @param $user_id int 用户id
     * @return array|bool
     */
    public static function getAllPermissionGroupIdsByUser($user_id)
    {

        $permissionGroup = self::getPermissionGroupByUser($user_id);
        if (!$permissionGroup) {
            return false;
        }
        return self::getChildIdsByIds($permissionGroup);
    }

    /**
     * 根据用户获取此用户的原始权限组
     * @param $user_id
     * @return array|bool
     */
    public static function getPermissionGroupByUser($user_id)
    {
        $data = ContentPermission::getPermissionByUser($user_id, ['extend_user_group_type', 'extend_user_group']);

        if (!$data) {
            return false;
        }

        // 所有选择的组
        $allSelectGroup = [];

        // 选择的组类型
        if ($data['extend_user_group_type']) {
            $groupTypeArr = explode(',', $data['extend_user_group_type']);
            $user_group_id = Yii::$app->user->identity->group_id;
            foreach ($groupTypeArr as $type) {
                if ($type == 1) {
                    // 管理本部门
                    $allSelectGroup[] = $user_group_id;
                } else if ($type == 2) {
                    // 管理主管部门
                    $all = Group::find()->select('id')->where(['manager' => $user_id])->asArray()->all();
                    //var_dump($all);
                    if ($all) {
                        foreach ($all as $item) {
                            $allSelectGroup[] = $item['id'];
                        }
                    }
                }
            }
        }

        // 选择的组
        if ($data['extend_user_group']) {
            $groupArr = explode(',', $data['extend_user_group']);
            foreach ($groupArr as $group) {
                $allSelectGroup[] = substr($group, 0, 1) == 'G' ? substr($group, 1) : $group;
            }
        }

        // 去除重复
        $allSelectGroup = array_unique($allSelectGroup);

        // 获取所有的子组，去除重复

        return $allSelectGroup;
    }

    /**
     * 根据身份获取节点json数据
     * @param bool $isJson
     * @return array|string
     */
    public static function getNodesByIdentity($isJson = false)
    {
        // 获取当前用户可以管理的所有组id
        $manageGroup = self::getPermissionGroupByUser(Yii::$app->user->getId());

        // 如果没有权限限制，获取所有
        if (!$manageGroup) {
            $data = array_values(self::getAllData());
        } else {
            $data = self::getChildByIds($manageGroup);
        }
    
        foreach ($data as $key => $val) {
            $data[$key]['iconSkin'] = $val['pid'] == 0 ? 'iconfont icon-bank' : 'iconfont icon-group';
            $data[$key]['open'] = $val['pid'] == 0 ? true : Yii::$app->systemConfig->getValue('GROUP_NODE_OPEN', 0) != 0;
        }
    
        if ($isJson) {
            return Json::encode($data);
        }

        return $data;

    }

    /**
     * 拖拽组到某个位置
     * @param $item array 配置数组 ['id' => '当前组id', 'target_id' => '拖拽目标组id']
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function dragGroup($item)
    {
        //当前组id
        $model = self::findOne($item['id']);
        if (!$model) {
            return false;
        }
        //目标组id
        $targetModel = self::findOne($item['target_id']);
        if (!$targetModel) {
            return false;
        }
        //如果当前组的pid和目标组id相同，则无需更改
        if ($model->pid == $targetModel->id) {
            return false;
        }

        //开启事务
        $transaction = Yii::$app->db->beginTransaction();
        try {
            //更新只能一个个的更新，先把所有的组拿出来，然后从上往下一级一级的更新
            $searchPath = $model->path . $model->id . '-';

            // 1，先更新当前组的pid
            $model->pid = $targetModel->id;
            $res = $model->save();
            if (!$res) {
                return false;
            }

            // 2，再按照之前的path取出来其下的所有的子节点，进行更新
            $childGroup = Group::find()
                ->select(['id', 'name', 'path', 'pid'])
                ->where(['like', 'path', $searchPath . '%', false])
                ->orderBy(['path' => SORT_ASC])
                ->asArray()
                ->all();

            foreach ($childGroup as $key => $item) {
                $model = self::findOne($item['id']);
                if (!$model) {
                    return false;
                }
                $res = $model->save();
                if (!$res) {
                    return false;
                }
            }

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();

            return false;
        }
    }

    /**
     * 获取name的path
     * @param int $id 组id
     * @param bool $showFirst 是否显示第一个元素
     * @param string $sep 分隔符
     * @return string
     */
    public static function getNamePath($id, $showFirst = false, $sep = ' / ')
    {
        $data = self::getOneById($id);
        if (!$data) {
            return '';
        }

        // 获取path的数组
        $pathArray = explode('-', trim($data['path'], '-'));
        $count = count($pathArray);
        $names = [];
        // 取出所有父级组name
        foreach ($pathArray as $k => $group_id) {
            if ($group_id == 0) {
                continue;
            }
            // 是否要跳过第一个元素，path的格式一般是0-1-3-，因为第一个元素一般是0，那么考虑的是第二个元素；但是如果只选择来根目录，
            // 比如path=0-1-，那么就不能省略掉来，必须显示出来
            if (!$showFirst && $k == 1 && $count >= 2) {
                continue;
            }
            $names[] = self::getNameById($group_id);
        }

        // 把本身的名字也放进去
        array_push($names, $data['name']);

        return implode($sep, $names);
    }

    /**
     * 批量获取组名称的全路径
     * @param array $groups 组id的数组
     * @param bool $showFirst 是否显示第一个元素，比如学校名称
     * @param string $sep 间隔符号
     * @return string
     */
    public static function getNamePathByGroups($groups, $showFirst = false, $sep = '/')
    {
        $newData = [];
        foreach ($groups as $id) {
            $newData[] = self::getNamePath($id, $showFirst, $sep);
        }
        return $newData;
    }

    /**
     * 根据给定的数据递归创建组，并返回最后一个创建的组
     * @param $data array 组的名称数组，比如['湖北省', '武汉市', '洪山区', '卓刀泉街道'];
     * @return bool|int
     */
    public static function createGroup($data)
    {
        //print_r($data);exit;
        if (empty($data) || !is_array($data)) {
            return false;
        }

        $pid = 0;
        $group = null;
        // 创建层次关系的组
        foreach ($data as $key => $item) {
            $group = self::find()->where(['name' => $item, 'pid' => $pid])->one();
            if (!$group) {
                $group = new self();
                $group->name = $item;
                $group->pid = $pid;
                if (!$group->save()) {
                    return false;
                }
            }
            // 保存成功以后，父id 更改为当前id
            $pid = $group->id;
        }

        // 将最后一个组的id返回
        return $group ? $group->id : false;
    }

    /**
     * 去除重复的下级group
     * @param $ids array 组id数组
     * @return array
     */
    public static function unRepeatChildGroup($ids)
    {
        //查询所有G开头的数据
        $groups = Tool::get_array_by_condition(self::getAllData(), ['id' => $ids]);
        ArrayHelper::multisort($groups, 'path', SORT_DESC);
        $groupIds = ArrayHelper::map($groups, 'id', 'path');

        //判断有没有父级
        $range = [];
        foreach ($groupIds as $id => $path) {
            $noParent = true;
            $path = explode('-', substr($path, 0, -1));
            foreach ($path AS $paths) {
                if (isset($groupIds[(int)$paths])) {
                    $noParent = false;
                    break;
                }
            }

            if ($noParent) {
                $range[] = $id;
            }

        }

        return $range;
    }

    /**
     * 根据节点的id数组获取这些节点下的所有子节点
     * @param array $ids 节点数组
     * @return array
     */
    public static function getChildByIds($ids)
    {
        if(empty($ids)){
            return [];
        }

        // 先去除重复的子组，把有依赖关系的子级组去掉
        $ids = self::unRepeatChildGroup($ids);

        $allChildNodes = [];
        foreach ($ids as $id) {
            $child = static::getChildById($id);
            if($child){
                foreach ($child as $one) {
                    $allChildNodes[] = $one;
                }
            }
        }
        return $allChildNodes;
    }

    /**
     * 根据节点的id数组获取所有这些节点下的所有子节点的id数组
     * @param array $ids 节点数组
     * @return array ['id1', 'id2', ...]
     */
    public static function getChildIdsByIds($ids)
    {
        $childIds = static::getChildByIds($ids);
        return ArrayHelper::map($childIds, 'id', 'id');
    }

    /**
     * 根据id获取此id的所有子节点
     * @param $id int 节点id
     * @param bool $includeSelf 是否包含本节点
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getChildById($id, $includeSelf = true)
    {
        //当前结点
        $node = static::getOneById($id);
        if (!$node) {
            return [];
        }
        $childNodes = [];
        if ($node['path'] != '') {
            $childNodes = Tool::get_array_by_condition(self::getAllData(), ['like', 'path', $node['path'] . $id . '-']);
        }
        //是否包含自身节点
        if ($includeSelf) {
            array_unshift($childNodes, $node);
            return $childNodes;
        }
        return $childNodes;
    }

    /**
     * 根据组id获取
     * @param $id
     * @param bool $includeSelf
     * @return array
     */
    public static function getChildIdsById($id, $includeSelf = true)
    {
        $data = self::getChildById($id, $includeSelf);
        return ArrayHelper::getColumn($data, 'id');
    }

    /**
     * 根据id获取一个结点
     * @param int $id 结点id
     * @param bool $asArray 是否返回数组，否则返回对象
     * @return array|null|self
     */
    public static function _getOneById($id, $asArray = true)
    {
        $query = static::find()->where(['id' => $id]);
        if ($asArray) {
            $query->asArray();
        }
        return $query->one();
    }

    /**
     * 获取某个id的第一级子id
     * @param int $id 组id
     * @return array
     */
    public static function getFirstChildsById($id = 0)
    {
        $data = self::find()->asArray()
            ->select(['id', 'name', 'pid'])
            ->where(['pid' => $id])
            ->orderBy(['sort' => SORT_DESC, 'name' => SORT_ASC])
            ->all();

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        self::getAllData(true);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();
        self::getAllData(true);
    }

    /**
     * 关联主管
     * @return \yii\db\ActiveQuery
     */
    public function getManage()
    {
        return $this->hasOne(User::className(), ['user_id' => 'manager']);
    }

    /**
     * 关联助理
     * @return \yii\db\ActiveQuery
     */
    public function getAssistant()
    {
        return $this->hasOne(User::className(), ['user_id' => 'assistant']);
    }

    /**
     * 关联上级主管领导
     * @return \yii\db\ActiveQuery
     */
    public function getLeader()
    {
        return $this->hasOne(User::className(), ['user_id' => 'leader']);
    }

    /**
     * 关联上级分管主任
     * @return \yii\db\ActiveQuery
     */
    public function getSubLeader()
    {
        return $this->hasOne(User::className(), ['user_id' => 'sub_leader']);
    }

    /**
     * 根据部门id获取父级部门的id，如果父级部门不存在返回0
     * @param $id int 部门id
     * @return int|mixed
     */
    public static function getParentId($id)
    {
        $group = self::getOneById($id);
        if ($group) {
            $paths = explode('-', trim($group['path'], '-'));
            $parentId = array_pop($paths);
            if (self::getOneById($parentId)) {
                return $parentId;
            }
        }

        return 0;
    }

    /**
     * 根据组的path获取组的所有父级id
     * @param $id int 组id
     * @return array
     */
    public static function getParentIds($id)
    {
        $group = self::getOneById($id);
        if ($group) {
            $paths = explode('-', trim($group['path'], '-'));
            return $paths;
        }

        return [];
    }
    
    /**
     * 获取部门树
     * @param $data array 要递归的部门数据,如果为空则获取全部
     * @param $id integer 最上级节点ID
     * @return array
     */
    public static function getRecurData($data = null, $id = 0, $has_key = true)
    {
        if (empty($data)) {
            $data = self::getAllData();
        }
    
        if ($id == 0) {
            return self::_RecurData($data, $id, $has_key);
        } else {
            $parent = self::getOneById($id);
            if (!empty($parent)) {
                $parent['children'] = self::_RecurData($data, $id, $has_key);
            }
            
            $arr[] = $parent;
            return $arr;
        }
    }
    
    /**
     * 递归获取菜单树
     * @param int $pid 父ID
     * @param bool $all false表示获取当前用户有权限并且is_display为1的菜单，true表示获取所有菜单
     * @param bool $is_display true表示只获取is_display=1的菜单 false表示获取所有
     * @param bool $has_key 是否把主键ID作为key
     * @return array
     */
    public static function _RecurData($list, $pid, $has_key = true)
    {
        $data = [];
        foreach ($list as $id => $one) {
            if ($one['pid'] == $pid) {
                if ($has_key) {
                    $data[$id] = $one;
                } else {
                    $data[] = $one;
                }
    
            }
        }
        if (!empty($list)) {
            foreach ($data as $id => $one) {
                $data[$id]['children'] = self::_RecurData($list, $one['id'], $has_key);
            }
        }
        return $data;
    }
    
    /**
     * 批量更新部门
     * @param string $group 部门名称，多级用‘/’连接，如：雨滴科技/研发部/技术组
     * @return int
     */
    public static function updateGroupByBatch($group)
    {
        $groups = explode('/', $group);
        $pid = 1;
        $path = "0-";
        foreach ($groups as $val) {
            $model = self::findOne(['name' => trim($val)]);
            if (empty($model)) {
                $model = new self();
                $model->name = $val;
                $model->pid = $pid;
                $model->path = $path . $pid . "-";
                if (!$model->save()) {
                    print_r($model->errors);
                    exit();
                }
            }

            $pid = $model->id;
            $path = $model->path;
        }
    
        return $pid;
    }

    public static $_department = [];

    public static function getDepartment($condition = []){
        if(empty(self::$_department)){
            self::$_department = self::find()->asArray()->all();
        }
        return Tool::get_array_by_condition(self::$_department, $condition, $keep_key = false);
    }

    /**
     * 获取该部门所有领导人
     * @param $group_id     string|int   部门id
     * @param $leader       string       部门领导
     * @return array|bool
     */
    public static function getGroupLeaderUserId($group_id, $leader = 'manager')
    {
        if(substr($group_id, 0, 1) == 'G'){
            $group_id = substr($group_id, 1);
        }
        $model = self::findOne($group_id);

        if(!$model){
            return false;
        };
        $arr = [];
        $leader_arr = explode(',', $leader);
        foreach($leader_arr as $v){
            if($model[$v] != 0){
                $arr[] = $model[$v];
            }
        }
        if(count($arr) == 0){
            return false;
        }
        return $arr;
    }
}
