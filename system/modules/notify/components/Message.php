<?php

namespace system\modules\notify\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use system\modules\user\models\User;
use system\modules\notify\models\NotifyCode;
use system\modules\notify\models\NotifyNode;
use system\modules\notify\models\NotifyMessage;
use system\modules\notify\models\NotifyMessageQueue;

/**
 * Class Message 消息组件，用户发送系统消息和短信，email等
 * 提供了以下几个方法：
 * 发送消息，按照节点设置的发送方式进行发送，可以同时发送系统消息，短信，email，其他消息：
 * send('消息节点', '接收人id或者数组', '消息节点需要的参数', '发送人id,默认0代表系统')
 * 发送系统消息：
 * sendMessage(数据数组，具体看方法)
 * 发送email：
 * sendEmail(数据数组，具体看方法)
 * 发送短信：
 * sendSms(数据数组，具体看方法)
 * 发送验证码：
 * sendCode('发送方式：phone/email', '消息节点', '发送目标:手机号码/email地址', '要验证的用户id,选填')
 * 验证验证码：
 * verifyCode('发送方式，同上', '消息节点', '验证的目标：手机号码/email地址'，'用户提交的code', '要验证的用户id")
 * @package system\modules\notify\components
 */
class Message extends Component
{
    /**
     * 发送消息；可以直接发送，如果直接发送效率低，可以使用队列；
     * 此处默认直接发送
     * @param $toIds  int|array 消息接收人id，这里是用户的id或者id数组
     * @param $node   string 节点名称
     * @param $params array 参数数组，用于解析模板的参数
     * @param $send_id int  发送人id，默认是0代表系统，可以填写用户id
     * @return bool
     */
    public function send($node, $toIds, $params = [], $send_id = 0)
    {
        // 立即发送
        return $this->_sendNow($node, $toIds, $params, $send_id);
    }

    /**
     * 发送系统消息
     * @param $data array 系统消息数组
     * @return bool
     */
    public function sendMessage($data)
    {
        $data_demo = [
            'user_id' => '用户id，必填',
            'content' => '内容，必填',
            'node_name' => '节点名称，选填',
            'module' => '节点所属模块，选填',
            'code' => '路由标识，选填',
            'params' => '路由参数，选填',
            'sender_id' => '发送人id，选填',
        ];
        $model = new NotifyMessage();
        $model->setAttributes($data);
        return $model->save();
    }

    /**
     * 发送短信
     * @param $data array 数据参数
     * @return array
     */
    public function sendSms($data)
    {
        // 触发某个节点，选择模版，然后找到短信配置，进行发送
        // $data 中应该包含 手机号码，短信内容，短信模板，短信参数
        $data_demo = [
            'send_to' => '18611111', // 要发送的手机号
            'content' => '发送内容', // 发送的内容
            'template' => '短信模板', //
            'params' => [], // 参数配置
        ];

        // 获取短信发送实例
        $smsObj = $this->_getSmsObj();

        if (!$smsObj) {
            return [
                'code' => 1,
                'message' => '短信接口配置有误',
            ];
        }

        if (method_exists($smsObj, 'send')) {
            // 发送短信
            $res = $smsObj->send($data);
            if (is_array($res) && isset($res['code'])) {
                return [
                    'code' => $res['code'],
                    'message' => $res['message'],
                ];
            } else {
                return [
                    'code' => 1,
                    'message' => '短信接口返回的数据有问题，请检查',
                ];
            }
        } else {
            return [
                'code' => 1,
                'message' => '短信接口错误，没有找到发送消息的方法',
            ];
        }
    }

