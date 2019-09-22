<?php
/*表格挂件*/

namespace system\modules\main\widgets\table;

use yii\base\Widget;
use yii\data\ActiveDataProvider;

class TableWidget extends Widget
{
    //数据模型，必传
    public $model;

    //是否分页
    public $pagination = true;

    //是否有配置
    public $groups=false;

    public $search=false;

    public function run()
    {
        parent::run();

        $tableModel = new $this->model;
        $query = $tableModel->find();

        //获取字段对应标签
        $labels=$tableModel->attributeLabels();

        //获取表单自增字段（id）,每个自增字段名称可能不同node_id、id。。
        $key=array();
        foreach($labels as $k=>$v){
            $key[]=$k;
        }
        $id=$key[0];


        //分页和不分页的情况分开查询
        if ($this->pagination) {
            //分页
            $pagination = new \yii\data\Pagination([
                'defaultPageSize' => \Yii::$app->systemConfig->getValue('LIST_ROWS', 20),
                'totalCount' => $query->count(),
            ]);
            $data = $query->asArray()
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->orderBy([$id => SORT_ASC])
                ->all();
        } else {
            $pagination = array();
            $data = $query->asArray()
                ->select('*')
                ->all();
        }

        //配置项
        if($this->groups){
            $groups=$this->groups;
        }else{
            $groups=array();
        }

        return $this->render('index', [
            'data' => $data,
            'labels'=>$labels,
            'id'=>$id,
            'pagination' => $pagination,//有则取出，无则默认为空
            'groups'=>$groups,//有则取出，无则默认为空
        ]);
    }
}