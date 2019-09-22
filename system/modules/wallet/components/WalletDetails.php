<?php
namespace system\modules\wallet\components;
use system\modules\wallet\models\UserWalletDetails;

/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2019/3/18
 * Time: 11:37
 */
class WalletDetails extends \yii\base\Component
{
    /**
     * 添加数据
     * @param $data
     * @return array
     */
    public function addData($data=[]){
        if(!$data){
            return ['code'=>1,'message'=>'数据不能为空'];
        }

        //必须的参数
        $parameter=['user_id','amount','type','description','target_user_id','target_id'];

        foreach ($data as $k=>$v){
            if(!in_array($k,$parameter)){
                return ['code'=>1,'message'=>'缺少'.$k.'参数'];
            }
        }

        $result=UserWalletDetails::saveData($data);

        if ($result['code'] != 0) {
            return ['code' => 1, 'message' => $result['message']];
        }

        return ['code' => 0, 'message' => $result['message']];

    }

}