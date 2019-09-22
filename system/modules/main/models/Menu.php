<?php

namespace system\modules\main\models;

use system\core\utils\Tool;
use system\modules\role\models\AuthAssign;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "yudear_menu".
 *
 * @property integer $menu_id       菜单id
 * @property string $menu_name      菜单名称
 * @property integer $pid           父级id
 * @property string $module         所属模块
 * @property string $path           path
 * @property string $icon           图标
 * @property integer $type          类型(0:菜单;1:操作)
 * @property integer $is_show       是否显示：0不显示，1显示；
 * @property integer $sort          排序
 */
class Menu extends \system\models\Model
{
    public $log_flag = true;
    public $log_options = [
        'target_name' => 'menu_name',//日志目标对应的字段名，默认name
        'model_name' => '菜单',//模型名称
    ];

	public $operate;
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_menu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['menu_name'], 'required'],
            [['menu_id', 'pid', 'type', 'is_show', 'sort'], 'integer'],
            [['path'], 'string'/*, 'max' => 64*/],
            [['menu_name', 'module', 'icon'], 'string', 'max' => 32],
	        [['operate'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'menu_id' => '菜单ID',
            'menu_name' => '菜单名称',
            'pid' => '上级菜单',
            'module' => '模块名称',
            'path' => '路径',
            'icon' => '图标',
	        'type' => '类型',
            'is_show' => '是否显示',
            'operate' => '操作权限',
            'sort' => '排序',
        ], parent::attributeLabels());
    }
	
	/**
     * 选择性属性列表
     * @return array
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
	    $list = [
		    'type' => ['0' => '菜单', '1' => '操作'],
            'is_show' => ['1' => '是', '0' => '否'],
		    'pid' => self::getNameArr()
        ];
	    return self::getAttributeValue($list, $field, $key, $default);
    }
	
    public static $nameArr = [];
    
	/**
	 * 根据节点id数组返回节点id对应的名称数组
	 * @param array $ids 节点id数组
	 * @return array
	 */
	public static function getNameArr()
	{
        if (empty(self::$nameArr)) {
            $list = self::getMenusByCondition([]);
            self::$nameArr = ArrayHelper::map($list, 'id', 'name');
        }
        
		return self::$nameArr;
	}
	
	/**
	 * 根据条件获取菜单
	 * @param $condition
	 * @return array
	 */
	public static function getMenusByCondition($condition)
	{
		$all = self::getAllData();
		$condition = is_numeric($condition) ? ['menu_id' => $condition] : $condition;
		
		return Tool::get_array_by_condition($all, $condition);
	}
	
	/**
	 * 根据条件获取单个菜单
	 * @param $condition
	 * @return mixed
	 */
	public static function getOneMenu($condition)
	{
		$all = self::getMenusByCondition($condition);
		
		return reset($all);
	}
	
	//整理菜单所需的数据
    public static function getMenu()
    {
        $menus = self::getMenusTree(['is_show' => 1], true);
        $menus = ArrayHelper::index($menus, 'menu_id');

        foreach ($menus as $key => $val) {
            $item = ['menu_id', 'menu_name', 'icon', 'children', 'path', 'is_show'];
            //删除一级菜单多余的元素
            foreach ($val as $k => $v) {
                if (!in_array($k, $item)) {
                    unset($val[$k]);
                }
            }
            $val['href'] = $val['path'];
            $val['children'] = array_values($val['children']);
            //二级菜单
            foreach ($val['children'] as $key1 => $val1) {
                //删除二级菜单多余的元素
                $items1 = ['menu_name', 'icon', 'path', 'children'];
                foreach ($val1 as $k1 => $v1) {
                    if (!in_array($k1, $items1)) {
                        unset($val['children'][$key1][$k1]);
                    }
                }
                //添加二级菜单必须的字段
                $val['children'][$key1]['title'] = $val1['menu_name'];
                //解析路径
                if (strpos($val1['path'], "http://") !== false || strpos($val1['path'], "https://") !== false) {
                    $val['children'][$key1]['href'] = $val1['path'];
                } else {
                    $val['children'][$key1]['href'] = \yii\helpers\Url::toRoute('/' . $val1['path']);
                }
                $val['children'][$key1]['icon'] = $val1['icon'] ? $val1['icon'] : 'fa fa-circle-o';
                $val['children'][$key1]['children'] = array_values($val1['children']);
                //三级菜单
                foreach ($val['children'][$key1]['children'] as $key2 => $val2) {
                    //删除三级多余的元素
                    $items = ['path', 'menu_name', 'icon', 'children'];
                    foreach ($val2 as $k2 => $v2) {
                        if (!in_array($k2, $items)) {
                            unset($val['children'][$key1]['children'][$key2][$k2]);
                        }
                    }
                    //添加三级菜单需要的字段
                    if (strpos($val2['path'], "http://") !== false || strpos($val2['path'], "https://") !== false) {
                        $val['children'][$key1]['children'][$key2]['href'] = $val2['path'];
                    } else {
                        $val['children'][$key1]['children'][$key2]['href'] = \yii\helpers\Url::toRoute('/' . $val2['path']);
                    }
                    $val['children'][$key1]['children'][$key2]['title'] = $val2['menu_name'];
                    $val['children'][$key1]['children'][$key2]['icon'] = $val2['icon'] ? $val2['icon'] : 'fa fa-circle-o';
                    //删除四级权限
                    unset($val['children'][$key1]['children'][$key2]['children']);
                }
                $menus[$key] = $val;
            }
        }
        return $menus;
    }

    /**
     * 获取所有菜单的树形结构
     * @param string|array $condition 筛选条件
     * @param bool $hasPermission 是否要通过角色验证，只显示角色内的菜单
     * @return array
     */
    public static function getMenusTree($condition = [], $hasPermission = true, $id = 0)
    {
        // 非超管的用户才需要过滤权限菜单
        $is_supper = AuthAssign::isSuper(Yii::$app->user->getId());
        if (!$is_supper && $hasPermission) {
	        $condition['menu_id'] = self::getPermissionId();
        }
	
	    $list = self::getMenusByCondition($condition);
	    if (empty($list)) {
			return [];
	    } elseif ($id == 0) {
		    return self::_RecurMenus($list, min(ArrayHelper::getColumn($list, 'pid')));
	    } else {
		    $parent = self::getOneMenu($id);
		    if (!empty($parent)) {
			    $parent['level'] = 1;
			    $parent['children'] = self::_RecurMenus($list, $id, 2);
		    }
		
		    $data[] = $parent;
		    return $data;
	    }
    }

    /**
     * 递归整理菜单
     * @param array $list
     * @param int $pid
     * @param $permission array 权限数组
     * @return array
     */
    private static function _RecurMenus($list = [], $pid = 0, $level = 1)
    {
        $menus = [];
        foreach ($list as $id => $one) {
            if ($one['pid'] == $pid) {
            	$one['level'] = $level;
                $menus[$id] = $one;
            }
        }
        if (!empty($list)) {
            foreach ($menus as $id => $menu) {
                $menus[$id]['children'] = self::_RecurMenus($list, $menu['menu_id'], $level + 1);
            }
        }
        return $menus;
    }
	
	/**
	 * 获取有权限的所有菜单id
	 * @return array
	 */
	public static function getPermissionId()
	{
		$ids = [];
		$allPermission = AuthAssign::getPermissionByUser(Yii::$app->user->id);
		// 获取对应的menu_id
		$allMenu = self::getAllData();
		foreach ($allMenu as $item) {
			if (in_array($item['path'], $allPermission)) {
				$ids[] = $item['menu_id'];
			}
		}
		return $ids;
	}

    /**
     * 模块的开启或者关闭时调用
     * @param $module_id string 模块id
     * @param $is_show
     * @return bool
     */
    public static function setModuleStatus($module_id, $is_show)
    {
        // 把本模块下的所有菜单状态都改掉
        // 如果状态更改为显示，那么+5，如果状态更改为不显示，那么-5; 这样可以保证在开启或者关闭的时候可以还原到原来的值
        if ($is_show == 1) {
            $sql = 'update '.self::tableName(). ' set is_show=is_show+5 where module="'.$module_id.'"';
        } else {
            $sql = 'update '.self::tableName(). ' set is_show=is_show-5 where module="'.$module_id.'"';
        }
        $res = Yii::$app->db->createCommand($sql)->execute();
        // 刷新缓存
        self::getAllData(true);
        return $res;
    }

    /**
     * 添加菜单，
     * @param $data array 菜单数据，可以使用children包含子级数据
     * @param $log bool 是否写日志
     * @return bool
     */
    public static function setMenu($data, $log = true)
    {
        if (!is_array($data)) {
            return false;
        }
        // 有两种菜单，第一种是直接在data里面写了menu_name等属性，第二种是里面又是数组
        foreach ($data as $k => $item) {
            if (is_int($k)) {
                $is_break = false;
                $menu = $item;
            } else {
                $is_break = true;
                $menu = $data;
            }

            // 如果菜单path不存在则添加
            $model = self::findOne(['path' => $menu['path']]);
            if (!$model) {
                $model = new self();
                $model->loadDefaultValues();
                $model->log_flag = $log; // 关闭写日志
                $model->setAttributes($menu);
                if (!$model->save()) {
                    return false;
                }
            }


            // 递归处理子菜单
            if (isset($menu['children'])) {
                foreach ($menu['children'] as $value) {
                    // 子菜单中如果没有定义pid，那么采用父级作为pid
                    if (!isset($value['pid'])) {
                        $value['pid'] = $model->menu_id;
                    }
	                // 如果子菜单中没有定义模块，那么采用父级的模块
	                if (!isset($value['module'])) {
		                $value['module'] = $model->module;
	                }
	                //如果菜单type类型为1, is_show置为不显示
	                if (isset($value['type']) && $value['type'] == 1) {
						$value['is_show'] = 0;
	                } else {
                        $value['is_show'] = isset($value['is_show']) ? $value['is_show'] : $model->is_show;
                    }

	                self::setMenu($value, $log);
                }
            }

            // 如果是直接数据的格式，那么退出循环
            if ($is_break) {
                break;
            }
        }

        // 刷新缓存
        self::getAllData(true);

        return true;
    }

    /**
     * 删除菜单，
     * @param $data array 菜单数据，可以使用children包含子级数据
     * @param $log bool 是否写日志
     * @return bool
     */
    public static function removeMenu($data, $log = true)
    {
        if (!is_array($data)) {
            return false;
        }
        // 有两种菜单，第一种是直接在data里面写了menu_name等属性，第二种是里面又是数组
        foreach ($data as $k => $item) {
            if (is_int($k)) {
                $is_break = false;
                $menu = $item;
            } else {
                $is_break = true;
                $menu = $data;
            }

            self::deleteAll(['path' => $menu['path']]);
            // 递归处理子菜单
            if (isset($menu['children'])) {
                foreach ($menu['children'] as $value) {
                    self::removeMenu($value, $log);
                }
            }

            // 如果是直接数据的格式，那么退出循环
            if ($is_break) {
                break;
            }
        }

        // 刷新缓存
        self::getAllData(true);

        return true;
    }

    /**
     * 获取所有的菜单
     * @param bool $refresh 是否刷新，从数据库中获取最新的数据
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public static function getAllData($refresh = false)
    {
        $cacheKey = 'main:menu:all';
        $data = Yii::$app->cache->get($cacheKey);
        if (!$data || $refresh) {
            $data = self::find()
                ->orderBy(['pid' => SORT_ASC, 'sort' => SORT_DESC, 'menu_id' => SORT_ASC])
                ->asArray()->all();
            Yii::$app->cache->set($cacheKey, $data);
        }

        return $data;
    }

	/**
	 * @inheritdoc
	 */
	public function afterFind()
	{
		parent::afterFind();
		$this->operate = self::getOperate($this->menu_id);
	}
	
	/**
	 * 获取菜单的操作列表
	 * @param int $id 父菜单ID
	 * @return array [menu_id => ['action' => action, 'name' => menu_name],……]
	 */
	public static function getOperate($id)
	{
		$list = self::getMenusByCondition(['pid' => $id, 'type' => 1]);
		
		$operate = [];
		foreach ($list as $val) {
			$operate[$val['menu_id']] = [
				'action' => substr($val['path'], strrpos($val['path'], '/') + 1),
				'name' => $val['menu_name']
			];
		}
		
		return $operate;
	}
	
	/**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
	
	    if ($this->type == 0 && is_string($this->operate)) {
		    $oldOperates = self::getOperate($this->menu_id);
		    $this->operate = Tool::paramsToArray($this->operate);//[action => name, ……]
		    
		    foreach ($oldOperates as $key => $val) {
			    if (!array_key_exists($val['action'], $this->operate)) {//原有操作不在修改后的列表中，删除原有操作
				    self::deleteAll(['menu_id' => $key]);
			    }
		    }
		    
		    foreach ($this->operate as $key => $val) {// action => name
			    if (!in_array($key, ArrayHelper::getColumn($oldOperates, 'action'))) {//修改后操作不在原有操作列表中，增加操作
				    $model = new self();
				    $model->menu_name = $val;
				    $model->pid = $this->menu_id;
				    $model->module = $this->module;
				    $model->path = substr($this->path, 0, strrpos($this->path, '/') + 1) . $key;
				    $model->type = 1;
				    $model->is_show = 0;
				    if (!$model->save()) {
					    print_r($model->errors);die;
				    };
			    }
		    }
	    }
        // 刷新缓存
        self::getAllData(true);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
	    parent::afterDelete();

	    // 删除显示的菜单后递归删除所有子级菜单
	    self::deleteMenuByPid($this->menu_id);

	    // 刷新菜单
        self::getAllData(true);
    }

    /**
     * 安装模块的菜单，提供给模块管理的安装
     * @param $module_id string 模块名称
     * @param $data array 菜单数据
     *
     * @return bool
     */
    public static function installMenuByModule($module_id, $data)
    {
        // 安装
        $res = self::setMenu($data, false);

        if ($res) {
            // 写个日志
            Yii::$app->systemOperateLog->write([
                'module' => 'main',
                'target_name' => '菜单',
                'model_class' => self::className(),
                'template' => '增加了 模块'.$module_id.' 的所有菜单',
            ]);
        }

        return $res;
    }

    /**
     * 删除模块的菜单
     * @param $module_id string 模块名称
     * @return bool
     */
    public static function deleteMenuByModule($module_id)
    {
        $res = self::deleteAll(['module' => $module_id]);

        if ($res) {
            // 写个日志
            Yii::$app->systemOperateLog->write([
                'module' => 'main',
                'target_name' => '菜单',
                'model_class' => self::className(),
                'template' => '删除了 模块'.$module_id.' 的所有菜单',
            ]);

            // 刷新缓存
            self::getAllData(true);
        }

        return true;
    }

    /**
     * 递归删除某个菜单的所有子级菜单
     * @param $pid
     */
    public static function deleteMenuByPid($pid)
    {
        $data = self::getAllData();
        foreach ($data as $item) {
            // 找到子级
            if ($item['pid'] == $pid) {
                self::deleteMenuByPid($item['menu_id']);
            }
        }
        self::deleteAll(['pid' => $pid]);
    }
}