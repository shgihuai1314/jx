<?php

namespace system\modules\main\models;

use Overtrue\Pinyin\Pinyin;
use system\core\utils\Tool;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * 应用管理的模型
 * @property string $id                     应用id
 * @property string $name                   应用名称
 * @property string $url                    应用url
 * @property string $image                  应用图标
 * @property string $content                应用内容
 * @property string $use_range              使用范围
 * @property integer $is_show               是否显示
 * @property integer $is_hot                是否热门服务，1是，0非； TODO 这个可以使用点击量等进行判断
 * @property integer $is_recommend          是否推荐服务，1是，0非
 * @property integer $sort                  排序
 * @property integer $created_at            创建时间
 * @property integer $update_at             更新时间
 * @property integer $created_by            创建人
 * @property integer $update_by             更新人
 */
class App extends \system\models\Model
{
    public $category_id;
    public $log_flag = true;
    public $log_options = [
        'target_name' => 'name',//日志目标对应的字段名，默认name
        'model_name' => '应用名称',//模型名称
        'except_field' => ['created_at', 'update_at', 'created_by', 'update_by', 'content'],
    ];
    public $convertList = [
        'use_range' => ['\system\modules\user\components\UserWithGroup', 'getNamesBySelect'],
    ];

    public static $cacheData = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_app';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {

        return parent::getRule([
            [['content', 'use_range'], 'string'],
            [['is_show', 'is_hot', 'is_recommend', 'sort', 'created_at', 'update_at', 'created_by', 'update_by'], 'integer'],
            [['name', 'url', 'image',], 'string', 'max' => 255],
            ['image', 'default', 'value' => '/static/images/app-default.png'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'ID',
            'name' => '名称',
            'category_id' => '分类',
            'url' => '应用链接',
            'image' => '图标',
            'content' => '内容',
            'use_range' => '应用范围',
            'is_show' => '是否显示',
            'is_hot' => '是否热门',
            'is_recommend' => '是否推荐',
            'sort' => '排序',
            'created_at' => '添加时间',
            'update_at' => '更新时间',
            'created_by' => '添加人',
            'update_by' => '更新人',
        ], parent::attributeLabels());
    }

    /**
     * 选择性属性列表
     * @param string $field 字段名
     * @param string $key 查找的key
     * @param bool $default 默认值(未查到结果的情况下返回)
     * @return array|bool|string
     */
    public static function getAttributesList($field = '', $key = '', $default = false)
    {
        $list = [
            'is_show' => ['1' => '是', '0' => '否'],
            'is_hot' => ['1' => '是', '0' => '否'],
            'is_recommend' => ['1' => '是', '0' => '否'],
            'category_id' => AppCategory::getNameArr(),
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * 获取文章分类
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        //return $this->hasMany(AppRelation::className(), ['app_id' => 'id'])->select(['cate_id']);
        return $this->hasMany(AppCategory::className(), ['id' => 'cate_id'])
            ->viaTable('tab_app_relation', ['app_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->id;
            }

            $this->update_at = time();
            $this->update_by = Yii::$app->user->id;

            // 如果有扩展字段，那么自动更新首字母
            if ($this->hasAttribute('extend_letter')) {
                $pinyinObj = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
                $this->extend_letter = strtoupper(substr($pinyinObj->abbr($this->name), 0, 1));
            }

            return true;
        }

        return false;
    }

    public function afterFind()
    {
        parent::afterFind();

        $this->category_id = implode(',',
            AppRelation::find()->select(['cate_id'])->where(['app_id' => $this->id])->column());
    }


    /**
     * 获取所有数据
     * @param bool $refresh
     * @return array 应用数组
     */
    public static function getAllData($refresh = false)
    {
        return self::getAllDataCache($refresh);
    }

    /**
     * 根据条件获取数据
     * @param null $condition
     * @return array
     */
    public static function getDataByCondition($condition = null)
    {
        $data = self::getAllData();
        if (!$condition) {
            return $data;
        } else {
            //如果condition是int，匹配ID；condition是string，匹配name；condition是数组，匹配字段
            $condition = is_numeric($condition) ? ['id' => $condition] : (is_string($condition) ? ['name' => $condition] : $condition);
            return Tool::get_array_by_condition($data, $condition);
        }
    }

    // 根据分类id获取所有可显示的应用
    public static function getDataByCategory($category_id)
    {
        $data = App::find()
            //->select('a.*,r.cate_id as category_id')
            ->from(App::tableName() . ' a')
            ->with('category')
            ->leftJoin(AppRelation::tableName() . ' r', 'r.app_id = a.id')
            ->where(['is_show' => 1, 'cate_id' => $category_id])
            ->asArray()
            ->all();
        return $data;
    }
}
