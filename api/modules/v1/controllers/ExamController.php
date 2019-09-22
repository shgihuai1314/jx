<?php
/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2018/9/10
 * Time: 17:42
 */

namespace Api;

use system\core\utils\Excel;
use system\core\utils\Tool;
use system\modules\course\models\CourseChapter;
use system\modules\course\models\CourseLessonTask;
use system\modules\course\models\CourseOrder;
use system\modules\course\models\CoursePlan;
use system\modules\course\models\CourseTeam;
use system\modules\course\models\StudentTask;
use system\modules\exam\models\Exam;
use system\modules\exam\models\ExamAnswer;
use system\modules\exam\models\ExamPaper;
use system\modules\exam\models\ExamQuestion;
use system\modules\exam\models\ExamQuestionCategory;
use system\modules\exam\models\ExamRecord;
use system\modules\exam\models\ExamSetScore;
use system\modules\user\models\User;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use system\core\utils\IconvHelper;

class ExamController extends BaseApiController
{
    public $notAuthAction = ['import-result'];

    /**
     * @info 创建题库
     * @method POST
     * @param string $name 题库名称 required
     * @return array ['code' => 0, 'message' => '创建成功']
     */
    public function actionCreateQuestionBank()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['name']) || !$post['name']) {
            return $this->apiReturn(false, '参数错误');
        }

        $model = new ExamQuestionCategory();
        $model->loadDefaultValues();

        if ($model->load($post, '')) {
            return $this->apiReturn($model->save(), ['创建成功', '操作失败']);
        } else {
            return $this->apiReturn(false, '无效的请求');
        }

    }

    /**
     * @info 修改题库
     * @method POST
     * @param integer $id 题库id required
     * @param string $name 题库名称
     * @return array ['code' => 0, 'message' => '修改成功']
     */
    public function actionUpdateQuestionBank()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['id'])) {
            return $this->apiReturn(false, '参数错误');
        }

        $model = ExamQuestionCategory::findOne(['id' => $post['id']]);

        if (!isset($post['name'])) {
            return $this->apiReturn(true, '操作成功', ArrayHelper::toArray($model));
        }

        if ($model->load($post, '')) {
            return $this->apiReturn($model->save(), ['修改成功', '操作失败']);
        } else {
            return $this->apiReturn(false, '无效的请求');
        }

    }

    /**
     * @info 清空题库,删除题库
     * @method POST
     * @param integer $id 题库id required
     * @param string $type 操作类型
     * @return array ['code' => 0, 'message' => '清除成功']
     */
    public function actionRemoveQuestionBank()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['ids'], $post['type'])) {
            return $this->apiReturn(false, '参数错误');
        }

        if ($post['type'] == '清空') {
            $res = ExamQuestion::updateAll(['is_delete' => 1], ['cate_id' => $post['ids']]);

            if (!$res) {
                return $this->apiReturn(false, '清空失败');
            }
        }

        //删除题库
        $result = ExamQuestionCategory::updateAll(['is_delete' => 1], ['id' => $post['ids']]);

        if (!$result) {
            return $this->apiReturn(false, '删除失败');
        }

        return $this->apiReturn(true, '操作成功');
    }

    /**
     * @info 删除题库
     * @method post
     * @param integer $ids 题库id required
     * @return array ['code' => 0, 'message' => '删除成功']
     */
    public function actionDelQuestionBank()
    {
        $post = Yii::$app->request->post();

        if (!$post['ids']) {
            return $this->apiReturn(false, '参数错误');
        }
        $row = ExamQuestionCategory::updateAll(['is_delete' => 1], ['id' => $post['ids']]);
        //删除题目
        if (!$row) {
            return $this->apiReturn(false, '删除失败');
        }

        $data = ExamQuestion::updateAll(['is_delete' => 1], ['cate_id' => $post['ids']]);

        return $this->apiReturn(true, '删除成功');

    }

    /**
     * @info 自己的题库和公共题库
     * @method get
     * @return array ['code' => 0, 'message' => '操作成功']
     */
    public function actionMyPublic()
    {
        $params = Yii::$app->request->get();

        $PublicQuestionBank = ExamQuestionCategory::find()->where(['is_question_bank' => 1, 'is_delete' => 0])->all();
        //自己题库
        $meQuestionBank = ExamQuestionCategory::find()->where(['user_id' => Yii::$app->user->id, 'is_delete' => 0])->andWhere(['NOT', ['is_question_bank' => 1]])->all();
        //所有题库的map
        $myQuestionBank = ArrayHelper::map($PublicQuestionBank, 'id', 'name') + ArrayHelper::map($meQuestionBank, 'id', 'name');

        if (!$myQuestionBank) {
            return $this->apiReturn(false, '操作失败');
        } else {
            return $this->apiReturn(true, '操作成功', $myQuestionBank);
        }

    }

    /**
     * @info 所有题库,根据type会返回不同的数据类型,不存参数type的时候返回id=>题库名称的数据结构
     * @method GET
     * @param integer $id 题库id
     * @param integer $type 数据类型
     * @param integer $isQuestionBank 数据类型
     * @return array [
     *          'code' => 0,
     *          'message' => '操作成功'
     *          'data'=>[
     *              id=>'题库名称'
     *              name: "默认题库",
     *              user_id: "用户名称",
     *              update_time: "修改时间",
     *              is_delete: "是否删除",
     *              question=>[
     *                      "id"=>'题目',
     *                      "cate_id"=>'分类id',
     *                      "title"=>"题目标题",
     *                      "question_type"=>"题目类型",
     *                      "difficulty"=>"题目难易度",
     *                      "result"=>"正确答案",
     *                      "update_time"=>"更新时间",
     *                      "is_delete"=>"是否删除",
     *                      "explain"=>"答案解析",
     *                      "option_1"=>"选项1",
     *                      "option_2"=>"选项2",
     *                      "option_3"=>"选项3",
     *                      "option_4"=>"选项4",
     *                      "option_5"=>"选项5",
     *                      "option_6"=>选项6,
     *                      "option_7"=>选项7,
     *                      "option_8"=>选项8
     *              "nums":"题目数量",
     *              "selectNum"=>单选题id,
     *              "multipleNum"=>多选题id,
     *              "judgmentNum"=>判断题id,
     *              "pagination"=>分页
     *          ]
     *      ]
     */
    public function actionGetAllBank()
    {
        $params = Yii::$app->request->get();

        $query = ExamQuestionCategory::find()
            ->orderBy(['id' => SORT_DESC])
            ->where(['is_delete' => 0]);

        if (!isset($params['isQuestionBank'])) {
            $query->andWhere(['user_id' => Yii::$app->user->id, 'is_question_bank' => 0]);
        } else {
            $query->andWhere(['is_question_bank' => 1]);
        }

        if (isset($params['name'])) {
            $query->andWhere(['like', 'name', $params['name']]);
        }

        if (isset($params['type']) && $params['type']) {

            $data = $query->with('question')->asArray()->paginate()->all();

            $pagination = ArrayHelper::remove($data, 'pagination');
            foreach ($data as $k => $v) {
                $data[$k]['nums'] = count($v['question']);
                foreach ($v['question'] as $key => $val) {
                    if ($val['question_type'] == 1) {
                        $data[$k]['selectNum'][] = $val['id'];
                    } elseif ($val['question_type'] == 2) {
                        $data[$k]['multipleNum'][] = $val['id'];
                    } else {
                        $data[$k]['judgmentNum'][] = $val['id'];
                    }
                }
            }
            return $this->apiReturn(true, 'success', ['bank' => $data, 'pagination' => $pagination]);
        } else {
            $data = ArrayHelper::map($query->all(), 'id', 'name');
            return $this->apiReturn(true, '操作成功', $data);
        }

    }

    /**
     * @info 题目列表
     * @method GET
     * @param integer $question_type 题目类型
     * @param integer $cate_id 题库id
     * @param integer $search 关键字搜索
     * @return array [
     *          'code' => 0,
     *          'message' => '操作成功',
     *          'data'=>[
     *              "topic"=>[
     *                     "id"=>'题目',
     *                    "cate_id"=>'分类id',
     *                     "title"=>"题目标题",
     *                     "question_type"=>"题目类型",
     *                     "difficulty"=>"题目难易度",
     *                     "result"=>"正确答案",
     *                     "update_time"=>"更新时间",
     *                     "is_delete"=>"是否删除",
     *                     "explain"=>"答案解析",
     *                     "option_1"=>"选项1",
     *                     "option_2"=>"选项2",
     *                     "option_3"=>"选项3",
     *                     "option_4"=>"选项4",
     *                     "option_5"=>"选项5",
     *                     "option_6"=>选项6,
     *                     "option_7"=>选项7,
     *                      "option_7"=>选项8,
     *              ]
     *             "question_bank"=>[
     *                  "id"=>"题库名称"
     *              ]
     *          ]
     *      ]
     */
    public function actionQuestionList()
    {
        $params = Yii::$app->request->get();

        //公共题库和自己的题库
        // $myQuestionBank = ExamQuestionCategory::getMapQuestionBank(true);
        //公共题库
        $PublicQuestionBank = ExamQuestionCategory::find()->where(['is_question_bank' => 1, 'is_delete' => 0])->all();
        //自己题库
        $meQuestionBank = ExamQuestionCategory::find()->where(['user_id' => Yii::$app->user->id, 'is_delete' => 0])->andWhere(['NOT', ['is_question_bank' => 1]])->all();

        //所有题库的map
        $myQuestionBank = ArrayHelper::map($PublicQuestionBank, 'id', 'name') + ArrayHelper::map($meQuestionBank, 'id', 'name');

        $query = ExamQuestion::find();

        if (isset($params['cate_id']) && $params['cate_id']) {
            $query->where(['cate_id' => $params['cate_id'], 'is_delete' => 0]);
        } else {
            $query->where(['cate_id' => array_keys(ArrayHelper::map($meQuestionBank, 'id', 'name')), 'is_delete' => 0]);
        }

        if (isset($params['question_type']) && $params['question_type']) {
            $query->andWhere(['question_type' => $params['question_type']]);
        }

        if (isset($params['search']) && $params['search']) {
            $query->andWhere(['like', 'title', $params['search']]);
        }

        //提供给作业  去掉了简答题
        if (isset($params['is_homeWork']) && $params['is_homeWork']) {
            $query->andWhere(['NOT', ['question_type' => 5]]);
        }

        $result = $query->orderBy(['id' => SORT_DESC])->asArray()->paginate(10)->all();

        $pagination = ArrayHelper::remove($result, 'pagination');

        $data = [
            'topic' => array_filter($result),
            'question_bank' => ArrayHelper::map($meQuestionBank, 'id', 'name'),
            'pagination' => $pagination
        ];
        //所有的题库
        return $this->apiReturn(true, '操作成功', $data);
    }

    /**
     * @info 试卷题目列表
     * @method POST
     * @param integer $question_type 题目类型 required
     * @param integer $cate_id 题库id
     * @param integer $search 关键字搜索
     * @return array [
     *             'code' => 0,
     *              'message' => '操作成功',
     *              'data'=>[
     *                    "topic"=>[
     *                          "id"=>'题目',
     *                          "cate_id"=>'分类id',
     *                          "title"=>"题目标题",
     *                          "question_type"=>"题目类型",
     *                          "difficulty"=>"题目难易度",
     *                          "result"=>"正确答案",
     *                          "update_time"=>"更新时间",
     *                          "is_delete"=>"是否删除",
     *                          "explain"=>"答案解析",
     *                          "option_1"=>"选项1",
     *                          "option_2"=>"选项2",
     *                          "option_3"=>"选项3",
     *                          "option_4"=>"选项4",
     *                          "option_5"=>"选项5",
     *                          "option_6"=>选项6,
     *                          "option_7"=>选项7,
     *                          "option_8"=>选项8
     *                      ],
     *               "question_bank"=>[
     *                          "id"=>"题库"
     *                      ]
     *          ]
     */
    public function actionTestQuestionList()
    {
        $params = Yii::$app->request->post();

        $query = ExamQuestion::find()->where(['question_type' => $params['question_type'], 'is_delete' => 0]);

        if (isset($params['cate_id']) && $params['cate_id']) {
            $query->andWhere(['cate_id' => $params['cate_id']]);
        } else {
            $query->andWhere(['cate_id' => array_keys(ExamQuestionCategory::getMapQuestionBank(true))]);
        }

        if (trim($params['search'])) {
            $query->andWhere(['like', 'title', trim($params['search'])]);
        }

        $result = $query->orderBy(['id' => SORT_DESC])->all();
//            ->createCommand()->getRawSql();
//        print_r($result);die;
        //->all();

        $data = [
            'topic' => array_filter($result),
            'question_bank' => ExamQuestionCategory::getMapQuestionBank(true)
        ];

        //所有的题库
        return $this->apiReturn(true, '操作成功', $data);
    }

    /**
     * @info 根据题库id和题型获取所有的类型题目
     * @method GET
     * @param integer $question_type 题目类型 required
     * @param integer $cate_id 题库id串 required
     */
    public function actionGetPaperQuestion()
    {
        $params = Yii::$app->request->get();

        if (!isset($params['cate_id'])) {
            return $this->apiReturn(false, '参数错误');
        }

        $cateId = !is_array($params['cate_id']) ? explode(',', $params['cate_id']) : $params['cate_id'];

        $query = ExamQuestion::find()
            ->where(['cate_id' => $cateId]);

        if ($params['question_type']) {
            $query->andWhere(['question_type' => $params['question_type']]);
        }

        if (trim($params['search'])) {
            $query->andWhere(['like', 'title', trim($params['search'])]);
        }
//        print_r($query->createCommand()->getRawSql());die;
        $data = $query->asArray()->paginate()->all();

        $pagination = ArrayHelper::remove($data, 'pagination');

        return $this->apiReturn(true, '操作成功', ['data' => $data, 'pagination' => $pagination]);
    }

    /**
     * @info 公共题库的题目以及非公共题库
     * @method POST
     * @return array [
     *             'code' => 0,
     *              'message' => '操作成功',
     *              'data'=>[
     *                    "data"=>[
     *                          "id"=>'题目',
     *                          "cate_id"=>'分类id',
     *                          "title"=>"题目标题",
     *                          "question_type"=>"题目类型",
     *                          "difficulty"=>"题目难易度",
     *                          "result"=>"正确答案",
     *                          "update_time"=>"更新时间",
     *                          "is_delete"=>"是否删除",
     *                          "explain"=>"答案解析",
     *                          "option_1"=>"选项1",
     *                          "option_2"=>"选项2",
     *                          "option_3"=>"选项3",
     *                          "option_4"=>"选项4",
     *                          "option_5"=>"选项5",
     *                          "option_6"=>选项6,
     *                          "option_7"=>选项7,
     *                          "option_8"=>选项8
     *                      ],
     *               "questionBank"=>[
     *                          "id"=>"题库"
     *                      ]
     *          ]
     */
    public function actionPublicData()
    {
        $bank = ExamQuestionCategory::getMapQuestionBank(true);
        $publicId = ArrayHelper::getColumn(ExamQuestionCategory::getAllData(), 'id');
        $data = ExamQuestion::find()->where(['cate_id' => $publicId, 'is_delete' => 0])->all();
        return $this->apiReturn(true, '操作成功', ['data' => $data, 'questionBank' => $bank]);
    }

    /**
     * @info 根据题目串获取题目以及根据分数设置表获取题目详情
     * @param string $ids 题目id串
     * @param string $paper_id 题目id串
     * @return array [
     *          'code' => 0,
     *          'message' => '操作成功'
     *          'data'=>[
     *              "id": 7,
     *               "cate_id"=>题库id
     *               "title"=>题目名称,
     *               "question_type"=>题目类型,
     *               "difficulty"=>题目难易度,
     *               "result"=>正确答案,
     *               "update_time"=>更新时间,
     *               "explain"=>大难解析,
     *               "option_1"=>答案1,
     *               "option_2"=>答案2,
     *               "option_3"=>答案3,
     *               "option_4"=>答案4,
     *               "option_5"=>答案5,
     *               "option_6"=>答案6,
     *               "option_7"=>答案7,
     *               "option_8"=>答案8,
     *          ]
     *      ]
     */
    public function actionGetQuestion()
    {
        $params = Yii::$app->request->get();

        if (!isset($params['ids'])) {
            return $this->apiReturn(false, '参数错误');
        }

        if (!isset($params['paper_id'])) {
            $data = ExamQuestion::find()->where(['id' => explode(',', $params['ids']), 'is_delete' => 0])->all();
            return $this->apiReturn(true, '操作成功', $data);
        }

        $data = ExamSetScore::find()
            ->where(['paper_id' => $params['paper_id'], 'qustion_id' => explode(',', $params['ids'])])
            ->with('question')
            ->asArray()
//            ->createCommand()
//            ->getRawSql();
            // print_r($data);die;
            ->all();


        return $this->apiReturn(true, '操作成功', $data);

    }

    /**
     * @info 添加题目
     * @method POST
     * @params integer $cate_id 所属题库的id required,
     * @params string $title 试题标题 required,
     * @params integer $question_type 题目类型1:单选;2:多选;3:判断;4:填空;5:问答 required,
     * @params integer $result 正确答案 required,
     * @params string $explain 答案解释
     * @params string difficulty 题目难易度 required,
     * @params string $option_1 答案1 required
     * @params string $option_2 答案2
     * @params string $option_3 答案3
     * @params string $option_4 答案4
     * @params string $option_5 答案5
     * @params string $option_6 答案6
     * @params string $option_7 答案7
     * @params string $option_8 答案8
     * @return array ['code' => 0, 'message' => '添加成功']
     */
    public function actionImportQuestion()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['cate_id'], $post['title'], $post['question_type'])) {
            return $this->apiReturn(false, '题目标题，题库，题目类型可能不存在');
        }


        if (!$post['cate_id'] || !$post['title'] || !$post['question_type']) {
            return $this->apiReturn(false, '题目标题，题库，题目类型不能为空');
        }

        //问答题不需要答案
        if ($post['question_type'] != 5) {
            if (!isset($post['result']) || !$post['result']) {
                return $this->apiReturn(false, '请勾选正确答案');
            }
        }

        if (is_array($post['result'])) {
            sort($post['result']);
            $post['result'] = implode(',', $post['result']);
        }

        if ($post['result'] > 8) {
            return $this->apiReturn(false, '答案不存在');
        }

        $model = new ExamQuestion();
        $model->loadDefaultValues();

        if ($model->load($post, '')) {
            return $this->apiReturn($model->save(), ['添加成功', '操作失败']);
        } else {
            return $this->apiReturn(false, '无效的请求');
        }

    }

    /**
     * @info 批量添加题目
     * @method POST
     * @params integer $cate_id 题库id
     * @params string $src 文件路径
     * @return array ['code' => 0, 'message' => '添加成功']
     */
    public function actionBatchImportQuestion()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['cate_id'], $post['src'])) {
            return $this->apiReturn(false, '参数错误');
        }

        $fileName = Yii::getAlias('@webroot') . '/upload' . explode('upload', $post['src'])[1];
