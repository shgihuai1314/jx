<?php
/**
 * Created by PhpStorm.
 * User: THINKPAD
 * Date: 2017/8/17
 * Time: 16:07
 */

namespace system\modules\main\components;

use system\modules\main\models\Modules;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii;

class LoadModule extends Component implements BootstrapInterface
{
    /**
     * 随系统一起启动
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        // 检查数据库和核心表是否存在
        if (APP_NAME != 'console' && !self::checkDb()) {
            if (strpos(Yii::$app->request->pathInfo, 'install/') === 0) {
                return ;
            };
            header('Location: /admin.php/install/');
            exit;
        };

        // 根据app获取可以使用的模块
        $modules = Modules::getModulesByApp();

        foreach ($modules as $item) {
            $path = Yii::getAlias('@system') . '/modules/' . $item['module_id'] . '/install/Config.php';
            //print_r($path);
            //配置模型
            if (file_exists($path)) {
                try {
                    $config = require $path;
                   //print_r($config['modules']);
                    if (isset($config['modules']) && $config['modules']) {
                        Yii::$app->setModules($config['modules']);
                    } else {
                        // 没有写模块，是否有默认值？
                    }

                    // 加载组件
                    if (isset($config['components']) && $config['components']) {
                        Yii::$app->setComponents($config['components']);
                    }

                    // 绑定类事件
                    if (isset($config['event']) && $config['event']) {
                        foreach ($config['event'] as $key => $value) {
                            foreach ($value as $key2 => $value2) {
                                yii\base\Event::on($key, $key2, $value2);
                                Yii::info('绑定了事件:'.$key.'::'.$key2);
                            }
                        }
                    }

                } catch (\Exception $e) {
                    //print_r($e);die;
                   // var_dump($path);exit;
                }
            }
        }
    }

    /**
     * 检查数据库和核心表是否存在
     * @return bool
     * @throws yii\base\InvalidConfigException
     * @throws yii\db\Exception
     */
    public static function checkDb()
    {
        $config = [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . env('hostname') . ';port=' . env('port'),
            'username' => env('username'),
            'password' => env('password'),
            'charset' => env('db_encode'),
        ];

        /** @var yii\db\Connection $db */
        $db = Yii::createObject($config);
        $db->open();

        // 检查数据库是否存在
        $res = $db->createCommand('show databases like "' . env('dbname') . '"')->queryAll();
        if (!$res) {
            return false;
        } else {
            // 检查数据库中表是否为空
            $db->createCommand('use ' . env('dbname'))->execute();
            $tables = $db->createCommand("show tables like 'tab_%'")->queryAll();
            if (!$tables) {
                return false;
            }
        }

        return true;
    }
}
