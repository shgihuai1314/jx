<?php
/**
 * Created by PhpStorm.
 * User: THINKPAD
 * Date: 2017/9/8
 * Time: 9:37
 */

namespace console\controllers;

use system\core\utils\FileUtil;
use system\modules\main\models\Migration;
use system\modules\main\models\Modules;
use yii\console\Controller;
use yii\helpers\FileHelper;
use Yii;

class ModuleController extends Controller
{
    /**
     * 创建一个新模块
     * @param null $module_id
     */
    public function actionCreate($module_id = null)
    {
        if (!empty($module_id) || $module_id = $this->prompt('Please enter the module_id :')) {
            $dir = Yii::getAlias("@system/modules/$module_id");
            if (file_exists($dir . '/Module.php')) {
                $this->_print('该模块已存在！');
                exit();
            }

            if (FileHelper::createDirectory($dir)) {
                //资源包
                if (FileHelper::createDirectory($dir . '/assets')) {
                    $indexContent = $this->renderFile(Yii::getAlias('@console/views/modules/Assets.php'), [
                        'module_id' => $module_id,
                        'type' => $module_id
                    ]);
                    $mobileContent = $this->renderFile(Yii::getAlias('@console/views/modules/Assets.php'), [
                        'module_id' => $module_id,
                        'type' => 'mobile'
                    ]);
                    file_put_contents($dir . '/assets/' . ucfirst($module_id) . 'Asset.php', $indexContent);
                    file_put_contents($dir . '/assets/MobileAsset.php', $mobileContent);
                }
                //组件
                FileHelper::createDirectory($dir . '/components');
                //控制器
                if (FileHelper::createDirectory($dir . '/controllers')) {
                    $content = $this->renderFile(Yii::getAlias('@console/views/modules/BaseController.php'), compact('module_id'));
                    file_put_contents($dir . '/controllers/BaseController.php', $content);
                }
                //扩展
                FileHelper::createDirectory($dir . '/extend');
                //安装
                if (FileHelper::createDirectory($dir . '/install')) {
                    $content = $this->renderFile(Yii::getAlias('@console/views/modules/Config.php'), compact('module_id'));
                    file_put_contents($dir . '/install/Config.php', $content);
                }
                //数据库文件
                FileHelper::createDirectory($dir . '/migrations');
                //模型
                FileHelper::createDirectory($dir . '/models');
                //静态资源
                if (FileHelper::createDirectory($dir . '/static')) {
                    if (FileHelper::createDirectory($dir . '/static/css')) {
                        file_put_contents($dir . '/static/css/' . $module_id . '.css', '');
                        file_put_contents($dir . '/static/css/mobile.css', '');
                    }
                    FileHelper::createDirectory($dir . '/static/images');
                    if (FileHelper::createDirectory($dir . '/static/js')) {
                        file_put_contents($dir . '/static/js/' . $module_id . '.js', '');
                        file_put_contents($dir . '/static/js/mobile.js', '');
                    }
                }
                //视图
                FileHelper::createDirectory($dir . '/views');
                //挂件
                FileHelper::createDirectory($dir . '/widgets');

                $content = $this->renderFile(Yii::getAlias('@console/views/modules/Module.php'), compact('module_id'));
                file_put_contents($dir . '/Module.php', $content);

                $this->_print('模块创建成功！');
            }
        }

    }

    /**
     * 安装模块
     * @param $modules string 模块名称，可以同时指定多个模块同时安装，如:email,oa,notice, 如果不指定，则安装核心模块；
     * 注意：一定要先安装核心模块，再安装其他模块；
     * TODO 这里应该是自动安装核心模块；或者根据产品的不同，在某个地方指定要安装的模块
     */
    public function actionInstall($modules = '')
    {
        // 安装前先清除缓存，以免因为缓存导致问题
        $this->_clearCache();
        if ($modules) {
            $data = explode(',', $modules);
        } else {
            // 注意顺序不要乱，main里面包含了大量核心的表，notify包含了消息节点表
            // 原则上是先安装基础模块，然后再安装可能会扩展基础模块的其他模块
            $data = ['main', 'notify', 'role', 'position', 'group', 'user', 'cron', 'charts'];
        }

        foreach ($data as $module_id) {
            $this->_print('开始安装 ' . $module_id . ' 模块');

            $migration = new Migration();
            $migration->printType = 1;
            $res = Modules::install($module_id, $migration);
            if ($res['code'] == 0) {
                $this->_print($module_id . ' 模块安装成功');
            } else {
                $this->_print($module_id . ' 模块安装失败!!!' . $res['message']);
            }
            unset($migration);
        }
    }

    // 卸载模块
    public function actionUninstall($modules = '')
    {
        if ($this->confirm(iconv('utf-8', 'gb2312', '确定要卸载 ' . $modules . ' 模块吗？'))) {
            // 安装前先清除缓存，以免因为缓存导致问题
            $this->_clearCache();
            if ($modules) {
                $data = explode(',', $modules);
            } else {
                $data = ['cron', 'notify', 'user', 'group', 'position', 'role', 'main', 'chat'];
            }
            foreach ($data as $module_id) {
                $this->_print('开始还原 ' . $module_id . ' 模块的相关数据库');
                $migration = new Migration();
                $migration->printType = 1;
                $res = Modules::uninstall($module_id, $migration);
                if ($res['code'] == 0) {
                    $this->_print($module_id . ' 模块卸载成功');
                } else {
                    $this->_print($module_id . ' 模块卸载失败!!!' . $res['message']);
                }
                unset($migration);
            }
        }
    }

    /**
     * 清理系统中的缓存
     */
    private function _clearCache()
    {
        set_time_limit(0);
        // 清理运行时缓存：$hostDir="H:/www/service-hall/system/runtime";
        $hostDir = \Yii::getAlias('@system/runtime');
        FileUtil::removeDirectory($hostDir, ['except' => ['logs', '.gitignore']]);
        // 清理console的缓存
        $consoleDir = \Yii::getAlias('@console/runtime');
        FileUtil::removeDirectory($consoleDir, ['except' => ['logs', '.gitignore']], true);
    }

    /**
     * 输出内容，windows系统输出gb2312格式，其他系统输出utf-8格式
     * @param $content
     */
    private function _print($content)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $content = iconv('utf-8', 'gb2312//IGNORE', $content);
        }
        echo $content . "\r\n";
    }

}