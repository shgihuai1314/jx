<?php
namespace console\controllers;

use yii\console\Controller;
use console\models\MysqlBackup;
use Yii;

class BackupController extends Controller
{
    public $formUser;
    public $siteName;

    // 可以将备份后的文件发送到指定的email或者ftp去
    public function _init()
    {
        parent::init();
        // 初始化email组件
        $this->formUser = Yii::$app->params['supportEmail'];
        $this->siteName = Yii::$app->name;
        if (Yii::$app->params['smtpUser']) {
            $this->formUser = Yii::$app->params['smtpUser'];
            $this->siteName = Yii::$app->params['siteName'];
            Yii::$app->set('mailer', [
                'class' => 'yii\swiftmailer\Mailer',
                'viewPath' => '@common/mail',
                'transport' => [
                    'class' => 'Swift_SmtpTransport',
                    'host' => Yii::$app->params['smtpHost'],
                    'username' => Yii::$app->params['smtpUser'],
                    'password' => Yii::$app->params['smtpPassword'],
                    'port' => Yii::$app->params['smtpPort'],
                    // 'mail' => Yii::$app->setting->get('smtpMail'), // 显示地址
                    'encryption' => 'tls',
                ],
            ]);
        }
    }

    /**
     * 发送email
     * @param $sqlFile string sql文件
     * @return bool
     */
    private function _sendEmail($sqlFile)
    {
        return Yii::$app->mailer->compose('backup')
            ->setFrom([$this->formUser => $this->siteName . '-机器人'])
            ->setTo(Yii::$app->params['backupEmail'])
            ->setSubject('数据库定时备份系统-' . $this->siteName)
            ->attach($sqlFile)
            ->send();
    }

    /**
     * 备份数据库
     */
    public function actionDb()
    {
        $sql = new MysqlBackup();
        $tables = $sql->getTables();
        Yii::info('数据库备份失败', 'backups');
        if (!$sql->startBackup()) {
            //render error
            Yii::info('数据库备份失败', 'backup');
            die;
        }

        foreach ($tables as $tableName) {
            $sql->getColumns($tableName);
        }

        foreach ($tables as $tableName) {
            $sql->getData($tableName);
        }
        $sqlFile = $sql->endBackup();

        //$this->_sendEmail($sqlFile);
        Yii::info('数据库备份成功', 'backup');
    }
}
