<?php
namespace system\modules\user\widgets;

use system\modules\user\models\UserRead;
use yii\base\Widget;

class UserReadWidget extends Widget{

    public $target_type;  //目标类型
    public $target_id;    //目标ID
    public $mode = '';    // 模式，mobile代表手机端

    public function init()
    {

    }

    public function run()
    {
        $target_type = $this->target_type;
        $target_id = $this->target_id;

        // 把当前用户加入已读
        $user_read = new UserRead();
        $user_read->addRead($target_type,$target_id);

        $data = UserRead::getReadData($target_type, $target_id);

        return $this->render('read/'. ($this->mode == 'mobile' ? 'mobile' : 'pc'), [
            'readData' => $data
        ]);
    }
}