    /**
     * 发送email
     * @param $data array 发送的数据数组
     * @return bool
     */
    public function sendEmail($data)
    {
        $data_demo = [
            'send_to' => 'ueek@qq.com',  // 发送给谁
            'subject' => '验证码', // 邮件主题
            'content' => '邮件内容',  // 邮件内容
            'template' => '', // 邮件内容模版
            'params' => [], // 邮件内容参数
            /*'type' => 'text', // 邮件类型：纯文本
            'view' => 'common/view1', // 邮件所用的模版
            'viewParams' => [  // 扩展参数
                'user' => '$model', // 可以传入模版中使用的变量
            ],*/
        ];

        // 获取邮箱的实例对象
        $emailObj = $this->_getEmailObj();
        //var_dump($emailObj);exit;

        $data['content'] = isset($data['template'], $data['params']) ? $this->_parseTemplate($data['template'], $data['params']) : $data['content'];
        return $emailObj->send($data);
    }

    /**
     * 发送验证码
     * @param $send_type string 发送类型： phone手机号，email邮件，其他。。。
     * @param $node string 消息节点：按照node表的设置
     * @param $target string 发送目标，比如手机号码，email等
     * @param int $user_id 用户id，严格模式下需要，比如验证某个用户的phone
     * @return array
     */
    public function sendCode($send_type, $node, $target, $user_id = 0)
    {
        // 获取节点的数据
        //$nodeModel = NotifyNode::findOne(['node_name' => $node]);
        $nodeData = NotifyNode::getOneNode($node);

        if (!$nodeData) {
            return [
                'code' => 1,
                'message' => '消息节点不存在',
            ];
        }

        $template = $nodeData['content'];
        if ($send_type == 'phone') {
            if (!isset($nodeData['send_sms']) || $nodeData['send_sms'] == 0) {
                return [
                    'code' => 1,
                    'message' => '短信未配置',
                ];
            }
            // 使用短信的模板
            if (isset($nodeData['extend_sms_template'])) {
                $template = $nodeData['extend_sms_template'];
            }
        } else {
            if ($send_type == 'email') {
                if (!isset($nodeData['send_email']) || $nodeData['send_email'] == 0) {
                    return [
                        'code' => 1,
                        'message' => '邮件未配置',
                    ];
                }
                // 使用email的模板
                if (isset($nodeData['extend_email_template'])) {
                    $template = $nodeData['extend_email_template'];
                }
            } else {
                return [
                    'code' => 1,
                    'message' => '发送方式未配置',
                ];
            }
        }

        // 6位随机数字验证码
        $code = rand(100000, 999999);

        // 查找最近的一条，如果已经存在，判断时间；如果不超过3分钟，那么不再发送；如果是10分钟以内，那么重新发送相同的code
        /** @var NotifyCode $recordModel */
        $query = NotifyCode::find()
            ->where(['send_type' => $send_type, 'node' => $node, 'target' => $target, 'is_verify' => 0]);
        if ($user_id) {
            // 如果传过来用户id参数，那么要同时验证用户id，比如绑定手机号的时候就需要传入用户，注册的时候就不需要
            $query->andWhere(['user_id' => $user_id]);
        }

        $recordModel = $query->orderBy(['id' => SORT_DESC])->one();
        if ($recordModel) {
            // 如果小于3分钟，那么不再发送
            if (time() - $recordModel->create_at <= 180) {
                return [
                    'code' => 0,
                    'message' => '发送成功，请稍等片刻',
                ];
            } else {
                if (time() - $recordModel->create_at <= 600) {
                    // 如果小于10分钟，那么发送相同的验证码, 以防止有时候延迟造成用户第二次发送以后，收到的第一条验证码无法验证通过
                    $code = $recordModel->code;
                }
            }
        }

        // 存入数据表,插入新的发送记录
        $codeModel = new NotifyCode();
        $codeModel->send_type = $send_type;
        $codeModel->target = strval($target);
        $codeModel->node = $node;
        $codeModel->code = strval($code);
        $codeModel->is_verify = 0;
        if ($user_id) {
            $codeModel->user_id = $user_id;
        }
        if (!$codeModel->save()) {
            return [
                'code' => 1,
                'message' => '生成验证码失败',
            ];
        }

        $params = [
            'code' => $code,
        ];

        $content = $this->_parseTemplate($template, $params);

        // 根据node生成短信内容，公共内容
        $data = [
            'send_to' => $target,     // 发送给谁？
            'template' => $template,  // 模版
            'params' => $params,      // 模版参数
            'content' => $content     // 模板内容
        ];
        //print_r( $data);die;

        if ($send_type == 'phone') {
            // 在这里可以自定义一些参数
            //$data['phone'] = $target; // 手机号码
            // 调用短信发送
            $res = $this->sendSms($data);

        } else {
            if ($send_type == 'email') {
                // 如果存在邮件主题字段，那么用作主题
                if (isset($nodeData['extend_email_subject'])) {
                    $subject = $nodeData['extend_email_subject'];
                } else {
                    $subject = $nodeData['node_info'];
                }
                // 自定义一部分参数
                $data['subject'] = $subject; // 主题
                $res = $this->sendEmail($data);
            } else {
                $res = [];
            }
        }

        if (isset($res) && $res['code'] == 0) {
            return [
                'code' => 0,
                'message' => 'ok',
            ];
        } else {
            print_r($res);die;
            $codeModel->delete();
            Yii::error(json_encode($res), 'sendCode');
            return [
                'code' => 1,
                'message' => '短信发送失败',
            ];
        }
    }

