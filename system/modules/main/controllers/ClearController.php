<?php

namespace system\modules\main\controllers;

use system\core\utils\FileUtil;

class ClearController extends BaseController
{
    /**
     * 清理缓存
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionIndex()
    {
        if ($type = \Yii::$app->request->get('type')) {
            if ($type == 1) {
                // 清理运行时缓存：$hostDir="H:/www/service-hall/system/runtime";
                $hostDir = \Yii::getAlias('@runtime');
                FileUtil::removeDirectory($hostDir, ['except' => ['logs', '.gitignore']]);
                FileUtil::removeDirectory(\Yii::getAlias('@webroot/data'), ['except' => ['.gitignore'], 'keepDir' => true], true);

                // 清理console的缓存
                $consoleDir = \Yii::getAlias('@console/runtime');
                FileUtil::removeDirectory($consoleDir, ['except' => ['logs', '.gitignore']], true);
                // 清理api的缓存
                $apiDir = \Yii::getAlias('@api/runtime');
                FileUtil::removeDirectory($apiDir, ['except' => ['.gitignore']], true);
            } else if ($type == 2) {
                // 清理日志缓存：$hostDir="H:/www/service-hall/system/runtime/logs";
                $hostDir = \Yii::getAlias('@runtime/logs');
                FileUtil::removeDirectory($hostDir);
                // 清理console的缓存
                $consoleDir = \Yii::getAlias('@console/runtime/logs');
                FileUtil::removeDirectory($consoleDir);
            } else if ($type == 3) {
                // 清理静态文件缓存：$hostDir='H:/www/service-hall/web/assets';
                $hostDir = \Yii::getAlias('@webroot/assets');
                FileUtil::removeDirectory($hostDir, ['except' => ['.gitignore'], 'keepDir' => true], true);
                $cacheVersion = \Yii::$app->systemConfig->getValue('CACHE_VERSION', 1);
                \Yii::$app->systemConfig->setValue('CACHE_VERSION', (string)($cacheVersion + 1));
            }

            return $this->ajaxReturn([
                'code' => 0,
                'message' => '删除成功！',
            ]);
        }

        return $this->render('index');
    }
}