//        //获取表格里面的内容
        $arr = Excel::set_file($fileName);
        $execl_data = array_splice($arr[0], 1);


        foreach ($execl_data as $v) {
            $question_model = new ExamQuestion();
            $question_model->is_delete = 0;
            $question_model->cate_id = $post['cate_id'];
            $question_model->question_type = $v[0];

            //没有这种题型,或者题目难易度，直接跳过
            if (!in_array($v[0], [1, 2, 3, 4, 5]) || !in_array($v[2], [1, 2, 3])) {
                continue;
            }

            $question_model->title = $v[1];
            $question_model->difficulty = $v[2];
            $question_model->result = $v[3];
            $question_model->explain = empty($v[4]) ? '' : $v[4];

            if ($v[0] == 3) {
                $question_model->option_1 = empty($v[5]) ? '是' : $v[5];
                $question_model->option_2 = empty($v[6]) ? '否' : $v[6];
            } else {
                $question_model->option_1 = $v[5];
                $question_model->option_2 = $v[6];
            }
            $question_model->option_3 = $v[7];
            $question_model->option_4 = $v[8];
            $question_model->option_5 = $v[9];
            $question_model->option_6 = $v[10];
            $question_model->save();
        }

        return $this->apiReturn(true, '导入成功');
    }

    /**
     * @info 从公共题库导入
     * @method POST
     * @params integer $publicQuestionId 公共题库id
     * @params integer $privateQuestionId 私人题库id
     */
    public function actionFromPublicQuestionImport()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['publicQuestionId'], $post['privateQuestionId'])) {
            return $this->apiReturn(false, '参数错误');
        }

        $questionResult = ExamQuestion::find()
            ->where(['cate_id' => $post['publicQuestionId'], 'is_delete' => 0])
            ->asArray()
            ->all();

        foreach ($questionResult as $k => $v) {
            $question_model = new ExamQuestion();
            $question_model->is_delete = 0;
            $question_model->cate_id = $post['privateQuestionId'];
            $question_model->title = $v['title'];
            $question_model->question_type = $v['question_type'];
            $question_model->difficulty = $v['difficulty'];
            $question_model->result = $v['result'];
            $question_model->update_time = time();
            $question_model->explain = $v['explain'];
            $question_model->option_1 = $v['option_1'];
            $question_model->option_2 = $v['option_2'];
            $question_model->option_3 = $v['option_3'];
            $question_model->option_4 = $v['option_4'];
            $question_model->option_5 = $v['option_5'];
            $question_model->option_6 = $v['option_6'];
            $question_model->option_7 = $v['option_7'];
            $question_model->option_8 = $v['option_8'];
            $question_model->save();
            if (!$question_model->save()) {
                $question_model->getErrors();
                die;
            }
        }

        return $this->apiReturn(true, '导入成功');
    }

    /**
     * @info 公共题库导入
     * @method POST
     * @params integer $questionId 题目id
     * @params integer $publicQuestionId 公共题库id
     */
    public function actionPublicQuestionImport()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['questionId'], $post['publicQuestionId'])) {
            return $this->apiReturn(false, '参数错误');
        }

        $questionResult = ExamQuestion::find()
            ->where(['id' => $post['questionId'], 'is_delete' => 0])
            ->asArray()
            ->all();

        foreach ($questionResult as $k => $v) {
            $question_model = new ExamQuestion();
            $question_model->is_delete = 0;
            $question_model->cate_id = $post['publicQuestionId'];
            $question_model->title = $v['title'];
            $question_model->question_type = $v['question_type'];
            $question_model->difficulty = $v['difficulty'];
            $question_model->result = $v['result'];
            $question_model->update_time = time();
            $question_model->explain = $v['explain'];
            $question_model->option_1 = $v['option_1'];
            $question_model->option_2 = $v['option_2'];
            $question_model->option_3 = $v['option_3'];
            $question_model->option_4 = $v['option_4'];
            $question_model->option_5 = $v['option_5'];
            $question_model->option_6 = $v['option_6'];
            $question_model->option_7 = $v['option_7'];
            $question_model->option_8 = $v['option_8'];
            $question_model->save();
            if (!$question_model->save()) {
                $question_model->getErrors();
                die;
            }
        }

        return $this->apiReturn(true, '导入成功');

    }

    /**
     * @info 复制公共题目数据
     * @method POST
     * @params integer $cate_id 题库id
     * @params string $sourceData 源数据
     * @return array ['code' => 0, 'message' => '添加成功']
     */
    public function actionCopyQuestion()
    {
        $post = Yii::$app->request->post();

        $keys = (new ExamQuestion())->attributes();

        array_shift($keys);

        if (!isset($post['cate_id'], $post['sourceData']) || !$post['cate_id']) {
            return $this->apiReturn(false, '参数错误');
        }

        $data = [];
        foreach ($post['sourceData'] as $k => $v) {
            $result = ExamQuestion::getQuestionDetail($v);
            $data[] = [
                $post['cate_id'],
                $result->title,
                $result->question_type,
                $result->difficulty,
                $result->result,
                $result->update_time,
                $result->is_delete,
                $result->explain,
                $result->option_1,
                $result->option_2,
                $result->option_3,
                $result->option_4,
                $result->option_5,
                $result->option_6,
                $result->option_7,
                $result->option_8,
            ];
        }


        $res = Yii::$app->db->createCommand()->batchInsert('tab_exam_question', $keys, $data)->execute();

        return $this->apiReturn($res, ['导入成功', '导入失败']);
    }

    /**
     * @info 修改题目
     * @method POST
     * @params integer $question_id 题目id required,
     * @params integer $cate_id 所属题库的id required,
     * @params string $title 试题标题 required,
     * @params integer $question_type 题目类型1:单选;2:多选;3:判断,4:填空;5:问答 required,
     * @params integer $result 正确答案 required,
     * @params string $explain 答案解释 required,
     * @params string difficulty 题目难易度 required,
     * @params string $option_1 答案1
     * @params string $option_2 答案2
     * @params string $option_3 答案3
     * @params string $option_4 答案4
     * @params string $option_5 答案5
     * @params string $option_6 答案6
     * @params string $option_7 答案7
     * @params string $option_8 答案8
     * @return array ['code' => 0, 'message' => '修改成功']
     */
    public function actionUpdateQuestion()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['question_id'], $post['cate_id'], $post['title'], $post['question_type'])) {
            return $this->apiReturn(false, '参数错误');
        }

        //问答题不需要答案
        if ($post['question_type'] != 5) {
            if (!isset($post['result']) || !$post['result']) {
                return $this->apiReturn(false, '请勾选正确答案');
            }

            if (is_array($post['result'])) {
                sort($post['result']);
                $post['result'] = implode(',', $post['result']);
            }

            if ($post['result'] > 8) {
                return $this->apiReturn(false, '答案不存在');
            }
        }


        $model = ExamQuestion::findOne(['id' => $post['question_id']]);

        if ($model->load($post, '')) {
            return $this->apiReturn($model->save(), ['修改成功', '修改失败']);
        } else {
            return $this->apiReturn(false, '无效的请求');
        }

    }

    /**
     * @info 预览题目
     * @method GET
     * @params integer $id 题目id required,
     * @return array
     * [
     *          'code' => 0,
     *          'message' => '操作成功'
     *          'data'=>[
     *                  "title"=>'题目名称',
     *                  "question_type"=>'题目类型',
     *                  "result"=>'正确答案',
     *                  "option_1"=>'答案1',
     *                  "option_2"=>'答案2',
     *                  "option_3"=>'答案3',
     *                  "option_4"=>'答案4',
     *                  "option_5"=>'答案5',
     *                  "option_6"=>'答案6',
     *                  "option_7"=>'答案7',
     *                  "option_8"=>'答案8',
     *                  "difficulty"=>'题目难易度',
     *                  "explain"=>'答案分析'
     *        ]
     * ]
     */
    public function actionPreview()
    {
        $id = Yii::$app->request->get('id');

        if (!$id) {
            return $this->apiReturn(false, '参数错误');
        }

        $model = ExamQuestion::find()
            ->search(['id'])
            ->asArray()
            ->one();

        if ($model) {
            return $this->apiReturn(true, '操作成功', array_filter(ArrayHelper::toArray($model)));
        }

        return $this->apiReturn(false, '不存在此试题');
    }

    /**
     * @info 批量和单个删除题目
     * @method post
     * @params integer $ids 题目id required,
     * @return array ['code' => 0, 'message' => '删除成功']
     */
    public function actionBatch()
    {
        $params = Yii::$app->request->post();
        if (!isset($params['ids'])) {
            return $this->apiReturn(false, '参数错误');
        }

        $data = ExamQuestion::BatchDel($params['ids']);

        return $this->apiReturn($data, ['删除成功', '操作失败']);
    }

    /**
     * todo 弃用
     * @info 固定选题题目,题库展示
     * @method POST
     * @params sting $questionBankId 题库的id  默认情况下显示所有题库的题目
     * @return array [
     *          'code' => 0,
     *          'message' => '操作成功'，
     *          'data'=>[
     *              'select'=>'单选题',
     *              'multiple'=>'判断题',
     *              'judgment'=>多选题
     *          ]
     */
    public function actionShowQuestion()
    {
        //查询题库
        $questionBank = ExamQuestionCategory::find()->select(['name', 'id'])->asArray()->all();

        $id = Yii::$app->request->get('id', $questionBank[0]['id']);

        if (!isset($id)) {
            return $this->apiReturn(false, '参数错误');
        }

        //题库
        $data['questionBank'] = $questionBank;
        //单选题
        $data['select'] = Tool::get_array_by_condition(ExamQuestion::getAll(), ['question_type' => 1, 'cate_id' => $id]);
        //多选
        $data['multiple'] = Tool::get_array_by_condition(ExamQuestion::getAll(), ['question_type' => 2, 'cate_id' => $id]);
        //判断
        $data['judgment'] = Tool::get_array_by_condition(ExamQuestion::getAll(), ['question_type' => 3, 'cate_id' => $id]);

        return $this->apiReturn(true, '操作成功', $data);
    }

    /**
     * @info 生成试卷
     * @method POST
     * @params string $paper_name 试卷名称 required,
     * @params string $paper_describle 试卷试卷说明
     * @params integer $select_question_type '选题方式，0:完全随机;1:手动选题;2:随机一次 required,
     * @params string  $customData 题目,如果手动选题就必填
     * @params string  $question_type_id 题库类别id串 required
     * @params integer $select_num 单选题个数，
     * @params integer $select_fraction 单选题分数，有单选题题必填,
     * @params integer $multiple_choice_num 多选题个数,
     * @params integer $multiple_choice_fraction 多选题分数,有多选题必填
     * @params integer $judgment_num 判断题个数,
     * @params integer $judgment_fraction 判断题分数,有判断题必填,
     * @params integer $gap_filling_num 填空题个数,
     * @params integer $gap_filling_fraction 填空题分数
     * @params integer $essay_question_num 问答题个数,
     * @params integer $essay_question_fraction 问答题分数,有判断题必填,
     * @return array ['code' => 0, 'message' => '试卷创建成功']
     */
    public function actionCreatePaper()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['paper_name'], $post['question_type_id']) || !$post['paper_name'] || !$post['question_type_id']) {
            return $this->apiReturn(false, '缺少参数，可能参数试卷名称,题库');
        }

        //识别选题方式，题目类型
        $handleResult = $this->handle($post);

        if (isset($handleResult['code']) == 1) {
            return $handleResult;
        }

        //添加数据
        $model = new ExamPaper();
        $model->loadDefaultValues();


        if ($model->load($post, '')) {
            return $this->apiReturn($model->save(), ['试卷创建成功', $model->getErrors()]);
        } else {
            return $this->apiReturn(false, '无效的请求');
        }

    }

    /**
     * @info 修改试卷
     * @method POST
     * @method integer $id 试卷id require
     * @params string  $paper_name 试卷名称 required,
     * @params integer $select_question_type '选题方式，0:完全随机;1:手动选题;2:随机一次 required,
     * @params string  $customData 手动添加的题目数据，如果修改的模式是手动选题或者是随即一次的就必填 required
     * @params string  $paper_describle 试卷试卷说明,
     * @params integer $select_num 单选题个数,针对完全随机模式的修改，
     * @params integer $select_fraction 单选题分数，有单选题题必填,对完全随机模式的修改
     * @params integer $multiple_choice_num 多选题个数,对完全随机模式的修改
     * @params integer $multiple_choice_fraction 多选题分数,有多选题必填,对完全随机模式的修改
     * @params integer $judgment_num 判断题个数,对完全随机模式的修改
     * @params integer $judgment_fraction 判断题分数,有判断题必填,对完全随机模式的修改
     * @params integer $gap_filling_num 填空题个数,对完全随机模式的修改
     * @params integer $gap_filling_fraction 填空题分数,对完全随机模式的修改
     * @params integer $essay_question_num 问答题个数,对完全随机模式的修改
     * @params integer $essay_question_fraction 问答题分数,有判断题必填,对完全随机模式的修改
     * @return array ['code' => 0, 'message' => '试卷修改成功']
     */
    public function actionUpdatePaper()
    {
        $post = Yii::$app->request->post();
        if (!isset($post['status'])) {
            if (!isset($post['id'], $post['paper_name'], $post['select_question_type'])) {
                return $this->apiReturn(false, '参数错误');
            }

            if ($post['select_question_type'] == 0) {
                $handleResult = $this->handle($post);
                if (isset($handleResult['code']) == 1) {
                    return $handleResult;
                }

            } else {
                if (!isset($post['customData']) || !$post['customData']) {
                    return $this->apiReturn(false, '题目不能为空');
                }
            }
        }

        //修改数据
        $model = ExamPaper::findOne(['id' => $post['id']]);

        if ($model->load($post, '')) {
            return $this->apiReturn($model->save(), ['试卷修改成功', '试卷修改失败']);
        } else {
            return $this->apiReturn(false, '无效的请求');
        }

    }

    /**
     * @info 删除试卷
     * @method POST
     * @params integer $id 试卷id required,
     * @return array ['code' => 0, 'message' => '删除成功']
     */
    public function actionDelPaper()
    {
        $post = Yii::$app->request->post();


        if (!isset($post['id'])) {
            return $this->apiReturn(false, '参数错误');
        }

        $res = ExamPaper::BatchDel(['id' => $post['id']]);

        return $this->apiReturn($res, ['删除成功', '删除失败']);
    }

    /**
     * @info 试卷列表
     * @method GET
     * @params string $id 试卷id,
     * @params string $name 试卷名称,
     * @params string $status 试卷发布状态
     * @params boolean $data_map 数据结构,为真的时候会返回map的数据结构，否则返回的是一个试卷列表
     * @return array [
     *             'code' => 0,
     *              'message' => '删除成功',
     *               'data'=>[
     *                      'paper'=>[
     *                         "id": "1",
     *                           "paper_name"=>"试卷名称",
     *                           "paper_describle"=>"试卷描述",
     *                           "select_question_type"=>"试卷模式",
     *                           "setting_info"=>"试卷题目",
     *                           "select_num"=>"单选题数量",
     *                           "select_fraction"=>"单选题分数",
     *                           "multiple_choice_num"=>"多选题数量",
     *                           "multiple_choice_fraction"=>"多选题分数",
     *                           "judgment_num"=>"判断题数量",
     *                           "judgment_fraction"=>"判断题分数",
     *                           "papers_num"=>"试卷数量",
     *                           "gap_filling_num"=>'填空题数量',
     *                           "gap_filling_fraction"=>'填空题分数',
     *                           "essay_question_num"=>'问答题数量',
     *                           "essay_question_fraction"=>'问答题分数',
     *                           "regression"=>'多选题漏选分数',
     *                           "question_type_id"=>"题库类别",
     *                           "time_limit"=>'考试时间限制',
     *                           "score_sum"=>"试卷分数",
     *                           "paper_difficulty"=>"试卷难易度",
     *                           "update_time"=>"更新时间",
     *                           "update_user"=>"更信人",
     *                           "status"=>"状态"
     *                      ],
     *                 pagination=>分页
     *              ]
     *
     *          ]
     *
     */
    public function actionPaperList()
    {
        $condition = [
            'id',
            'status',
            'search' => [
                'or',
                ['like', 'paper_name', ':val'],
            ],
        ];

        //所有的试卷
        $query = ExamPaper::find()
            ->search($condition)
            ->andWhere(['update_user' => Yii::$app->user->id])
            ->asArray();
//            ->all();

        if (Yii::$app->request->get('data_map')) {
            $data = $query->andWhere(['status' => 1])->all();
            return $this->apiReturn(true, '操作成功', ArrayHelper::map($data, 'id', 'paper_name'));
        }

        $data = $query->paginate()->all();

        $pagination = ArrayHelper::remove($data, 'pagination');

        return $this->apiReturn(true, '操作成功', ['paper' => $data, 'pagination' => $pagination]);
    }

    /**
     * @info 预览试卷
     * @method GET
     * @params integer $paperId 试卷id
     * @return array [
     *      'code' => 0,
     *      'message' => 'succeed'
     *      'data'=>[
     *              "paper_name"=>"试卷名称",
     *              "paper_describle"=>"试卷描述",
     *              "update_time"=>"最后更新时间",
     *              "update_user"=>"更新人",
     *              "select"=>[
     *                      "id"=>"题目id",
     *                       "cate_id"=>"题库id",
     *                       "title"=>"题目名称",
     *                       "question_type"=>"题目类型",
     *                       "difficulty"=>"试卷难易度",
     *                       "result"=>正确答案,
     *                       "update_time"=>更新时间,
     *                       "is_delete"=>是否删除,
     *                       "explain"=>题目解析,
     *                       "option_1"=>"答案1",
     *                       "option_2"=>"答案2",
     *                       "option_3"=>"答案3",
     *                       "option_4"=>"答案4",
     *                       "option_5"=>"答案5",
     *                       "option_6"=>"答案6",
     *                       "option_7"=>"答案7",
     *                       "option_8"=>"答案8",
     *                       "score":"题目分数"
     *              ]
     *              "judgment"=>[],//判断题
     *              "multiple"=>[],//多选题
     *         ]
     * ]
     */
    public function actionPreviewPaper()
    {
        $id = Yii::$app->request->get('paperId');

        if (!$id) {
            return $this->apiReturn(false, '试卷不存在');
        }

        $data = ExamPaper::getPaperMsg($id, false);

        if (!$data) {
            return $this->apiReturn(false, '没有数据');
        }

        return $this->apiReturn(true, '操作成功', $data);
    }

    /**
     * @info 新增考试
     * @method POST
     * @params integer $paper_id 试卷id require
     * @params string $name 考试名称 require
     * @params integer $exam_number 考试次数，默认不受限制
     * @params string $retake_interval 重考间隔，默认没有间隔
     * @return array ['code' => 0, 'message' => '添加考试成功']
     */
    public function actionCreateExam()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['paper_id'], $post['name'])) {
            return $this->apiReturn(false, '参数错误');
        }

        $model = new Exam();
        $model->loadDefaultValues();

        if ($model->load($post, '')) {
            return $this->apiReturn($model->save(), ['添加考试成功', '添加考试失败']);
        } else {
            return $this->apiReturn(false, '无效的请求');
        }

    }

    /**
     * @info 修改考试
     * @method POST
     * @params $id integer 考试id require
     * @params integer $category_id 考试组的id
     * @params string $name 考试名称
     * @params string $describle 考试的描述
     * @params integer $start_time 考试的开始时间
     * @params integer $end_time 考试的结束时间 r
     * @params string $files 考试资料
     * @return array ['code' => 0, 'message' => '修改考试成功']
     */
    public function actionUpdateExam()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['id'])) {
            return $this->apiReturn(false, '考试不存在');
        }
        //修改数据
        $model = Exam::findOne(['id' => $post['id']]);

        if ($model->load($post, '')) {
            return $this->apiReturn($model->save(), ['修改考试成功', '修改考试失败']);
        } else {
            return $this->apiReturn(false, '无效的请求');
        }

    }

    /**
     * @info 删除考试
     * @method GET
     * @params $id integer 考试id require
     * @return array ['code' => 0, 'message' => '删除考试成功']
     */
    public function actionDelExam()
    {
        $id = Yii::$app->request->get('id');

        if (!$id) {
            return $this->apiReturn(false, '考试不存在');
        }

        $model = Exam::findOne($id);

        return $this->apiReturn($model->delete(), ['删除考试成功', '删除考试失败']);

    }

    /**
     * @info 开始考试
     * @method GET
     * $params $exam_id 考试id require
     * @return array [
     *      'code' => 0,
     *      'message' => 'succeed'
     *      'data'=>[
     *              "paper_name"=>"试卷名称",
     *              "paper_describle"=>"试卷描述",
     *              "update_time"=>"最后更新时间",
     *              "update_user"=>"更新人",
     *              "select"=>[
     *                      "id"=>"题目id",
     *                       "cate_id"=>"题库id",
     *                       "title"=>"题目名称",
     *                       "question_type"=>"题目类型",
     *                       "difficulty"=>"试卷难易度",
     *                       "result"=>正确答案,
     *                       "update_time"=>更新时间,
     *                       "is_delete"=>是否删除,
     *                       "explain"=>题目解析,
     *                       "option_1"=>"答案1",
     *                       "option_2"=>"答案2",
     *                       "option_3"=>"答案3",
     *                       "option_4"=>"答案4",
     *                       "option_5"=>"答案5",
     *                       "option_6"=>"答案6",
     *                       "option_7"=>"答案7",
     *                       "option_8"=>"答案8",
     *                       "score":"题目分数"
     *              ]
     *              "judgment"=>[],//判断题
     *              "multiple"=>[],//多选题
     *         ]
     */
    public function actionBeginExam()
    {
        $exam_id = Yii::$app->request->get('exam_id');

        if (!$exam_id) {
            return $this->apiReturn(false, '缺少考试id');
        }

        $examIds = ArrayHelper::getColumn(Exam::getAllData(), 'id');

        if (!Exam::getAllData() || !in_array($exam_id, $examIds)) {
            return $this->apiReturn(false, '考试不存在');
        }

        //获取考试信息
        $examMsg = Exam::getExamMsg($exam_id);

        if (isset($examMsg['code']) && $examMsg['code'] == 1) {
            return $this->apiReturn(false, $examMsg['msg']);
        }

        $questionIds = isset($examMsg['questionIds']) ? $examMsg['questionIds'] : false;

        //获取试卷信息
        $data = ExamPaper::getPaperMsg($examMsg['paper_id'], $questionIds);

        //添加考试记录id
        $data['record_id'] = $examMsg['record_id'];

        //添加考试时长，todo 初始化考试时间
//print_r($examMsg);die;
        if (isset($examMsg['is_first']) && $examMsg['is_first'] == 1) {
            $data['duration'] = Exam::find()->where(['id' => $exam_id])->one()->duration;
        } else {
            $data['duration'] = ExamRecord::findOne(['id' => $examMsg['record_id']])->answer_duration;
        }

        return $this->apiReturn(true, '操作成功', $data);
    }

    /**
     * @info 更新考试时间
     * @params $record_id integer 考试记录record_id require
     * @params $duration integer 考试记录duration require
     * @return array ['code' => 0, 'message' => '考试时间更新成功']
     */
    public function actionUpdateExamTime()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['record_id'], $post['duration'])) {
            return $this->apiReturn(false, '缺少参数');
        }

        $model = ExamRecord::findOne(['id' => $post['record_id']]);

        if (!$model) {
            return $this->apiReturn(false, '没有考试记录');
        }

        //考试时间
        $model->answer_duration = (int)$post['duration'];

        if (!$model->save()) {
            return $this->apiReturn(false, '考试时间更新失败');
        }

        return $this->apiReturn(false, '考试时间更新成功');

    }

    /**
     * @info 有权限批阅的试卷，批阅统计和考试记录
     * @method POST
     * @params $integer 考试id require
     * @return array [
     *              'code' => 0,
     *              'message' => '操作成功',
     *              'data'=>[
     *                  "id"=> "1",
     *                   "name"=> "考试名称",
     *                   "describe"=> "考试描述",
     *                   "start_time"=> "开始时间",
     *                   "end_time"=>"结束时间",
     *                   "exam_info"=> "考试题目串",
     *                   "sort"=>"排序",
     *                   "paper_id"=>"试卷id",
     *                   "lesson_id"=>"课程id",
     *                   "exam_number"=>"考试次数",
     *                   "duration"=>"时长",
     *                   "update_time"=>"更新时间",
     *                   "retake_interval"=>"次数",
     *                   "exam_score"=>"分数",
     *                   "lesson"=>[],//课程
     *                   "record"=>[],//考试记录
     *                   "course"=>[],//课程
     *                   "plan"=>[],//计划
     *                   "submitNum"=>[],//已提交
     *                   "notRead"=>[],//未批阅
     *              ]
     *          ]
     */
    public function actionCanRead()
    {
        $params = Yii::$app->request->get();

        $query = Exam::find();

        $lessonId = CourseTeam::getlensson(Yii::$app->user->id, 'exam_review');

        if (!isset($params['id'])) {
            $list=isset($params['search'])? Exam::getRead($params['search']):Exam::getRead();
            //分页
            $pageSize = Yii::$app->systemConfig->getValue('LIST_ROWS', 13);

            $pagination = new Pagination([
                'defaultPageSize' => $pageSize,
                'totalCount' => count($list),
            ]);

            $data = array_slice($list, $pagination->offset, $pagination->limit);

            return $this->apiReturn(true, '操作成功', ['data' => $data, 'pagination' => $pagination]);

        } else {
            //查询考试的map数据
            $examMap = $query->where(['lesson_id' => $lessonId])->asArray()->all();
            //查询某一条考试数据
            $oneExamData = $query->where(['id' => $params['id']])->with('record')->asArray()->one();
            $oneExamData['countNum'] = count($oneExamData['record']);

            foreach ($oneExamData['record'] as $k => $v) {
                if ($v['end_time']) {
                    $oneExamData['submitNum'][] = $v['id'];//提交人数
                }

                if ($v['remark_status'] == 1) {
                    $oneExamData['read'][] = $v['id'];//已批阅
                } else {
                    $oneExamData['notRead'][] = $v['id'];//未批阅
                }
            }

            //用户数据
            $allUser = User::getAllUser();
            //关联的所有的考试数据
            $records = ExamRecord::find()
                ->where(['exam_id' => $params['id']]);
            if ($params['status'] == 0) {
                $records->andWhere(['remark_status' => 0]);
            } elseif ($params['status'] == 1) {
                $records->andWhere(['remark_status' => 1]);
            } else {
                $records->andWhere(['NOT', ['end_time' => '']]);
            }

            $recordData = $records->with('answer')
                ->asArray()
                ->paginate()
                ->all();

            //考试记录分页
            $pagination = ArrayHelper::remove($recordData, 'pagination');

            if (!$recordData) {

                $data = [
                    'examMap' => ArrayHelper::map($examMap, 'id', 'name'),//考试的map数据
                    'oneExamData' => $oneExamData,//一条考试数据
                    'allUser' => $allUser,//所有的用户
                    'pagination' => $pagination,//考试记录分页
                    'recordData' => $recordData,//所有的考试记录
                ];

                return $this->apiReturn(true, '操作成功', $data);
            }

            $objectivesScore = [];
            foreach ($recordData as $k => $v) {
                $recordData[$k]['time'] = Tool::showTime($v['start_time']);//多少分钟之前
                $recordData[$k]['sumScore'] = $v['score'];//总分
                foreach ($v['answer'] as $key => $value) {
                    $recordData[$k]['answer'][$key]['remarkTime'] = isset($value['remark_time']) ? Tool::showTime(isset($value['remark_time'])) : 0;//点评多少分钟之前
                    if ($value['question']['question_type'] == 5) {
                        $objectivesScore[] = $value['score'];
                    }
                }

                $recordData[$k]['objectives'] = array_sum($objectivesScore);//客观题分数

                $recordData[$k]['subjectivity'] = $recordData[$k]['sumScore'] - array_sum($objectivesScore);//主观题分数
                $recordData[$k]['sumPeople'] = count($recordData);//主观题分数
            }

            //是否存在考试记录id，不存在默认使用第一个
            if (!isset($params['record_id'])) {
                $res = ExamRecord::MyExamRecord($recordData[0]['id']);
            } else {
                $res = ExamRecord::MyExamRecord($params['record_id']);
            }

            //以题型分组
            $record = CourseOrder::array_group_by($res['data'], 'question_type');

            //客观题
            $question['objective'] = isset($record[5]) ? $record[5] : [];

            //主观题
            $question['subjectivity'] = ArrayHelper::merge(isset($record[1]) ? $record[1] : [], isset($record[2]) ? $record[2] : []);

            $data = [
                'examMap' => ArrayHelper::map($examMap, 'id', 'name'),//考试的map数据
                'oneExamData' => $oneExamData,//一条考试数据
                'recordData' => $recordData,//所有的考试记录
                'pagination' => $pagination,//考试记录分页
                'allUser' => $allUser,//所有的用户
                'question' => ['question' => $question, 'record' => $res['record']],//所有的题目
            ];

            return $this->apiReturn(true, '操作成功', $data);
        }

    }

    /**
     * @info 点评
     * @method GET
     * @params $id integer 记录record_id require
     * @params $id integer 评语remark require
     * @params $id integer 题目question_id require
     * @return array ['code' => 0, 'message' => '删除考试成功']
     */
    public function actionComment()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['record_id'], $post['remark'], $post['question_id'])) {
            return $this->apiReturn(false, '参数缺失');
        }

        $model = ExamAnswer::find()->where(['exam_record_id' => $post['record_id'], 'question_id' => $post['question_id']])->one();

        if (!$model) {
            return $this->apiReturn(false, '数据缺失');
        }

        $model->remark = $post['remark'];
        $model->remark_time = time();
        $model->remark_user = Yii::$app->user->id;

        return $this->apiReturn($model->save(), ['操作成功', '操作失败']);
    }

    /**
     * @info 打分
     * @method post
     * @params $id integer 记录record_id require
     * @params $id integer 评语score require
     * @params $id integer 题目question_id require
     * @return array ['code' => 0, 'message' => '删除考试成功']
     */
    public function actionScore()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['record_id'], $post['score'], $post['question_id'])) {
            return $this->apiReturn(false, '参数缺失');
        }

        if (!$post['score']) {
            return $this->apiReturn(false, '分数不能为空');
        }

        $model = ExamAnswer::find()->where(['exam_record_id' => $post['record_id'], 'question_id' => $post['question_id']])->one();

        if (!$model) {
            return $this->apiReturn(false, '数据缺失');
        }

        $model->score = $post['score'];
