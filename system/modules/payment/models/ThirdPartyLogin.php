<?php
/**
 * Created by PhpStorm.
 * User: 01
 * Date: 2019/9/23
 * Time: 10:44
 */

namespace system\modules\payment\models;


use system\core\utils\Tool;
use system\models\Model;
use system\modules\user\components\UserIdentity;
use system\modules\user\models\User;
use system\modules\user\models\UserExtend;

class ThirdPartyLogin extends Model
{
    protected static $appid = '';
    protected static $appKey = '';

    /**
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
     * @return
     */
    public static function GetOpenid($code = '')
    {
        if (!$code) {
            return false;
        }

        //获取openid
        $data = self::getOpenidFromMp($code);

        if (isset($data['errcode'])) {
            return false;
        }

        //获取用户信息
        $res=self::addUserInfo($data);

        if($res){
            return $res;
        }

        return false;




        //通过code获得openid
        /* if (!isset($_GET['code'])){
             //触发微信返回code码
             $baseUrl = \Yii::$app->request->hostInfo.\Yii::$app->request->url;
             $url = $this->__CreateOauthUrlForCode($baseUrl);

             Header("Location: $url");
             exit();
         } else {
             //获取code码，以获取openid
             $code = $_GET['code'];
             $openid = $this->getOpenidFromMp($code);
             return $openid;
         }*/
    }

    /**
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     * @return openid
     */
    public static function GetOpenidFromMp($code)
    {
        $url = self::__CreateOauthUrlForOpenid($code);
        $res = Tool::httpcode($url);
        $data = json_decode($res, true);
        return $data;
    }

    /**
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     * @return string
     */
    private static function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = self::$appid;
        $urlObj["secret"] = self::$appKey;
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = self::ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
    }

    /**
     * 拼接签名字符串
     * @param array $urlObj
     * @return string
     */
    private static function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") $buff .= $k . "=" . $v . "&";
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 获取用户信息
     * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在该公众号下的Openid
     * @return string
     */
    public static function getUserInfo($openid, $access_token)
    {
        $response = Tool::httpcode('https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN');
        return json_decode($response, true);
    }

    /**
     * 添加用户信息
     * @param $data
     * @return array|bool|int
     */
    public static function addUserInfo($data)
    {

        if (!$data['openid']) {
            return false;
        }
        //查询用户信息
        $model = UserExtend::findOne(['extend_openid' => $data['openid']]);
        if(!empty($model)){
            $user = UserIdentity::findOne($model->user_id);
            \Yii::$app->user->login($user);
            return true;
        }else{
            \Yii::$app->session->set('wechat_openid', $data['openid']);
            $user = self::getUserInfo($data['openid'], $data['access_token']);
            //保存用户信息用户数据表
            $model = new User();
            $model->name = '';
            $model->realname = isset($user['nickname']) ? $user['nickname'] : '';
            $model->gender = isset($user['sex']) ? $user['sex'] : '';
            $model->avatar = isset($user['headimgurl']) ? $user['headimgurl'] : '';
            $user_id = $model->save();

            if ($user_id) {
                return false;
            }

            $useModel = new UserExtend();
            $useModel->user_id =$model->user_id;
            $useModel->extend_update_time = time();
            $useModel->extend_openid = isset($user['openid']) ? $user['openid'] : '';
            $useModel->extend_province = isset($user['province']) ? $user['province'] : '';
            $useModel->extend_city = isset($user['city']) ? $user['city'] : '';
            $useModel->extend_country = isset($user['country']) ? $user['country'] : '';
            $useModel->unionid = isset($user['unionid']) ? $user['unionid'] : '';

            if(!$useModel->save()){
                return false;
            }
            return true;
        }

    }
}