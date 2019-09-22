<?php

namespace system\modules\article\models;

use system\modules\main\models\ClickTotal;
use system\modules\tag\models\TagArticle;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_article".
 *
 * @property integer $id
 * @property string  $title
 * @property string  $content
 * @property integer $cate_id
 * @property integer $is_display
 * @property integer $sort
 * @property string  $author
 * @property integer $update_at
 * @property integer $update_by
 * @property integer $create_by
 * @property integer $create_at
 * @property integer $is_recommend
 * @property integer $is_del
 */
class Article extends \system\models\Model
{
    public $tags = [];          // 标签

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_article';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['title'], 'required'],
            [['cate_id', 'is_display', 'sort', 'update_at', 'update_by', 'create_by', 'create_at', 'is_del', 'is_recommend'], 'integer'],
            [['title', 'author', 'content'], 'string'],
            ['tags', 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'Id',
            'title' => '文章标题',
            'content' => '文章内容',
            'cate_id' => '文章分类',
            'is_display' => '是否显示',
            'sort' => '排序',
            'author' => '作者',
            'update_at' => '更新时间',
            'update_by' => '更新人',
            'create_by' => '创建人',
            'create_at' => '创建时间',
            'is_recommend' => '是否推荐',
            'is_del' => '是否删除',
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
            'is_del' => [
                '1' => '是',
                '0' => '否',
            ],
            'cate_id' => ArticleCategory::getNameArr(),
        ];

        return self::getAttributeValue($list, $field, $key, $default);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->create_by = Yii::$app->user->getId();
                $this->create_at = time();
            }

            $this->update_by = Yii::$app->user->getId();
            $this->update_at = time();

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->tags) {
            TagArticle::setTag($this->id, $this->tags);
        }
    }

    /**
     * 关联文章分类
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ArticleCategory::className(),  ['id' => 'cate_id']);
    }

    /**
     * 抓取内容图片
     * @param $content
     * @return array
     */
    public static function getImageFromContent($content)
    {
        preg_match_all('/<img.*?src="([^"]*)"[^>]*>/i', $content, $matches);
        $val = [];
        if ($matches) {
            foreach ($matches[1] as $key=>$value){
                if($key<3){
                    $val[] =$value;
                }
            }
        }

        return $val;
    }

    /**
     * 把内容分离成图片和文字
     * @param $content
     * @return array
     */
    public static function getImageContent($content)
    {
        preg_match_all('/<img.*?src="([^"]*)"[^>]*>/i', $content, $matches);

        $val['image'] = isset($matches[1][0]) ? $matches[1][0] : '';

        $html="/(<(?:\/img|img)[^>]*>)/i";

        $val['content'] = preg_replace($html, '', $content);

        return $val;
    }

    /**
     * 获取分类路径
     * @param $id
     * @return array
     */
    public static function getCatePath($id)
    {
        $model = self::findOne($id);

        $path = ArticleCategory::getPath($model['cate_id']);

        $list = [];
        foreach ($path as $value) {
            $category = ArticleCategory::getOneById($value);
            if (!$category) {
                continue;
            }
            $item['name'] = $category['name'];
            $item['code'] = $category['code'];
            $list[] = $item;
        }

        return $list;
    }

    /**
     * 获取文章列表
     * @param $num
     * @param $type
     * @param $cate_id
     * @return array
     */
    public static function getArticleList($num, $type, $cate_id = 0)
    {
        $query =  Article::find()
            ->select('a.id,a.title,a.content,a.cate_id,a.create_at,c.click_total')
            ->from(Article::tableName() . ' a')
            ->leftJoin(ClickTotal::tableName() . ' c', 'a.id=c.target_id and c.target_type="article"');

        if($cate_id){
            $cate_ids = ArticleCategory::getChildIds($cate_id);
            $query->where(['a.cate_id' => $cate_ids]);
        }

        $query->andWhere(['a.is_display' => 1,'a.is_del' => 0])->limit($num);

        if($type == 'hot'){
            $query->orderBy(['c.click_total' => SORT_DESC,'a.sort' => SORT_DESC,'a.create_at' => SORT_DESC]);
        }elseif ($type == 'recommend'){
            $query->orderBy(['a.is_recommend' => SORT_DESC,'a.sort' => SORT_DESC,'a.create_at' => SORT_DESC]);
        }else{
            $query->orderBy(['a.sort' => SORT_DESC,'a.create_at' => SORT_DESC]);
        }

        $article =  $query->asArray()->all();

        return $article;
    }

    /**
     * 根据文章id获取所属站点
     * @param $id
     * @return mixed
     */
    public static function getSite($id)
    {
        $article = self::findOne($id);

        return ArticleCategory::getSite($article['cate_id']);
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
}
