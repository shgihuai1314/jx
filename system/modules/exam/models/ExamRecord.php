<?php

namespace system\modules\exam\models;

use Symfony\Component\Console\Question\Question;
use system\core\utils\Tool;
use system\modules\user\models\User;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tab_exam_record".
 *
 * @property integer $id
 * @property integer $exam_id
 * @property integer $user_id
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $used_time
 * @property integer $score
 * @property string $question_id
 * @property integer $status
 * @property integer $is_receive
 * @property string $remark
 * @property integer $remark_status
 * @property integer read_user
 * @property integer answer_duration
 */
class ExamRecord extends \system\models\Model
{
    //是否记录日志
    public $log_flag = true;
    //日志信息配置
    public $log_options = [
        'target_name' => 'id',//日志目标对应的字段名，默认name
        'model_name' => 'tab_exam_record',//模型名称
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tab_exam_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return parent::getRule([
            [['exam_id', 'user_id', 'start_time', 'end_time', 'used_time', 'score', 'status', 'is_receive', 'is_true'], 'integer'],
            [['question_id'], 'string'],
            [['remark', 'versions'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'id' => '主键',
            'exam_id' => '考试id',
            'user_id' => '答题用户id',
            'start_time' => '开始时间',
            'end_time' => '结束时间，0：用户没有结束答题',
            'used_time' => '答题耗时',
            'score' => '得分数',
            'question_id' => '试题id串',
            'status' => '成绩是否有效，有效为0，无效为1',
            'is_receive' => '是否领取，0为未领取，1为已领取',
            'is_true' => '是否是正确的学号姓名，0为是，1为不是，默认为0',
            'remark' => '评语',
            'remark_status' => '批阅状态,0:未批阅,1:已批阅',
            'read_user' => '批阅人'
        ], parent::attributeLabels());
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->start_time = time();
                $this->user_id = Yii::$app->user->id;
            }
            return true;
        }

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExam()
    {
        return $this->hasOne(Exam::className(), ['id' => 'exam_id']);
    }

    /**
     * 关联答题记录
     * @return \yii\db\ActiveQuery
     */
    public function getAnswer()
    {
        return $this->hasMany(ExamAnswer::className(), ['exam_record_id' => 'id'])->with('question');
    }

    /**
     * @param $record_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getOneData($record_id)
    {
        $oneData = self::find()
            ->where(['id' => $record_id])
            ->asArray()
            ->one();
        return $oneData;
    }

    /**
     * 根据记录id获取题型分类
     * @param $record_id
     * @return array|bool
     */
    public static function getExamRecord($record_id)
    {
        //查询考试记录
        $one = self::getOneData($record_id);
        if (!$one) {
            return false;
        }

        //查询考试的题目
        $data = ExamQuestion::getConditionQuestion(['questionId' => explode(',', $one['question_id'])]);

        $result['select'] = [];
        $result['multiple'] = [];
        $result['judgment'] = [];
        //题目分类
        foreach ($data as $k => $v) {
            switch ($v['question_type']) {
                case 1://单选
                    $result['select'][] = $v['id'];
                    continue;
                case 2://多选
                    $result['multiple'][] = $v['id'];
                    continue;
                //todo 填空，问答
                default://判断
                    $result['judgment'][] = $v['id'];
            }
        }
        //题目类型分组
        return $result;
    }

    /**
     * 获取答题的情况
     */
    public static function getAnswerQuestionCase($record_id = '')
    {
        $oneData = self::find()->where(['id' => $record_id])->one();

        $convert =ArrayHelper::toArray($oneData);

        //转换用户名
        $convert['user_id']= User::getUser($oneData->user_id)->realname;
        //转换时间
        $convert['time'] = $oneData->end_time ? date('Y-m-d H:i:s', $oneData->end_time) : '未提交';

        $paperData= ExamPaper::find()->where(['id' => $oneData->exam->paper_id])->one();

        $convert['name']= $paperData->paper_name;

        //试卷数据,统计数据
        $data = self::MyExamRecordDetail($record_id);

        $data['record']=$convert;
//        $data['paperData'] = ['user_id'=>$user_id,'time'=>$time,'name'=>$name];
        return $data;
    }

    /**
     * 统计答题的数据
     * @param $record_id
     * @param $question_id
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    /*public static function MyExamRecordDetail111($record_id = '')
    {
        //查询数据表数据
        $data = self::find()
            ->select('a.*,a.question_id as qustionstring,b.*,c.question_type,c.result')
            ->with('exam')
            ->from(self::tableName() . ' a')
            ->leftJoin(ExamAnswer::tableName() . ' b', 'a.id=b.exam_record_id')
            ->leftJoin(ExamQuestion::tableName() . ' c', 'b.question_id=c.id')
            ->where(['a.id' => $record_id])
            ->asArray()
            ->all();

        $result = [];
        $result['select']['score'] = [];
        $result['select']['error'] = [];
        $result['select']['correct'] = [];
        $result['multiple']['score'] = [];
        $result['multiple']['error'] = [];
        $result['multiple']['correct'] = [];
        $result['multiple']['regressionScore'] = [];
        $result['judgment']['score'] = [];
        $result['judgment']['error'] = [];
        $result['judgment']['correct'] = [];
        foreach ($data as $k => $v) {
            switch ($v['question_type']) {
                case 1://单选
                    //正确个数
                    if ($v['question_answer'] == $v['result']) {
                        $result['select']['correct'][] = $v['question_id'];
                        $result['select']['score'][] = ExamSetScore::computeScore($v['exam']['paper_id'], $v['question_id']);
                    } else {
                        $result['select']['error'][] = $v['question_id'];
                    }
                    continue;
                case 2://多选
                    if ($v['question_answer'] == $v['result']) {
                        $result['multiple']['correct'][] = $v['question_id'];
                        $result['multiple']['score'][] = ExamSetScore::computeScore($v['exam']['paper_id'], $v['question_id']);
                    } else {
                        //我选择的答案
                        $myAnswer = explode(',', $v['question_answer']);
                        //标准答案
                        $correctAnswer = explode(',', $v['result']);

                        if (array_intersect($myAnswer, $correctAnswer)) {
//                            $result['multiple']['regression'][] = $v['question_id'];
                            $result['multiple']['regressionScore'][] = ExamPaper::getOne($v['exam']['paper_id'])['regression'];
                        }

                        $result['multiple']['error'][] = $v['question_id'];
                    }
                    continue;
                //todo 填空，问答
                default://判断
                    //$result['judgment'][] = $v['id'];
                    if ($v['question_answer'] == $v['result']) {
                        $result['judgment']['correct'][] = $v['question_id'];
                        $result['judgment']['score'][] = ExamSetScore::computeScore($v['exam']['paper_id'], $v['question_id']);
                    } else {
                        $result['judgment']['error'][] = $v['question_id'];
                    }
            }
        }
//        print_r($data);die;
        $statistics = [];
        //题目总数
        $statistics['select']['count'] = count(self::getExamRecord($record_id)['select']);
        $statistics['multiple']['count'] = count(self::getExamRecord($record_id)['multiple']);
        $statistics['judgment']['count'] = count(self::getExamRecord($record_id)['judgment']);
        //答对数量
        $statistics['select']['answerCorrect'] = count($result['select']['correct']);
        $statistics['multiple']['answerCorrect'] = count($result['multiple']['correct']);
        $statistics['judgment']['answerCorrect'] = count($result['judgment']['correct']);
        //答错数量
        $statistics['select']['errorCorrect'] = count($result['select']['error']);
        $statistics['multiple']['errorCorrect'] = count($result['multiple']['error']);
        $statistics['judgment']['errorCorrect'] = count($result['judgment']['error']);
        //未答数量
        // print_r($statistics['select']['count']-(count($result['select']['correct']) + count($result['select']['error'])));die;
        $statistics['select']['notAnswer'] = $statistics['select']['count'] - (count($result['select']['correct']) + count($result['select']['error']));
        $statistics['multiple']['notAnswer'] = $statistics['multiple']['count'] - (count($result['multiple']['correct']) + count($result['multiple']['error']));
        $statistics['judgment']['notAnswer'] = $statistics['judgment']['count'] - (count($result['judgment']['correct']) + count($result['judgment']['error']));
        //题型得分
        $statistics['select']['sumScore'] = array_sum($result['select']['score']);
        $statistics['multiple']['sumScore'] = array_sum($result['multiple']['score']) + array_sum($result['multiple']['regressionScore']);
        $statistics['judgment']['sumScore'] = array_sum($result['judgment']['score']);

        //总成绩
        $statistics['performance'] = array_sum([$statistics['select']['sumScore'], $statistics['multiple']['sumScore'], $statistics['judgment']['sumScore']]);

        return $statistics;
    }*/


    /**
     * 记录详情
     * @param string $record_id
     * @return mixed
     */
    public static function MyExamRecord($record_id = '')
    {
        $record = self::find()->where(['id' => $record_id])->asArray()->one();

        $exam=Exam::find()->where(['id' => $record['exam_id']])->with('examPaper')->asArray()->one();


        //题目串
        $questionIds = explode(',', $record['question_id']);

        //所有的题目
        $data = ExamQuestion::find()
            ->where(['id' => $questionIds])
            ->orderBy(['question_type'=>SORT_ASC])
            ->asArray()
            ->all();

        //所有的答题记录
        $allAnswer = ExamAnswer::find()->where(['question_id' => $questionIds, 'exam_record_id' => $record_id])->asArray()->all();
        $arr=ArrayHelper::index($allAnswer,'question_id');

        foreach ($data as $k => $v) {
            //每道题的分数
            $questionScore=ExamSetScore::find()->where(['paper_id'=>$exam['paper_id'],'qustion_id'=>$v['id']])->one();
            $data[$k]['oneQuestionScore']=$questionScore['question_score'];

           if(isset($arr[$v['id']])){
               if($arr[$v['id']]['remark_user']){
                   $userMsg=isset($arr[$v['id']]['remark_user'])?User::getUser($arr[$v['id']]['remark_user']):'';
                   $arr[$v['id']]['username']=$userMsg['realname'];
                   $arr[$v['id']]['userImage']=$userMsg['avatar'];
                   $arr[$v['id']]['remarkTime']=Tool::showTime($arr[$v['id']]['remark_time']);
               }
               $data[$k]['answer'] =$arr[$v['id']];
           }else{
               $data[$k]['answer']=[
                   'remark'=>'',
                   'is_correct'=>0,
                   'question_answer'=>'',
               ];
           }
        }

        $userInfo=User::getUser($record['user_id']);
        $record['image']=$userInfo['avatar'];
        $record['username']=$userInfo['realname'];
        $record['readname']=User::getUser($record['read_user'])['realname'];
        $record['time']=Tool::showTime($record['start_time']);
        $record['start_time']=date('Y-m-d',$record['start_time']);
        $record['name']=$exam['examPaper']['paper_name'];
        $record['describle']=$exam['examPaper']['paper_describle'];

        return ['data'=>$data,'record'=>$record];

    }



    /**
     * 记录详情
     * @param string $record_id
     * @return mixed
     */
    public static function MyExamRecordDetail($record_id = '')
    {
        $record = self::find()->where(['id' => $record_id])->one();
        //题目串
        $questionIds = explode(',', $record->question_id);

        //所有的题目
        $data = ExamQuestion::find()->where(['id' => $questionIds])->asArray()->all();

        //所有的答题记录
        $allAnswer = ExamAnswer::find()->where(['question_id' => $questionIds, 'exam_record_id' => $record_id])->asArray()->all();

        foreach ($data as $k => $v) {
            foreach ($allAnswer as $key => $val) {
                if ($val['question_id'] == $v['id']) {
                    $data[$k]['answer'] = $val;
                }
            }
        }

        return ['data'=>$data,'statistics'=>self::getQuestionType($data, $record)];
    }

    /**
     * 统计答题情况
     * @param $data
     * @param $record
     */
    public static function getQuestionType($data, $record)
    {
        //查找试卷的数据
        $paperData = ExamPaper::find()->where(['id' => $record->exam->paper_id])->one();

        $result = [];
        $result['select']['score'] = [];
        $result['select']['error'] = [];
        $result['select']['correct'] = [];
        $result['multiple']['score'] = [];
        $result['multiple']['error'] = [];
        $result['multiple']['correct'] = [];
        $result['multiple']['regressionScore'] = [];
        $result['judgment']['score'] = [];
        $result['judgment']['error'] = [];
        $result['judgment']['correct'] = [];
        foreach ($data as $k => $v) {
//            $result[$v['question_type']][]=$v;
            switch ($v['question_type']) {
                //多选
                case 2:
                    if(isset($v['answer'])){
                        if ($v['answer']['question_answer'] == $v['result']) {
                            $result['multiple']['correct'][] = $v['id'];
                            //试卷模式
                            if($paperData->select_question_type==0){
                                $result['multiple']['score'][]=$paperData->multiple_choice_fraction;
                            }else{
                                $result['multiple']['score'][] = ExamSetScore::computeScore($record->exam->paper_id, $v['id']);
                            }
                        } else {
                            //我选择的答案
                            $myAnswer = explode(',', $v['answer']['question_answer']);
                            //标准答案
                            $correctAnswer = explode(',', $v['result']);

                            if (array_intersect($myAnswer, $correctAnswer)) {
//                            $result['multiple']['regression'][] = $v['question_id'];
                                $result['multiple']['regressionScore'][] = $paperData->regression;
                            }

                            $result['multiple']['error'][] = $v['id'];
                        }
                    }else{
                        $data['multiple']['notAnswer'][] = $v['id'];
                    }
                    //todo 填空，问答
                    continue;
                default:
                    //单选，判断
                    $type=$v['question_type']==1?'select':'judgment';
                    if (isset($v['answer'])) {
                        if ($v['answer']['question_answer'] == $v['result']) {
                            $result[$type]['correct'][] = $v['id'];
                            //试卷模式，如果是完全随机的就拿试卷里面的分数的设置，否择就拿分数表的数据
                            if ($paperData->select_question_type == 0) {
                                $result[$type]['score'][] = $type=='select'?$paperData->select_fraction:$paperData->judgment_fraction;
                            } else {
                                $result[$type]['score'][] = ExamSetScore::computeScore($record->exam->paper_id, $v['id']);
                            }
                        } else {
                            $result[$type]['error'][] = $v['id'];
                        }
                    } else {
                        $result[$type]['notAnswer'][] = $v['id'];
                    }
            }
        }

        $statistics = [];
        //题目总数
        $statistics['select']['count'] = count(self::getExamRecord($record->id)['select']);
        $statistics['multiple']['count'] = count(self::getExamRecord($record->id)['multiple']);
        $statistics['judgment']['count'] = count(self::getExamRecord($record->id)['judgment']);
        //答对数量
        $statistics['select']['answerCorrect'] = count($result['select']['correct']);
        $statistics['multiple']['answerCorrect'] = count($result['multiple']['correct']);
        $statistics['judgment']['answerCorrect'] = count($result['judgment']['correct']);
        //答错数量
        $statistics['select']['errorCorrect'] = count($result['select']['error']);
        $statistics['multiple']['errorCorrect'] = count($result['multiple']['error']);
        $statistics['judgment']['errorCorrect'] = count($result['judgment']['error']);
        //未答数量
        // print_r($statistics['select']['count']-(count($result['select']['correct']) + count($result['select']['error'])));die;
        $statistics['select']['notAnswer'] = $statistics['select']['count'] - (count($result['select']['correct']) + count($result['select']['error']));
        $statistics['multiple']['notAnswer'] = $statistics['multiple']['count'] - (count($result['multiple']['correct']) + count($result['multiple']['error']));
        $statistics['judgment']['notAnswer'] = $statistics['judgment']['count'] - (count($result['judgment']['correct']) + count($result['judgment']['error']));
        //题型得分
        $statistics['select']['sumScore'] = array_sum($result['select']['score']);
        $statistics['multiple']['sumScore'] = array_sum($result['multiple']['score']) + array_sum($result['multiple']['regressionScore']);
        $statistics['judgment']['sumScore'] = array_sum($result['judgment']['score']);
        //总成绩
        $statistics['performance'] = array_sum([$statistics['select']['sumScore'], $statistics['multiple']['sumScore'], $statistics['judgment']['sumScore']]);

       return $statistics;
    }

    /**
     * 根据用户id和考试id获取用户的考试记录
     * @param $user_id
     * @param $exam_id
     * @return array|mixed|null|\yii\db\ActiveRecord
     */
    public static function getOneByUserIdAndExamId($user_id, $exam_id)
    {
        $model = ExamRecord::find()
            ->where(['exam_id' => $exam_id, 'user_id' => $user_id])
            ->one();

        return $model;
    }

    /**
     * 修改批阅状态
     */
    /* public static function updateReadStatus($attributes)
     {
         $model=new self;
         return call_user_func_array([$model,'updateInternal'], $attributes);
     }*/

    /**
     * @param null $attributes
     * @return bool|int
     */
    /* public function updateInternal($attributes = [])
     {
         if (!$this->beforeSave(false)) {
             return false;
         }

         // 获取等下要更新的字段及新的字段值
         $values = $this->getDirtyAttributes($attributes);

         if (empty($values)) {
             $this->afterSave(false, $values);
             return 0;
         }
         // 把原来ActiveRecord的主键作为等下更新记录的条件，
         // 也就是说，等下更新的，最多只有1个记录。
         $condition = $this->getOldPrimaryKey(true);

         // 获取版本号字段的字段名，比如 versions
         $lock = $this->optimisticLock();

         // 如果 optimisticLock() 返回的是 null，那么，不启用乐观锁。
         if ($lock !== null) {
             // 这里的 $this->$lock ，就是 $this->ver 的意思；
             // 这里把 ver+1 作为要更新的字段之一。
             $values[$lock] = $this->versions + 1;

             // 这里把旧的版本号作为更新的另一个条件
             $condition[$lock] = $this->versions;
         }
         $rows = $this->updateAll($values, $condition);

         // 如果已经启用了乐观锁，但是却没有完成更新，或者更新的记录数为0；
         // 那就说明是由于 ver 不匹配，记录被修改过了，于是抛出异常。
         if ($lock !== null && !$rows) {
             throw new StaleObjectException('The object being updated is outdated.');
         }
         $changedAttributes = [];
         foreach ($values as $name => $value) {
             $changedAttributes[$name] = isset($this->_oldAttributes[$name]) ? $this->_oldAttributes[$name] : null;
             $this->_oldAttributes[$name] = $value;
         }
         $this->afterSave(false, $changedAttributes);
         return $rows;
     }*/


    /**
     * 重载父类方法
     * @return string
     */
    /*  public function optimisticLock()
      {
          return 'versions';
      }*/
    /**
     * 获取考试记录
     *
     */
    public static function getAllExamRecord($student_ids,$exam_id){
        return self::find()
            ->select('user_id,sum(score)/count(id) as score')
            ->where(['in','user_id',$student_ids])
            ->andWhere(['exam_id'=>$exam_id])
            ->groupBy('user_id')
            ->asArray()
            ->all();
    }
}
