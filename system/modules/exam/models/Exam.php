<?php

namespace system\modules\exam\models;

use Symfony\Component\Console\Question\Question;
use system\modules\course\models\Course;
use system\modules\course\models\CourseChapter;
use system\modules\course\models\CourseLesson;
use system\modules\course\models\CoursePlan;
use system\modules\course\models\CourseTeam;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_exam".
 *
 * @property integer $id
 * @property integer $paper_id
 * @property string $name
 * @property string $permission
 * @property string $describe
 * @property integer $start_time
 * @property integer $end_time
 * @property string $exam_info
 * @property string $exam_number
 * @property integer $retake_interval
 * @property integer $lesson_id
 * @property integer $update_time
 * @property integer $duration
 * @property integer $exam_score
 */
class Exam extends \system\models\Model
{
    //是否记录日志
    public $log_flag = true;
    //日志信息配置
    public $log_options = [
        'model_name' => 'tab_exam',//模型名称
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_exam';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['paper_id', 'start_time', 'end_time', 'exam_number', 'sort'], 'integer'],
            [['name', 'describe', 'exam_info'], 'string'],
            [['retake_interval', 'lesson_id', 'update_time', 'duration', 'exam_score'], 'safe']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => 'Id',
            'paper_id' => '试卷id',
            'name' => '考试名称',
            'exam_number' => '考试次数',
            'retake_interval' => '重复间隔',
            'describe' => '说明',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'exam_info' => '试题id',
            'sort' => '排序',
            'lesson_id' => '课程id',
            'update_time' => '发布时间',
            'duration' => '考试时长',
            'exam_score' => '分数',
        ], parent::attributeLabels());
    }

    /**
     * 关联试卷
     * @return \yii\db\ActiveQuery
     */
    public function getExamPaper()
    {
        return $this->hasOne(ExamPaper::className(), ['id' => 'paper_id']);
    }

    /**
     * 获取考试的所有数据
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public static function getAllData()
    {
        $data = self::find()->asArray()->all();
        if (!$data) {
            return false;
        }

        return $data;
    }

    /**
     * 随机生成试题
     * @return bool
     */
    public static function startExam($id)
    {
        $query = self::find();
        $query->andWhere(['tab_exam.id' => $id]);
        $examOne = $query->one();

        //完全随机
        if ($examOne->examPaper->select_question_type == 0) {
            $randQuestion = self::randQuestion($examOne->examPaper);
            $toSting = self::toString($randQuestion);
            return implode(',', $toSting);
            //固定模式
        } elseif ($examOne->examPaper->select_question_type == 1) {
            $result = $query->joinWith(['examPaper'])->asArray()->one();
            //print_r($result);
            return $result['examPaper']['setting_info'];
            //随机生成一套
        } elseif ($examOne->examPaper->select_question_type == 2) {
            $result = $query->select(['exam_info'])->asArray()->one();
            return $result['exam_info'];
        }
    }

    /**
     * 抽取题目
     * @param object|array $examPaperMsg
     * @return array|bool
     */
    public static function randQuestion($examPaperMsg)
    {
       // print_r($examPaperMsg);die;
        //题库
        if (is_array($examPaperMsg->question_type_id)) {
            $question_banks = $examPaperMsg->question_type_id;
        } else {
            $question_banks = explode(',', $examPaperMsg->question_type_id);
        }

        $data = ExamQuestion::find()
            ->select(['id', 'question_type', 'cate_id'])
            ->where(['in', 'cate_id', $question_banks])
            ->asArray()
//            ->createCommand()
//            ->getRawSql();
            ->all();


        $data = ArrayHelper::map($data, 'id', 'id', 'question_type');
//        print_r($data);die;
        $result = [];

        // 单选
        $select_num = $examPaperMsg->select_num;

        if ($select_num > 0 && (!isset($data[1]) || count($data[1]) < $select_num)) {
            return false;
        }

        $result['select'] = $select_num > 0 ? array_rand($data[1], $select_num) : [];

        // 多选
        $multiple_choice_num = $examPaperMsg->multiple_choice_num;
        if ($multiple_choice_num > 0 && (!isset($data[2]) || count($data[2]) < $multiple_choice_num)) {
            return false;
        }

        $result['multiple_choice'] = $multiple_choice_num > 0 ? array_rand($data[2], $multiple_choice_num) : [];

        // 判断
        $judgment_num = $examPaperMsg->judgment_num;
        if ($judgment_num > 0 && (!isset($data[3]) || count($data[3]) < $judgment_num)) {
            return false;
        }
        $result['judgment'] = $judgment_num > 0 ? array_rand($data[3], $judgment_num) : [];

        //填空
        $gap_filling = $examPaperMsg->gap_filling_num;
        if ($gap_filling > 0 && (!isset($data[4]) || count($data[4]) < $gap_filling)) {
            return false;
        }
        $result['gap'] = $gap_filling > 0 ? array_rand($data[4], $gap_filling) : [];

        //问答
        $essay_question = $examPaperMsg->essay_question_num;
        if ($essay_question > 0 && (!isset($data[5]) || count($data[5]) < $essay_question)) {
            return false;
        }
        $result['essay'] = $essay_question > 0 ? array_rand($data[5], $essay_question) : [];
//        print_r($result);die;
        return $result;
    }

    /**
     * 随机抽取的数组转换
     * @param $val
     * @return array|string
     */
    public static function toString($val)
    {
        $str = '';
        $split = '';
        $data = [];


        if (!$val) {
            return $data;
        }

        foreach (array_filter($val) as $key => $item) {
            if (in_array($key, ['select', 'multiple_choice', 'judgment', 'gap', 'essay'])) {
                if (is_array($item)) {
                    $str .= $split . implode(',', $item);
                    $split = ',';
                } else {
                    $str .= $split . $item;
                    $split = ',';
                }
            }
        }
        if ($str) {
            return explode(',', $str);
        }

        return [];

    }


    /**
     * @param bool $insert
     * @return bool
     */
    /*  public function beforeSave($insert)
      {
          if (parent::beforeSave($insert)) {

              if ($insert) {
                  if ($this->examPaper->select_question_type == 2) {
                      $this->exam_info = $this->examPaper->setting_info;
                      $this->update_time = time();
                  }
                  $this->exam_score=$this->examPaper->score_num;
                  return true;
              }
              $this->exam_score=$this->examPaper->score_num;
              $this->update_time = time();
              return true;
          }
          return false;
      }*/


    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->exam_info = $this->examPaper->setting_info;
            $this->update_time = time();
            $this->exam_score = $this->examPaper->score_sum;
            return true;
        }

        return false;
    }

    /**
     * 关联课程
     * @return \yii\db\ActiveQuery
     */
    public function getLesson()
    {
        return $this->hasOne(CourseLesson::className(), ['id' => 'lesson_id']);
    }

    /**
     * 关联考试记录
     * @return \yii\db\ActiveQuery
     */
    public function getRecord()
    {
        return $this->hasMany(ExamRecord::className(), ['exam_id' => 'id']);
    }

    /**
     * 获取一条考试记录
     * @param $exam_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getOne($exam_id)
    {
        $cache = self::find()->where(['id' => $exam_id])->one();

        return $cache;
    }

    /**
     * 提供课程添加修改数据
     * @param string $exam_id
     */
    public static function affordCourse($exam_id = '', $data = [])
    {
        if (!isset($data['lesson_id'], $data['name'], $data['paper_id'], $data['exam_number']/*, $data['retake_interval']*/)) {
            return false;
        }

        $model = $exam_id ? self::getOne($exam_id) : new self();
        $model->name = $data['name'];//考试名称
        $model->describe = isset($data['describe']) ? $data['describe'] : '';//考试描述
        $model->paper_id = $data['paper_id'];//试卷id
        $model->exam_number = $data['exam_number'];//考试次数
        $model->duration = isset($data['duration']) ? $data['duration'] : 0;//考试时长
        $model->lesson_id = $data['lesson_id'];//
        $model->retake_interval = isset($data['retake_interval']) ? $data['retake_interval'] : 0;//考试间隔

        //todo 考试合格分数设置
        if ($model->save()) {
            return $model->id;
        } else {
            // print_r($model->getErrors());die;
            return $model->getErrors();
        }

    }


    /**
     * 返回关联章节的考试
     * @return array|bool
     */
    public static function getEXamId()
    {
        //获取所有的lessonid
        $lessonId = CourseTeam::getlensson(Yii::$app->user->id, 'exam_review');

        //查询关联的考试
        $data = self::find()->select('id')->where(['lesson_id' => $lessonId])->column();

        if (!$data) {
            return false;
        }

        return $data;
    }

    /**
     * 获取可批阅的考试
     * @return mixed
     */
    public static function getRead($name=''){
        $lessonId = CourseTeam::getlensson(Yii::$app->user->id, 'exam_review');

        $query = Exam::find();

        $result = $query
            ->where(['lesson_id' => $lessonId])
            ->andWhere(['like','name',$name])
            ->with('record')
            ->asArray()
            ->all();

        $data = self::getExamDetail($result);

        return $data;
    }

    /**
     * todo 写缓存
     * 获取课程信息，课程计划，以及批阅状态，提交人数
     * @param $data
     */
    public static function getExamDetail($data)
    {
        foreach ($data as $key => $val) {
            //课程ID-计划ID-章节ID

            $lessonData=CourseChapter::find()->where(['id'=>$val['lesson_id']])->asArray()->one();
            //计划
            $name=CoursePlan::find()->select(['name','course_id'])->where(['id' => $lessonData['plan_id']])->asArray()->with('course')->one()/*->scalar()*/;

            $data[$key]['plan'] = $name['course']['name'].' — '.$name['name'];
            //课时
           $data[$key]['lessons'] =$lessonData['title'] ;

            foreach ($val['record'] as $k => $v) {
                if ($v['end_time']) {
                    $data[$key]['submitNum'][] = $v['id'];//提交人数
                }
                if ($v['remark_status'] == 1) {
                    $data[$key]['read'][] = $v['id'];//已批阅
                } else {
                    $data[$key]['notRead'][] = $v['id'];//未批阅
                }
            }
        }

        return $data;
    }


    /**
     * 开始考试
     */
    public static function getExamMsg($exam_id = '')
    {
        if (!$exam_id) {
            return '没有考试';
        }

        $one = self::findOne(['id' => $exam_id]);

        //用户id
        $user_id = Yii::$app->user->id;

        //todo 如果是完全随机，就传递试卷id，题目，其他模式传递试卷id即可
        $exam_record = ExamRecord::find()->where(['exam_id' => $exam_id, 'user_id' => $user_id])->all();

        //是否有考试次数限制,如果有的话就看是否达到次数
        if ($one->exam_number && count($exam_record) >= $one->exam_number) {
            return ['code' => 1, 'msg' => '已达到考试次数,无法在次考试'];
        }

        //完全随机模式
        $data = [];
        if ($exam_record && $exam_record[count($exam_record) - 1]) {
            //如果没有提交,每次进去都是之前的记录，无法下一次考试
            if (!$exam_record[count($exam_record) - 1]->end_time && !$exam_record[count($exam_record) - 1]->score) {
                if ($one->examPaper->select_question_type == 0) {
                    return [
                        'paper_id' => $one->paper_id,
                        'questionIds' => explode(',', $exam_record[count($exam_record) - 1]->question_id),
                        'record_id' => $exam_record[count($exam_record) - 1]->id
                    ];
                } else {
                    return ['paper_id' => $one->paper_id, 'record_id' => $exam_record[count($exam_record) - 1]->id];
                }
            }
        }

        //开始一次考试
        //添加考试记录 用户id 开始时间 考试id 试题串
        $model = new ExamRecord();
        $model->exam_id = $exam_id;
        $model->question_id = is_array(self::startExam($exam_id)) ? implode(',', self::startExam($exam_id)) : self::startExam($exam_id);
        $model->save();

        if ($one->examPaper->select_question_type == 0) {
            $data = [
                'is_first'=>1,
                'paper_id' => $one->paper_id,
                'questionIds' => self::startExam($exam_id),
                'record_id' => $model->id
            ];
        } else {
            $data = [
                'is_first'=>1,
                'paper_id' => $one->paper_id,
                'record_id' => $model->id
            ];
        }

        return $data;
    }

}