    /**
     * 验证code；可以是短信，也可以是email
     * @param $send_type  string  发送类型
     * @param $node  string 节点名称
     * @param $target   string 发送目标
     * @param $code  string 验证码
     * @param int $user_id 严格验证模式，必须要验证是相同的用户
     * @return array
     */
    public function verifyCode($send_type, $node, $target, $code, $user_id = 0)
    {
        // 根据节点名称，手机号码，验证码 验证是否正确,查找最近的一条记录
        /** @var NotifyCode $recordModel */
        $recordModel = NotifyCode::find()->where([
            'send_type' => $send_type,
            'node' => $node,
            'target' => $target,
            'code' => $code,
            'user_id' => $user_id,
            'is_verify' => 0,
        ])->one();
        if (!$recordModel) {
            return [
                'code' => 1,
                'message' => '验证不通过',
            ];
        }

        // 短信验证码有效期，默认30分钟
        $validity = Yii::$app->systemConfig->getValue('VERIFY_CODE_VALIDITY', 30);
        if (time() - $recordModel->create_at > $validity * 60) {
            return [
                'code' => 1,
                'message' => '验证码已过期',
            ];
        }

        // 状态更改为已验证
        $recordModel->is_verify = 1;
        $recordModel->save();

        return [
            'code' => 0,
            'message' => '验证通过',
        ];
    }

    /**
     * 发送app消息,使用阿里百川
     * @param $data array 数据数组
     * @return array
     */
    public function sendApp($data)
    {
        $res = Alibaichuan::sendMsg($data['content'], $data['user_id']);

        if (!$res) {
            return [
                'code' => 1,
                'message' => 'app发送消息失败',
            ];
        } else {
            return [
                'code' => 0,
                'message' => 'app消息发送成功',
            ];
        }
    }

    /**
     * 发送app消息，即环信消息
     * @param $data array 数据数组
     * @return array
     */
    public function sendAppHuanxin($data)
    {
        //获取令牌
        list($code, $token) = Easemob::getToken();

        if ($code != 200) {
            return [
                'code' => 1,
                'message' => 'app发送消息失败',
            ];
        }
        /* $model = new NotifyMessage();
         $model->setAttributes($data);
         if(!$model->save()){
             return [
                 'code' => 1,
                 'message' => 'app发送消息失败',
             ];
         }*/
        list($status, $message) = Easemob::sendMsg($data['content'], $data['user_id'], $token);

        if ($status != 0) {
            return [
                'code' => 1,
                'message' => 'app发送消息失败',
            ];
        } else {
            return [
                'code' => 0,
                'message' => 'app消息发送成功',
            ];
        }

    }

