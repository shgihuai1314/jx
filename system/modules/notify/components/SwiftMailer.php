<?php
/*邮件发送组件*/

namespace system\modules\notify\components;

use Yii;
use yii\swiftmailer\Mailer;

class SwiftMailer extends Mailer
{

    //public $viewPath = '@common/mail';

    // smtp 服务器
    public $host = '';
    // 服务器端口，默认是25
    public $port = '25';
    // 登录用户名
    public $username = '';
    // 登录密码
    public $password = '';
    // 从哪个账号发出
    public $from = '';
    // 发现人名称
    public $fromName = '';
    public $encryption = 'tls'; // 25端口时使用，如果是465/994端口，那么应该是ssl

    private $_emailObj = null;

    public function init()
    {
        parent::init();

        $this->_emailObj = Yii::createObject([
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                // 如果是163邮箱，host改为smtp.163.com
                'host' => $this->host,
                // 邮箱登录帐号
                'username' => $this->username,
                // 如果是qq邮箱，这里要填写第三方授权码，而不是qq登录密码，参考qq邮箱的帮助文档
                //http://service.mail.qq.com/cgi-bin/help?subtype=1&&id=28&&no=1001256
                'password' => $this->password,
                'port' => $this->port,
                'encryption' => $this->encryption,
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => [
                    $this->from => $this->fromName
                ]
            ],
        ]);
    }

    /**
     * 发送email
     * 如果出现问题，请注意邮件内容是否为空
     * @param \yii\mail\MessageInterface $data
     * @return bool
     */
    public function send($data)
    {
        $data_demo = [
            'send_to' => '发送到哪个email',
            'subject' => '主题',
            'content' => '内容',
        ];
        /** @var SwiftMailer $emailObj */
        $emailObj = $this->_emailObj;

        if (isset($data['type']) && $data['type'] == 'html') {
            // 设置html模板 TODO 模板
//            $mailer = $emailObj->compose($data['view'], $data['viewParams']);
            $mailer = $emailObj->compose();
            $mailer->setHtmlBody($data['content']);
        } else {
            // text模版；TODO text形式
            $mailer = $emailObj->compose();
            $mailer->setTextBody($data['content']); //发布纯文字文本
        }

        $mailer->setTo($data['send_to']); //要发送的邮箱
        $mailer->setSubject($data['subject']); //邮件主题
        return $mailer->send();
    }

}