//        $model->score=isset($post['score'])?$post['score']:'';
        return $this->apiReturn($model->save(), ['评分成功', '评分失败'], ArrayHelper::toArray($model));
    }

    /**
     * @info 考试结果各种数据展示
     * @method POST
     * @params $integer 考试记录id require
     * @return array [
     *          'code' => 0,
     *          'message' => '操作成功'
     *          'data'=>[
     *              'data'=>[
     *                         [
     *                          "id"=>'题目',
     *                          "cate_id"=>'分类id',
     *                          "title"=>"题目标题",
     *                          "question_type"=>"题目类型"
     *                          "difficulty"=>"题目难易度",
     *                          "result"=>"正确答案",
     *                          "update_time"=>"更新时间",
     *                          "is_delete"=>"是否删除",
     *                          "explain"=>"答案解析",
     *                          "option_1"=>"选项1",
     *                          "option_2"=>"选项2",
     *                          "option_3"=>"选项3",
     *                          "option_4"=>"选项4",
     *                          "option_5"=>"选项5",
     *                          "option_6"=>选项6,
     *                          "option_7"=>选项7,
     *                          "option_8"=>选项8
     *                       ]
     *                   //题型数据统计
     *                  "statistics"=>[
     *                            "select"=>[],
     *                             "multiple"=>[],
     *                             "judgment"=>[]
     *                   ],
     *                   "record"=>[
     *                           "id"=>"记录id",
     *                           "exam_id"=>"考试id",
     *                           "user_id"=>"用户",
     *                           "start_time"=>"开始时间",
     *                           "end_time"=>"结束时间",
     *                           "used_time"=>"用时",
     *                           "score"=>"分数",
     *                           "question_id"=>"题目串",
     *                           "status"=>"状态",
     *                           "remark"=>"批语",
     *                           "remark_status"=>"批阅状态",
     *                           "read_user"=>"批阅人",
     *                           "name"=>"名称"
     *                  ]
     *              ]
     *          ]
     *      ]
     */
    public function actionExamResult1()
    {
        $params = Yii::$app->request->post();

        //todo 获取考试记录，考试,答题记录,题目分数
        if (!isset($params['record_id'])) {
            return $this->apiReturn(false, '缺少考试记录id');
        }

        $data = ExamRecord::getAnswerQuestionCase($params['record_id']);

        return $this->apiReturn(true, '操作成功', $data);
    }

    /**
     * @info 考试结果各种数据展示
     * @method POST
     * @params $integer 考试记录id require
     * @return array [
     *          'code' => 0,
     *          'message' => '操作成功'
     *          'data'=>[
     *              'data'=>[
     *                         [
     *                          "id"=>'题目',
     *                          "cate_id"=>'分类id',
     *                          "title"=>"题目标题",
     *                          "question_type"=>"题目类型"
     *                          "difficulty"=>"题目难易度",
     *                          "result"=>"正确答案",
     *                          "update_time"=>"更新时间",
     *                          "is_delete"=>"是否删除",
     *                          "explain"=>"答案解析",
     *                          "option_1"=>"选项1",
     *                          "option_2"=>"选项2",
     *                          "option_3"=>"选项3",
     *                          "option_4"=>"选项4",
     *                          "option_5"=>"选项5",
     *                          "option_6"=>选项6,
     *                          "option_7"=>选项7,
     *                          "option_8"=>选项8
     *                       ]
     *                   //题型数据统计
     *                  "statistics"=>[
     *                            "select"=>[],
     *                             "multiple"=>[],
     *                             "judgment"=>[]
     *                   ],
     *                   "record"=>[
     *                           "id"=>"记录id",
     *                           "exam_id"=>"考试id",
     *                           "user_id"=>"用户",
     *                           "start_time"=>"开始时间",
     *                           "end_time"=>"结束时间",
     *                           "used_time"=>"用时",
     *                           "score"=>"分数",
     *                           "question_id"=>"题目串",
     *                           "status"=>"状态",
     *                           "remark"=>"批语",
     *                           "remark_status"=>"批阅状态",
     *                           "read_user"=>"批阅人",
     *                           "name"=>"名称"
     *                  ]
     *              ]
     *          ]
     *      ]
     */
    public function actionExamResult()
    {
        $params = Yii::$app->request->post();

        if (!isset($params['record_id'])) {
            return $this->apiReturn(false, '缺少考试记录id');
        }

        $result = ExamRecord::MyExamRecord($params['record_id']);

        return $this->apiReturn(true, '操作成功', $result);
    }

    /**
     * @info 批阅
     * @method POST
     * @params $integer 记录id require
     * @params $string 批语 remark
     * @return array ['code' => 0, 'message' => '批阅成功']
     */
    public function actionReadOver()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['id'])) {
            return $this->apiReturn(false, '记录id不能为空');
        }

        $model = ExamRecord::findOne(['id' => $post['id']]);

        if (!$model) {
            return $this->apiReturn(false, '记录不存在');
        }

        //是否已经批阅了
        if ($model->remark_status == 1) {
            return $this->apiReturn(false, '已经批阅过了');
        }

        //开始批阅
        $model->remark = $post['remark'];
        $model->remark_status = 1;
        $model->read_user = Yii::$app->user->id;
        $res = $model->save();

        if($res){
            $title = CourseChapter::getLessonCoursePlan($model->exam->lesson_id);
            \Yii::$app->systemMessage->send('exam_result', $model->user_id, [
                'title' => $title['course_name'].'/'.$title['plan_name'].'/'.$model->exam->name
            ]);
        }

        return $this->apiReturn($res, ['批阅成功', '批阅失败']);
    }

    /**
     * @info 提交考试
     * @params $integer 记录$record_id require
     * @method POST
     * @return array ['code' => 0, 'message' => '提交成功']
     */
    public function actionReferExam()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['record_id'],$post['answer'])) {
            return $this->apiReturn(false, '缺少考试记录id,或者答案字段answer');
        }

        if ($post['answer']) {
            ExamAnswer::addData(array_filter($post['answer']), $post['record_id']);
        }

        $model = ExamRecord::findOne(['id' => $post['record_id']]);


        //以前的提交方式
        /* if (isset($post['selectAnswer']) && $post['selectAnswer']) {
             ExamAnswer::addData($post['select'], $post['selectAnswer'], $post['record_id']);
         }

         if (isset($post['multipleAnswer']) && $post['multipleAnswer']) {
             ExamAnswer::addData($post['multiple'], $post['multipleAnswer'], $post['record_id']);
         }

         if (isset($post['judgmentAnswer']) && $post['judgmentAnswer']) {
             ExamAnswer::addData($post['judgment'], $post['judgmentAnswer'], $post['record_id']);
         }*/

        /* if ($post['gapAnswer']) {
             ExamAnswer::addData($post['gap'], $post['gapAnswer'], $post['record_id']);
         }

         if ($post['essayAnswer']) {
             ExamAnswer::addData($post['essay'], $post['essayAnswer'], $post['record_id']);
         }*/

        //完成任务
        $totalScore = ExamRecord::MyExamRecordDetail($model->id)['statistics']['performance'];

        if ($post['task_id'] && $post['exam_id']) {
            //任务id
            $tasks = CourseLessonTask::findOne(['lesson_id' => $post['task_id'], 'target_id' => $post['exam_id']]);

            //解析条件
            $codition = json_decode($tasks->options, true);

            //查询任务
            //$taskData = CourseLessonTask::findOne(['id' => $taskId]);
            //完成任务
            if ($codition['condition'] == 1) {
                if ($totalScore >= $codition['score']) {
                    StudentTask::finishProcess($post['task_id'], Yii::$app->user->id);
                }
            } else {
                StudentTask::finishProcess($post['task_id'], Yii::$app->user->id);
            }
        }

        $model->end_time = time();
        $model->used_time = time() - $model->start_time;
        $model->score = $totalScore;

        return $this->apiReturn($model->save(), ['提交成功', '提交失败']);
    }

    /**
     * @info 完成考试任务
     * @method POST
     * @return array ['code' => 0,'message' => '任务完成']
     */
    public function actionCompleteExamTask()
    {
        //1，接收任务id，记录id，2，获取完成条件，3，调用接口，修改数据
        $post = Yii::$app->request->post();

        if (!isset($post['record_id'], $post['task_id'])) {
            return $this->apiReturn(false, '缺少考试记录id或者任务id');
        }

        //查询某一个任务的数据
        $taskData = CourseLessonTask::findOne(['id' => $post['task_id']]);

        if ($taskData->completion == 1) {
            //查询考试记录
            $recordData = ExamRecord::findOne(['id' => $post['record_id']]);

            if ($recordData->score < $taskData->completion_score) {
                return $this->apiReturn(false, '分数不达标，请继续学习');
            }
        }

        $status = StudentTask::finishProcess($post['task_id'], Yii::$app->user->id);

        return $this->apiReturn($status, ['完成任务', '任务失败']);

    }

    /**
     * @info 查看考试记录
     * @param integer $remark_status 批阅状态(默认不传为全部;0:未批阅,1:已批阅)
     * @method GET
     * @return array [
     *             'code' => 0,
     *             'message' => '任务完成'
     *              'data'=>[
     *                  'record'=>[
     *                           "id"=>"记录id",
     *                           "exam_id"=>"考试id",
     *                           "user_id"=>"用户id",
     *                           "start_time"=>"开始时间",
     *                           "end_time"=>"结束时间",
     *                           "used_time"=>"考试时长",
     *                           "score"=>"考试分数",
     *                           "question_id"=>"试题id",
     *                           "status"=> "考试状态",
     *                           "remark": "批语",
     *                           "remark_status"=>"批阅状态",
     *                           "read_user"=>"批阅人",
     *                           "exam"=>[
     *                               "id"=>"考试id",
     *                               "name"=>"考试名称",
     *                               "describe"=>"考试描述",
     *                               "paper_id"=>"试卷id",
     *                               "lesson_id"=>"计划id",
     *                               "exam_number"=>"考试次数",
     *                               "duration"=>"考试时长",
     *                               "update_time"=>"最后更新时间",
     *                               "retake_interval"=>"重复间隔",
     *                               "exam_score"=>"考试分数",
     *                          ]
     *                              "courseName"=>[
     *                                      course=>[
     *                                           "id"=>"课程id",
     *                                           "name"=>"课程名称"
     *                                          ],
     *                                      plan=>[
     *                                           "name"=>"计划名称",
     *                                      ],
     *                                      section=>[
     *                                            "name": "章节名称",
     *                                      ],
     *                              ]
     *                               "time"=>"时间格式"
     *                               "pagination"=>"分页"
     *                      ]
     *                  ]
     *              ]
     */
    public function actionViewRecord()
    {
        $params = Yii::$app->request->get();

        $query = ExamRecord::find()
            ->where(['user_id' => Yii::$app->user->id/*, 'remark_status' => 1*/])
            ->with('exam')
            ->asArray();


        if (isset($params['remark_status'])) {
            if ($params['remark_status'] != 3) {
                $query->andWhere(['remark_status' => $params['remark_status']]);
            }
        }

        if (Yii::$app->request->isPost) {
            return $this->apiReturn(false, '请求错误');
        }

        $data = $query->paginate()->all();

        $pagination = ArrayHelper::remove($data, 'pagination');
        //print_r($data);die;
        foreach ($data as $k => $v) {
            $lessonCoursePlan = CourseChapter::getLessonCoursePlan($v['exam']['lesson_id']);
            $data[$k]['course'] = $lessonCoursePlan;
            $data[$k]['time'] = date('Y.m.d', $v['start_time']);
        }

//        print_r($data);die;
        return $this->apiReturn(true, '操作成功', ['record' => $data, 'pagination' => $pagination]);
        // return $data;
    }

    /**
     * @info 批阅中
     * @return array
     * @return array ['code' => 0, 'message' => 'success']
     */
    public function actionTestMarking()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['record_id'])) {
            return $this->apiReturn(false, '无法批阅');
        }

        //缓存键
        $cacheKey = 'paper:' . $post['record_id'];

        //队列
        $post['record_id'] = [];

        if (Yii::$app->cache->get($cacheKey) && !isset($post['refresh'])) {

            if (Yii::$app->cache->get($cacheKey) == Yii::$app->user->id) {
                return $this->apiReturn(true, 'success');
            }

            $username = User::getUser(Yii::$app->cache->get($cacheKey))->realname;

            return $this->apiReturn(false, $username . '正在批阅中', Yii::$app->cache->get($cacheKey));
        }

        if (!isset($post['refresh'])) {
            Yii::$app->cache->set($cacheKey, Yii::$app->user->id, 10);
        } else {
            Yii::$app->cache->set($cacheKey, false);
        }

        return $this->apiReturn(true, 'success');
    }

    /**
     * @info 根据计划导出考试成绩，支持.csv和.txt两种格式
     * @method GET
     * @params $type 记录 require
     * @params $plan_id 计划id
     * @params $is_group 是否多个分组的数据
     */
    public function actionImportResult()
    {
        $params = Yii::$app->request->get();

        if (!isset($params['type']) && !$params['type']) {
            return $this->apiReturn(false, '缺少导出的类型');
        }

        //查询数据
        $query = ExamRecord::find()
            ->select(['a.user_id', 'a.exam_id', 'b.user_id', 'b.username', 'c.name', 'c.lesson_id', 'b.realname', 'COUNT(a.user_id) AS number', 'SUM(a.used_time) as time', 'SUM(a.score) as score'])
            ->from(ExamRecord::tableName() . ' a')
            ->leftJoin(User::tableName() . ' b', 'a.user_id=b.user_id')
            ->leftJoin(Exam::tableName() . ' c', 'a.exam_id=c.id');

        //查询计划的考试与否
        if (isset($params['plan_id']) && $params['plan_id']) {
            $examIds = CourseLessonTask::find()
                ->select(['target_id'])
                ->where(['type' => 2, 'plan_id' => is_string($params['plan_id']) ? explode(',', $params['plan_id']) : $params['plan_id']])
                ->column();
        } else {
            //所有计划id
            $planIds = CoursePlan::find()->select('id')->asArray()->column();
            $examIds = CourseLessonTask::find()
                ->select(['target_id'])
                ->where(['type' => 2, 'plan_id' => $planIds])
                ->column();
        }

        $query->where(['a.exam_id' => $examIds]);
        //根据参数导出两种成绩1.某个学生在某个考试的总成绩，2,某个学生的总成绩
        if (isset($params['is_group']) && $params['is_group']) {
            $query->groupBy(['a.exam_id', 'a.user_id']);
        } else {
            $query->groupBy(['a.user_id']);
        }

        $data = $query->orderBy('score desc,time asc')->asArray()->all();

//                    ->createCommand()
//                    ->getRawSql();

        /*$sql="SELECT b.username as '账号',b.realname as '名字',c.name as '考试名称',COUNT(a.user_id) as '考试次数',SUM(a.score) as '总分'
                FROM tab_exam_record as a 
                LEFT JOIN tab_user as b ON a.user_id=b.user_id 
                LEFT JOIN tab_exam as c ON a.exam_id=c.id
                GROUP BY a.user_id";
        $data=Yii::$app->db->createCommand($sql)->queryAll();*/

        // 导出到文档
        $execl_data = ['账号,用户名,考试课程名称,计划名称,章节名称,考试名称,考试次数,总时长,总分,排名'];
        // 组合数据成文本
        foreach ($data as $k => $item) {
            $used_time = Tool::ftime($item['time']);
            $coursePlanLessonData = CourseChapter::getLessonCoursePlan($item['lesson_id']);

            $one = [
                $item['username'],
                $item['realname'],
                $coursePlanLessonData['course_name'],
                $coursePlanLessonData['plan_name'],
                $coursePlanLessonData['section'],
                $item['name'],
                $item['number'],
                $used_time,
                $item['score'],
                $k + 1
            ];
            $execl_data[] = implode(',', $one);
        }
        // 转换utf-8编码为gb2312，否则在office excel中打开显示乱码；在wps中utf-8是正常的；
        $execl_data = IconvHelper::Conversion($execl_data, 'utf-8', 'gb2312');

        $filename = '考试记录';

        if ($params['type'] == 'excel') {
            $filename .= '.csv';
        } else {
            $filename .= '.txt';
        }

        header("Content-type:application/octet-stream");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        echo implode("\r\n", $execl_data);
        exit;
    }

    /**
     * 处理各种模式以及题目类型
     * @param $data
     * @return array|string
     */
    private function handle($data)
    {
        //选题方式，随机模式必须有题库id串，手动选题模式题目串字段不能为空
        if (isset($data['select_question_type']) && $data['select_question_type'] == 1) {
            if (!isset($data['customData']) || !$data['customData']) {
                return $this->apiReturn(false, '手动选题，题目不能为空');
            }

        }/* else {
            if (!isset($data['question_type_id'])) {
                return $this->apiReturn(false, '随机模式模式，题库不能为空');
            }
        }*/

        //有单选题个数，必须有分数
        if (isset($data['select_num']) && $data['select_num']) {

            $questionNums = ExamQuestion::getQuestionNum(1, $data['question_type_id']);

            if ($data['select_num'] > $questionNums) {
                return $this->apiReturn(false, '单选题个数不能超过题库数量');
            }

            if (!isset($data['select_fraction']) || !$data['select_fraction']) {
                return $this->apiReturn(false, '单选题分数不能为空');
            }

        }

        //有多选题个数，必须有分数
        if (isset($data['multiple_choice_num']) && $data['multiple_choice_num']) {

            $questionNums = ExamQuestion::getQuestionNum(2, $data['question_type_id']);

            if ($data['multiple_choice_num'] > $questionNums) {
                return $this->apiReturn(false, '多选题个数不能超过题库数量');
            }

            if (!isset($data['multiple_choice_fraction']) || !$data['multiple_choice_fraction']) {
                return $this->apiReturn(false, '多选题分数不能为空');
            }

        }

        //有判断题个数，必须有分数
        if (isset($data['judgment_num']) && $data['judgment_num']) {
            $questionNums = ExamQuestion::getQuestionNum(3, $data['question_type_id']);
            if ($data['judgment_num'] > $questionNums) {
                return $this->apiReturn(false, '判断题个数不能超过题库数量');
            }

            if (!isset($data['judgment_fraction']) || !$data['judgment_fraction']) {
                return $this->apiReturn(false, '判断题分数不能为空');
            }

        }

        //填空题
        if (isset($data['gap_filling_num']) && $data['gap_filling_num']) {
            $questionNums = ExamQuestion::getQuestionNum(4, $data['question_type_id']);
            if ($data['gap_filling_num'] > $questionNums) {
                return $this->apiReturn(false, '填空题个数不能超过题库数量');
            }

            if (!isset($data['gap_filling_fraction']) || !$data['gap_filling_fraction']) {
                return $this->apiReturn(false, '填空题分数不能为空');
            }

        }
        //问答题
        if (isset($data['essay_question_num']) && $data['essay_question_num']) {
            $questionNums = ExamQuestion::getQuestionNum(5, $data['question_type_id']);
            if ($data['essay_question_num'] > $questionNums) {
                return $this->apiReturn(false, '问答题个数不能超过题库数量');
            }

            if (!isset($data['essay_question_fraction']) || !$data['essay_question_fraction']) {
                return $this->apiReturn(false, '问答题分数不能为空');
            }
        }

        return false;
    }
}