<?php

namespace system\modules\article\models;

use system\core\utils\Tool;
use system\modules\advert\models\AdvertCategory;
use system\modules\page\models\PageSite;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_article_category".
 *
 * @property integer $id
 * @property string $name
 * @property string $icon
 * @property integer $pid
 * @property string $path
 * @property integer $is_display
 * @property integer $sort
 * @property string $code
 * @property integer $update_at
 * @property integer $update_by
 * @property integer $create_at
 * @property integer $create_by
 */
class ArticleCategory extends \system\models\Model
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_article_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['pid', 'is_display', 'sort', 'update_at', 'update_by', 'create_at', 'create_by'], 'integer'],
            [['name', 'icon', 'path', 'code'], 'string'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '分类id',
            'name' => '分类名称',
            'icon' => '分类图标',
            'pid' => '上级分类',
            'path' => '结构路径',
            'is_display' => '是否显示',
            'sort' => '排序',
            'code' => '代码',
            'update_at' => '更新时间',
            'update_by' => '更新人',
            'create_at' => '创建时间',
            'create_by' => '创建人'
        ], parent::attributeLabels());
    }

    /**
     * 选择性属性列表
     * @param string $field 字段名
     * @param string $key 查找的key
     * @param string $default 默认值(未查到结果的情况下返回)
     * @return array|bool|string
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
        $list = [
            'is_display' => [
                '1' => '是',
                '0' => '否',
            ],
            'pid' => self::getNameArr(),
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($insert){
                $this->create_by = Yii::$app->user->getId();
                $this->create_at = time();
            }

            if ($this->pid == 0) {
                $this->path = '0-';
            } else {
                $category = self::findOne($this->pid);
                if ($category) {
                    $this->path = $category->path . $this->pid . '-';
                } else {
                    return false;
                }
            }

            $this->update_by = Yii::$app->user->getId();
            $this->update_at = time();

            return true;
        }

        return false;
    }

    private static $_allData = [];

    /**
     * 获取所有分类数据
     * @param bool $refresh 是否强制刷新数据
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAllData($refresh = true)
    {
        if (empty(self::$_allData) || $refresh) {
            self::$_allData = self::getAllDataCache($refresh);
        }

        return self::$_allData;
    }


    /**
     * 根据条件获取数据
     * @param null $condition
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDataByCondition($condition = null)
    {
        $data = self::getAllData();
        if (empty($condition)) {
            return $data;
        } else {
            //如果condition是int，匹配ID；condition是string，匹配title；condition是数组，匹配字段
            $condition = is_numeric($condition) ? ['id' => $condition] : (is_string($condition) ? ['title' => $condition] : $condition);
            return Tool::get_array_by_condition($data, $condition);
        }
    }

    /**
     * 获取文章分类列表
     * @param array|int $ids
     * @return array
     */
    public static function getNameArr($ids = null)
    {
        $list = self::getDataByCondition(empty($ids) ? null : ['id' => $ids]);

        $arr = $ids == null ? ArrayHelper::merge([0 => '-'], ArrayHelper::map($list, 'id', 'name')) : ArrayHelper::map($list, 'id', 'name');
        return $arr;
    }

    /**
     * 根据pid 返回树状结构数组
     * @param $data
     * @param $pId
     * @return array
     */
    public static function getTreeCate($data, $pId)
    {
        $tree = [];

        foreach ($data as $k => $v) {
            if ($v['pid'] == $pId) {
                $children = self::getTreeCate($data, $v['id']);
                if ($children) {
                    $v['children'] = $children;
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    /**
     * 获取某分类下的所有子级分类
     * @param int $pid
     * @param bool $is_display
     * @param array $cates
     * @return array
     */
    public static function getChildCate($pid = 0, $is_display = true, $cates = [])
    {
        $query = self::find()
            ->where(['pid' => $pid]);

        if($is_display){
            $query->andWhere(['is_display' => 1]);
        }

        $cate_info = $query->orderBy(['sort' => SORT_DESC])
            ->asArray()->all();

        if ($cate_info) {
            foreach ($cate_info as $k => $v) {
                $cates[] = $v;
                $cates = self::getChildCate($v['id'], $is_display, $cates);
            }
        }

        return $cates;
    }

    /**
     * 根据ID或ID数组获取所有下级分类ID
     * @param array|int $ids ID或ID数组
     * @param bool $includeSelf 是否包括自身
     * @return array
     */
    public static function getChildIds($ids, $includeSelf = true)
    {
        $res = self::getDataByCondition(['id' => $ids]);
        $arr = [];
        foreach ($res as $val) {
            $arr = self::find()->asArray()
                ->where(['like', 'path', $val['path'] . $val['id'] . '-'])
                ->andWhere(['!=', 'id', $val['id']])
                ->andWhere(['is_display' => 1])
                ->all();
            array_unshift($arr, $val);
        }
        $list = ArrayHelper::getColumn($arr, 'id', []);

        if ($includeSelf) {
            if (is_array($ids)) {
                $list = ArrayHelper::merge($ids, $list);
            } else {
                $list[] = $ids;
            }
        }

        return array_unique($list);
    }

    /**
     * 获取站点布局设计器分类信息
     * @param $code
     * @return array
     */
    public static function getCateByCode($code)
    {
        if($code == 'default'){
            $cate = self::find()->where(['is_display' => 1])->asArray()->all();
            return $data = self::getTree($cate,0);
        }else{
            $article_cate = PageSite::findOne(['code' => $code])->article_cate;
            $catas = self::getChildCate($article_cate);

            $catas[] = self::find()->where(['id' => $article_cate])->asArray()->one();
            $data = self::getTree($catas,0);
        }

        return $data;
    }

    /*
     *获取分类
     * */
    public static function getCategory($params)
    {
        $info = self::findOne($params['cate_id'][1]);

        $item = self::find()
            ->where(['pid' => $params['cate_id'][1]])
            ->limit($params['number'])
            ->orderBy(['sort' => SORT_DESC, 'id' => SORT_DESC])
            ->asArray()
            ->all();

        if($params['list'] == 1 || $params['list'] == 2){
            $data = [];
            foreach ($item as $k => $v) {
                $data['list'][$k]['url'] = $info['code'];
                $data['list'][$k]['name'] = $v['name'];
            }
        }

        //区分展示方式
        if($params['list'] == 1){
            $data['title'] = $info['name'];
            $data['src'] = $info->icon == 0 ? '' : \Yii::$app->systemFileInfo->get($info->icon, 'src');
        }elseif ($params['list'] == 2){
            $data['name'] = $params['title'];
            $data['src'] = $info->icon == 0 ? '' : \Yii::$app->systemFileInfo->get($info->icon, 'src');
        }elseif ($params['list'] == 3){
            //查询二级分类
            $data = [];
            $arr = [];
            foreach ($item as $k => $v) {
                $article = self::find()->where(['pid' =>$v['id']])->asArray()->one();
                $arr['url'] = $article['code'];
                $arr['name'] = $article['name'];
                $data[$k]['list'][] = $arr;
                $data[$k]['name'] = $v['name'];
            }
        }

        return $data;
    }

    /**
     * 根据id获取名称
     * @param $id
     * @return mixed
     */
    public static function getNameById($id)
    {
        $data = self::getOneById($id);
        if (!$data) {
            return '';
        }

        return $data['name'];
    }

    /**
     * @param $id
     * @return bool|null|static
     */
    public static function getOneById($id)
    {
        return self::findOne($id);
    }

    /**
     * 根据pid 返回树状结构数组
     * @param $data
     * @param $pId
     * @return array
     */
    public static function getTree($data, $pId)
    {
        $tree = [];

        foreach ($data as $k => $v) {
            $value = [];

            if ($v['pid'] == $pId) {
                $value['value'] = $v['id'];
                $value['label'] = $v['name'];
                $children = self::getTree($data, $v['id']);
                if ($children) {
                    $value['children'] = $children;
                }
                $tree[] = $value;
            }
        }
        return $tree;
    }

    /**
     * 根据ID获取分类结构
     * @param $cate_id
     * @return array
     */
    public static function getPath($cate_id)
    {
        $path = self::find()->select(['path'])->where(['id' => $cate_id])->scalar();

        if (!$path) {
            $data = ['0'];
        } else {
            $path .= $cate_id;
            $data = explode('-', $path);
        }

        return $data;

    }

    /**
     * 根据分类ID找到所属站点
     * @param $id
     * @return mixed
     */
    public static function getSite($id)
    {
        $cate = self::getPath($id);

        if(isset($cate[1])){
            $site = PageSite::findOne(['article_cate' => $cate[1]]);
        }else{
            $site = false;
        }

        return $site;
    }

    /**
     * 是否站点管理员
     * @param $id
     * @param $user_id
     * @return bool
     */
    public static function isManager($id, $user_id)
    {
        $site = self::getSite($id);

        if (!$site) {
            return false;
        }

        return in_array($user_id, explode('|',trim($site->manager, '|')));
    }

    /**
     * @param $name
     * @return bool|int
     */
    public static function createNewOne($name,$code)
    {
        $model = new self();
        $model->loadDefaultValues();
        $model->name = $name;
        $model->code = $code;
        $model->pid = 0;
        if ($model->save()) {
            return $model->id;
        } else {
            return false;
        }
    }

    /**
     * 获取分类顶级code
     * @param $code
     * @return mixed
     */
    public static function getTopCode($code)
    {
        $cate = self::findOne(['code' => $code]);

        if($cate){
            $path = $cate->path;
        }else{
            return '';
        }

        $cate_id = explode('-',$path);

        if($cate_id[1] != ''){
            $code = self::findOne($cate_id[1])->code;
        }

        return $code;
    }
}
