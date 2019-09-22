<?php
/**
 * Created by PhpStorm.
 * User: shihuai
 * Date: 2018/11/7
 * Time: 13:09
 */

namespace Api;


use system\core\utils\Tool;
use system\modules\main\extend\SaveUpload;
use system\modules\user\models\InfoForm;
use system\modules\user\models\User;
use system\modules\user\models\UserExtend;
use yii\helpers\ArrayHelper;
use Yii;
use yii\helpers\BaseFileHelper;
use yii\web\UploadedFile;

class InfoController extends BaseApiController
{
    public $notAuthAction = ['get-user-setting'];

    /**
     * @info 修改密码
     * @method POST
     * @param string $oldPassword 原始密码 required
     * @param string $newPassword 新密码 required
     * @param string $newPasswordRepeat 确认密码 required
     * @return array ['code' => 0, 'message' => '操作成功']
     */
    public function actionPassword()
    {
        $post = \Yii::$app->request->post();

        if (!isset($post['oldPassword'], $post['newPassword'], $post['newPasswordRepeat'])) {
            return $this->apiReturn(false, '缺少参数');
        }

        $model = new InfoForm();
        $model->scenario = 'password';

        if ($model->load($post, '')) {
            $res = $model->changePassword();
            if ($res === true) {
                return $this->apiReturn(true, '修改密码成功');
            } else {
                return $this->apiReturn(false, $res);
            }
        }

        return $this->apiReturn(false, '修改密码失败');
    }

    /**
     * @info 修改用户信息
     * @method POST
     * @param string $avatar 头像
     * @param string $realname 姓名 required
     * @param string $personal_profile 个人简介
     * @param string $phone 手机号
     * @param string $email 邮箱
     * @param string $wx 微信
     * @return array ['code' => 0, 'message' => '操作成功']
     */
    public function actionUpdate()
    {
        $post = \Yii::$app->request->post();
        $model = new InfoForm();
        $model->scenario = 'update';

        if ($model->load($post, '')) {
            $res = $model->updateInfo(true);
            if ($res === true) {
                return $this->apiReturn(true, '修改成功');
            } else {
                return $this->apiReturn(false, '修改失败');
            }
        }
        return $this->apiReturn(false, '修改失败');
    }


    /**
     * @info 获取用户信息
     * @method GET
     * @return array
     * [
     *      'code' => 0,
     *      'message' => 'success',
     *      'data' => [
     *          [
     *              "userMsg" => ['用户基本信息'],
     *              "user_extend" => ['用户拓展信息']
     *          ]
     *      ]
     * ]
     */
    public function actionInfo()
    {
        $model = User::findOne(\Yii::$app->user->id);
        $user_extend=UserExtend::findOne(['user_id'=>Yii::$app->user->id]);

        if (!$model) {
            return $this->apiReturn(false, '用户不存在！');
        }

        $data=[
            'userMsg'=>ArrayHelper::toArray($model->getBaseInfo()),
            'user_extend'=>ArrayHelper::toArray($user_extend),
        ];

        return $this->apiReturn(true, 'success',$data);
    }


    /**
     * @info 设置背景音乐,背景图片
     * @method POST
     * @param string $extend_bg_img 背景图片
     * @param string $extend_bg_music 背景音乐
     * @return array ['code' => 0, 'message' => '操作成功']
     */
    public function actionUserSetting(){
        $post=\Yii::$app->request->post();

        $res = UserExtend::findOne(['user_id' => \Yii::$app->user->id]);

        $model = $res ? $res : new UserExtend();
        $model->user_id = \Yii::$app->user->id;

        //背景图片
        if(isset($post['extend_bg_img'])){
            $model->extend_bg_img=$post['extend_bg_img'];
        }

        //背景音乐
        if(isset($post['extend_bg_music'])){
            $model->extend_bg_music=$post['extend_bg_music'];
        }

        //隐私设置
        if(isset($post['result'])){
            foreach ($post['result'] as $k=>$v){
                $filed='extend_'.$k;
                $model->$filed=$v=='true'?1:0;
            }
        }

//        die;
       /* if(isset($post['extend_course_is_privacy'])){
            $model->extend_bg_music=$post['extend_course_is_privacy'];
        }

        if(isset($post['extend_topic_is_privacy'])){
            $model->extend_bg_music=$post['extend_topic_is_privacy'];
        }

        if(isset($post['extend_note_is_privacy'])){
            $model->extend_bg_music=$post['extend_note_is_privacy'];
        }

        if(isset($post['extend_attention_is_privacy'])){
            $model->extend_bg_music=$post['extend_attention_is_privacy'];
        }*/

        $model->extend_update_time=time();

        if($model->save()){
            return $this->apiReturn(true, '保存成功');
        }

        return $this->apiReturn(true, '保存失败');
    }

    /**
     * @info 获取用户背景音乐，背景图
     * @method GET
     * @return array
     * [
     *      'code' => 0,
     *      'message' => 'success',
     *      'data' => [
     *          [
     *              "extend_bg_img" => 背景图片,
     *              "extend_bg_music" => 背景音乐
     *          ]
     *      ]
     * ]
     */
    public function actionGetUserSetting(){
        $model=UserExtend::findOne(['user_id'=>\Yii::$app->user->id]);

        if (!$model){
            return $this->apiReturn(false, '请先上传');
        }

        return $this->apiReturn(true, '操作成功', ArrayHelper::toArray($model));
    }


    /**
     * @info 上传头像
     * @method POST
     * @param string $file 上传文件信息
     * @return array ['code' => 0, 'message' => '操作成功']
     */
    public function actionFile(){
        $post = Yii::$app->request->post();

        if(!isset($post['file'])){
            return $this->apiReturn(false, '参数缺失');
        }

        $data=$this->decode($post['file']);

        return $this->apiReturn(true, 'success',$data);

    }

    /**
     * base64解码
     * @param $base64_image_content
     * @param $path
     * @return bool|string
     */
    private function decode($base64_image_content,$config=[]){
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $type = $result[2];

            $dir = isset($config['dir']) ? $config['dir'] : null;

            //文件夹名称
            $path = $dir ? $dir . '/' : 'images' . '/' . date('Y') . '/' . date('m') . '/';
            //相对路径
            $fileHost = \Yii::$app->systemConfig->getValue('FILE_SERVER_HOST', \Yii::$app->request->hostInfo);
            $relative=$fileHost.'/upload/' . $path;
            //绝对目录
            $absoluteDir = Yii::getAlias('@webroot') . '/upload/' . $path;

            //创建文件所在的目录
            if (!BaseFileHelper::createDirectory($absoluteDir)) {
                return false;
            }

            //文件名称
            $fileName=time().rand(100000, 999999).".{$type}";
            //新文件名称
            $new_file = $absoluteDir.$fileName;

            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                //返回的是相对对路径
                return [
                    'code' => 0,
                    'msg' => '上传成功',
                    'data' => $relative.$fileName,
                ];
            }else{
                return [
                    'code' => 1,
                    'msg' => '保存失败！',
                ];
            }
        }else{
            return [
                'code' => 1,
                'msg' => '无效文件',
            ];
        }
    }
}