    // email发送类的实例
    private $_emailObj = null;

    /**
     * 获取email发送类
     * @return bool|null|object
     */
    private function _getEmailObj()
    {
        if (!$this->_emailObj) {
            //获取邮件配置
            $mailConfig = Yii::$app->systemConfig->getValue('MAIL_CONFIG', []);

            if (empty($mailConfig)) {
                return false;
            }

            // 如果没有配置class，那么使用默认类
            if (!isset($mailConfig['class'])) {
                $mailConfig['class'] = '\system\modules\notify\components\SwiftMailer';
            }

            try {
                //实例化邮件发送类
                $this->_emailObj = Yii::createObject($mailConfig);
            } catch (Exception $e) {
                return false;
            }
        }

        return $this->_emailObj;
    }

    // 短信发送类的实例
    private $_smsObj = null;

    /**
     * 获取短信实例类
     * @return bool|null|object
     */
    private function _getSmsObj()
    {
        if (!$this->_smsObj) {
            $smsConfig = \Yii::$app->systemConfig->getValue('SMS_CONFIG', []);

            if (empty($smsConfig)) {
                return false;
            }

            // 如果没有配置class，那么使用默认类
            if (!isset($smsConfig['class'])) {
                $smsConfig['class'] = '\system\modules\notify\components\Alidayu';
            }

            try {
                //实例化短信发送类
                $this->_smsObj = Yii::createObject($smsConfig);
            } catch (Exception $e) {
                return false;
            }
        }

        return $this->_smsObj;
    }

    /**
     * 解析消息模版
     * @param $template string 消息模版
     * @param $params array 消息参数
     * @return mixed
     */
    private function _parseTemplate($template, $params)
    {
        $pattern = [];
        $replace = [];
        foreach ($params as $k => $param) {
            $pattern[] = '/\$\{' . $k . '\}/';
            $replace[] = $param;
        }
        //正则用参数填充模板
        $content = preg_replace($pattern, $replace, $template);
        return $content;
    }

    /**
     * 添加到消息队列
     * @param $node string 节点名称
     * @param $toIds int|array 发送给谁
     * @param $params array 消息参数
     * @return int
     */
    private function _addQueue($node, $toIds, $params)
    {
        // 写到队列中，存成一个用户，还是存成多个? 综合考虑，决定每个用户存储一条记录
        // 存成一个的速度比较快，但是没法单独控制
        // 存成多个，那么需要更多的时间，但是可以每个都可以单独处理
        $users = [];
        if (is_array($toIds)) {
            $toIds = array_unique($toIds);
            $users = $toIds;
        } else {
            $users[] = $toIds;
        }

        // 采用批量导入的形式
        $queueData = [];
        $time = time();
        foreach ($users as $userId) {
            $queueData[] = [$userId, $node, json_encode($params), $time];
        }

        return Yii::$app->db->createCommand()
            ->batchInsert(NotifyMessageQueue::tableName(), ['user_id', 'node_name', 'data', 'created_at'], $queueData)
            ->execute();
    }

