<?php

namespace system\modules\main\components;

use system\modules\course\models\CourseSource;
use system\core\utils\Tool;
use yii\base\Component;
use yii;

class Trans extends Component
{
    /**
     * 统一处理转码组件
     *
     */
    public function trans()
    {
        //获取配置项
        $extension = \Yii::$app->systemConfig->getValue('TRANS_EXTENSIONS');
        $transValue = \Yii::$app->systemConfig->getValue('TRANS_VALUES');

        //将配置项重新组装
        $trans = [];
        foreach ($extension as $k => $v) {
            $trans[$k]['class'] = $v;
            $trans[$k]['extension'] = $k;
            $trans[$k]['options'] = $transValue;
        }

        //key值重新命名
        $trans = array_values($trans);

        $file_type = [];
        foreach (yii\helpers\ArrayHelper::getColumn($trans, 'extension') as $tran) {
            $file_type = yii\helpers\ArrayHelper::merge($file_type, explode(',', $tran));
        }

        $res = true;

        while ($res) {
            //获取转码数据
            $data = CourseSource::find()
                ->where(['trans_state' => 0, 'file_extension' => $file_type])
                ->limit(1)
                ->all();

            if (!$data) {
                //echo 'There is no trans work to be done'.PHP_EOL;
                $res = false;
                continue;
                //return;
            }

            echo 'Starting trans task ...' . PHP_EOL;
            //进行转码
            foreach ($data as $key => $fileModel) {
                foreach ($trans as $k1 => $v1) {
                    //判断源文件类型
                    if (strpos($v1['extension'], $fileModel->file_extension) !== false) {
                        $fileModel->trans_state = 2;//正在转码
                        $fileModel->save();

                        //查询相关类型，匹配到相关类
                        $object = Yii::createObject([
                            'class' => $v1['class'],
                        ]);

                        $result = $object->trans($fileModel['file_path'], $v1['options']);

                        //判断转码是否成功
                        if (!$result) {
                            $fileModel->trans_state = 2; // 置为未转换状态
                            $fileModel->save();
                        } else {
                            if ($fileModel->file_type == 'video') {
                                $fileModel->trans_type = isset($transValue['type']) ? $transValue['type'] : '';
                            } else {
                                $fileModel->trans_type = $result['trans_type'];
                            }
                            //保存到数据库
                            $fileModel->trans_state = 1;//转码成功
                            $fileModel->trans_path = json_encode($result['trans_file']);
                            $fileModel->save();
                        }
                    }
                }
            }
        }
        return;
    }
}