    /**
     * 发送消息
     * @param $node string 节点名称
     * @param $toIds int|array 接收人id
     * @param $params array 参数
     * @param $send_id int 发送人id
     * @return bool
     */
    private function _sendNow($node, $toIds, $params, $send_id)
    {
        // 先找到消息节点；如果消息节点不存在，那么不用发送，直接返回
        $nodeData = NotifyNode::getOneNode($node);
        if (!$nodeData) {
            return false;
        }

        // 找到用户
        $usersModels = User::find()->where(['user_id' => $toIds])->all();

        $template = $nodeData['content']; // 默认模板

        foreach ($usersModels as $userModel) {
            /** @var User $userModel */
            // TODO 用户的自定义消息节点设置；
            // 可以配置一个tab_notify_user表来设置每个用户的设置项，如果没有自定义设置，那么使用系统设置

            // 设置相对路径的url
            $url_relate = isset($params['url']) ? $params['url'] : '';
            // 设置绝对路径url
            $url_absolute = '';
            if (isset($params['url'])) {
                if (substr($params['url'], 0, 7) == 'http://') {
                    $url_absolute = $params['url'];
                } else {

                }
            }

            // 判断是否要发送系统消息
            if (isset($nodeData['send_message']) && $nodeData['send_message'] == 1) {
                $content = $this->_parseTemplate($template, $params);
                $this->sendMessage([
                    'user_id' => $userModel->user_id,
                    'node_name' => $nodeData['node_name'],
                    'module' => $nodeData['module'],
                    'content' => $content,
                    'code' => isset($params['code']) ? $params['code'] : '',
                    'params' => isset($params['params']) ? $params['params'] : '',
                    'sender_id' => $send_id,
                ]);
            }

            // 判断是否要发送短信
            if (isset($nodeData['send_sms']) && $nodeData['send_sms'] == 1) {
                // 同时判断用户是否绑定了手机号
                if ($userModel->validation_phone == 1 && $userModel->phone) {
                    if (isset($nodeData['extend_sms_template']) && trim($nodeData['extend_sms_template']) != '') {
                        $sms_template = $nodeData['extend_sms_template'];
                    } else {
                        $sms_template = $template;
                    }

                    $content = $this->_parseTemplate($sms_template, $params);

                    $this->sendSms([
                        'send_to' => $userModel->phone,         // 要发送的手机号
                        'content' => $content,                  // 发送的内容
                        'template' => $sms_template,                // 模板
                        'params' => $params,                         // 参数配置
                    ]);
                }
            }

            // 判断是否要发送email
            if (isset($nodeData['send_email']) && $nodeData['send_email'] == 1) {
                if ($userModel->validation_email == 1 && $userModel->email) {
                    // 如果存在email模板字段，并且字段有值，那么使用email的模板
                    if (isset($nodeData['extend_email_template']) && trim($nodeData['extend_email_template']) != '') {
                        $email_template = $nodeData['extend_email_template'];
                    } else {
                        $email_template = $template;
                    }

                    $content = $this->_parseTemplate($email_template, $params);

                    // 如果存在邮件主题字段，那么用作主题
                    if (isset($params['subject'])) {
                        $subject = $params['subject'];
                    } else {
                        if (isset($nodeData['extend_email_subject'])) {
                            $subject = $nodeData['extend_email_subject'];
                        } else {
                            $subject = $nodeData['node_info'];
                        }
                    }

                    $this->sendEmail([
                        'send_to' => $userModel->email,         // 要发送的手机号
                        'content' => $content,                  // 发送的内容
                        'template' => $email_template,          // email模板
                        'params' => $params,                    // 参数配置
                        'subject' => $subject,                  // 邮件主题
                    ]);
                }
            }

            // 判断是否要发送app消息
            if (isset($nodeData['send_app']) && $nodeData['send_app'] == 1) {
                $content = $this->_parseTemplate($template, $params);
                $this->sendApp([
                    'user_id' => $userModel->username,
                    'node_name' => $nodeData['node_name'],
                    'module' => $nodeData['module'],
                    'content' => $content,
                    'url' => isset($params['url']) ? $params['url'] : '',
                    'sender_id' => $send_id,
                ]);
            }

            // 判断是否要发送企业微信消息，必须要配置agentid参数； TODO 这里的url需要使用绝对路径的url
            if (isset($nodeData['send_qywx']) && $nodeData['send_qywx'] == 1 && isset($nodeData['extend_qywx_agentid']) && $nodeData['extend_qywx_agentid']) {
                // 如果存在企业微信模板字段，并且字段有值，那么使用自定义的模板
                if (isset($nodeData['extend_qywx_template']) && trim($nodeData['extend_qywx_template']) != '') {
                    $qywx_template = $nodeData['extend_qywx_template'];
                } else {
                    $qywx_template = $template;
                }

                $content = $this->_parseTemplate($qywx_template, $params);

                // 发送企业微信，消息类型：text，image，voice，video，file，textcard，news，mpnews；
                // 主要使用：纯文字text，文字卡片textcard，图文news；
                $msgtype = isset($nodeData['extend_qywx_msgtype']) ? $nodeData['extend_qywx_msgtype'] : 'text'; // 消息类型
                $data = [
                    'touser' => $userModel->username, // 用户名
                    'msgtype' => $msgtype, // 消息类型
                    'agentid' => $nodeData['extend_qywx_agentid'], // 企业微信里面应用的id，
                ];
                // 文本
                if ($msgtype == 'text') {
                    $data['text'] = [
                        'content' => $content,
                    ];
                } else {

                    if (isset($params['url'])) {
                        if (substr($params['url'], 0, 7) == 'http://' || substr($params['url'], 0, 8) == 'https://') {
                            $url = $params['url'];
                        } else if (Yii::$app instanceof \yii\console\Application) {
                            $url = \Yii::$app->systemConfig->getValue('SYSTEM_HOST') . '/qywx.php/' . $params['url'];
                        } else {
                            $url = Yii::$app->request->hostInfo . '/qywx.php/' . $params['url'];
                        }
                    } else {
                        $url = '';
                    }

                    if ($msgtype == 'textcard') {
                        $data['textcard'] = [
                            'title' => (isset($nodeData['extend_qywx_title']) && $nodeData['extend_qywx_title']) ? $nodeData['extend_qywx_title'] : $nodeData['node_info'],
                            'description' => $content,
                            //'url' => $url,
                            //"btntxt":"更多"
                        ];
                        if ($url) {
                            $data['textcard']['url'] = $url;
                        }

                    } else {
                        if ($msgtype == 'news') {
                            $data['news']['articles'] = [
                                'title' => (isset($nodeData['extend_qywx_title']) && $nodeData['extend_qywx_title']) ? $nodeData['extend_qywx_title'] : $nodeData['node_info'],
                                'description' => $content,
                                //'url' => $url,
                                'picurl' => isset($nodeData['extend_qywx_picurl']) ? $nodeData['extend_qywx_picurl'] : '',
                                //"btntxt":"更多"
                            ];

                            if ($url) {
                                $data['news']['articles']['url'] = $url;
                            }

                        }
                    }
                }
                //print_r($data);exit;
                Yii::$app->systemQyweixin->sendMessage($data);
                //var_dump($res);
                //var_dump(Yii::$app->systemQyweixin->errCode, Yii::$app->systemQyweixin->errMsg);
            }

            // 判断是否要发送微信公众号消息
            if (isset($nodeData['send_wechat']) && $nodeData['send_wechat'] == 1) {
                $content = $this->_parseTemplate($template, $params);

                if (isset($params['url'])) {
                    if (substr($params['url'], 0, 7) == 'http://' || substr($params['url'], 0, 8) == 'https://') {
                        $url = $params['url'];
                    } else if (Yii::$app instanceof \yii\console\Application) {
                        $url = \Yii::$app->systemConfig->getValue('SYSTEM_HOST') . '/wechat.php/' . $params['url'];
                    } else {
                        $url = Yii::$app->request->hostInfo . '/wechat.php/' . $params['url'];
                    }
                } else {
                    $url = '';
                }

                $data = [
                    'user_id' => $userModel->user_id,
                    'name' => $userModel->realname,
                    'module' => $nodeData['module'],
                    'template_id' => $nodeData['extend_wechat_template_id'],
                    'template_params' => $nodeData['extend_wechat_template_params'],
                    'content' => $content,
                    //'url' => $url
                ];

                if ($url) {
                    $data['url'] = $url;
                }

                Yii::$app->systemWechat->sendMessage($data);
            }

        }
    }

}