<?php
use yii\helpers\Html;
use system\modules\main\models\Menu;

$menu = Menu::getMenu();
$first_menu_id = current(array_keys($menu));
/*foreach ($menu as $key => $val) {
    $id[] = $key;
}*/

/** @var \yii\web\View $this */
$this->title = Yii::$app->systemConfig->getValue('SYSTEM_NAME');

\system\assets\IconFontAsset::register($this);
\system\assets\CookieAsset::register($this);
\system\assets\SoundAsset::register($this);
$frameBundle = \system\modules\main\assets\MainFrameAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">

    <?php $this->head() ?>
    <script type="text/javascript">
        <?php $this->beginBlock('mainJs');?>
        var navs = <?= json_encode($menu)?>;
        var basePath = '<?= $frameBundle->baseUrl ?>';
        var id = '<?= $first_menu_id //isset($id[0]) ? $id[0] : ''?>';
        var spread_default = <?= Yii::$app->systemConfig->getValue('MAIN_FRAME_NAV_SPREAD', 0)?>; // 是否默认展开
        // 未读消息
        var getNotifyUrl = '<?= \yii\helpers\Url::toRoute(['/notify/user/not-read'])?>';

        // 如果存在父框架，那么刷新
        if (self !== top) {
            top.location.href = self.location.href;
        }
        <?php $this->endBlock();?>
        <?php $this->registerJs($this->blocks['mainJs'], \yii\web\View::POS_BEGIN)?>
    </script>
</head>
<body class="bgcolor-f8">
<?php $this->beginBody() ?>
<div class="layui-layout layui-layout-admin" style="border-bottom: solid 4px #1988fa;">
    <div class="layui-header header header-demo">
        <div class="layui-main">
            <div class="admin-login-box">
                <a class="logo" style="left: 0; line-height: 59px; top: 0;" href="<?= Yii::$app->getHomeUrl() ?>"
                   title="<?= Yii::$app->systemConfig->getValue('SYSTEM_NAME') ?>">
                    <!--<span style="font-size: 22px;"></span>-->
                    <img src="<?= Yii::$app->systemConfig->getValue('SYSTEM_LOGO', Yii::$app->request->hostInfo . '/static/images/logo.png') ?>" style="max-width: 185px;max-height: 59px;">
                </a>
                <div class="admin-side-toggle" title="隐藏菜单栏">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </div>
                <div class="admin-side-full" title="全屏显示">
                    <i class="fa fa-arrows-alt" aria-hidden="true"></i>
                </div>
            </div>
            <!--一级菜单layui-this-->
            <div class="beg-layout-menu">
                <ul class="beg-layout-nav" id="menu-nav">
                    <?php foreach ($menu as $key => $val): ?>
                        <li class="layui-nav-item <?= $val['menu_id'] == $first_menu_id ? "layui-this" : '' ?>"
                            data-first_id="<?= $val['menu_id'] ?>">
                            <a id="items"<?= empty($val['children']) ? 'href=' . $val['path'] : 'href="javascript:;"' ?>>
                                <i class="<?= $val['icon'] ?>" aria-hidden="true"></i>
                                <cite><?= $val['menu_name'] ?></cite>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <!--<span class="layui-nav-bar" style="left: 135px; top: 55px; width: 0px; opacity: 0;"></span>-->
                </ul>
            </div>

            <!--消息提示  管理员信息-->
            <div class="layui-nav admin-header-item">
                <div class="layui-nav-item pr" id="userMsg">
                    <a class="msg-bd pr"
                       href="javascript:openUrl('<?= \yii\helpers\Url::toRoute('/notify/user/index') ?>', '我的消息', 'iconfont icon-bell', true);">
                        <i class="iconfont icon-bell" aria-hidden="true"></i>
                        <span id="notifyCount"></span>
                    </a>
                    <div id="notifyList" class="bgcolor-ff layui-nav-child"></div>
                </div>

                <div class="layui-nav-item pr" id="uesrName">
                    <a class="user-bd" href="javascript:;">
                        <img class="img-circle mt10 fl" src="<?= Yii::$app->user->identity->avatar ?>"/>
                    </a>
                    <dl class="layui-nav-child">
                        <dd class="avatar-nav-hd">
                            <a  class="bgcolor-f8" href="#">
                                <img class="img-circle mt10 fl" width="40" height="40"
                                     src="<?= Yii::$app->user->identity->avatar ?>"/>
                                <strong><?= Yii::$app->user->identity->realname ?></strong>
                            </a>
                        </dd>
                        <dd>
                            <a href="javascript:openUrl('<?= \yii\helpers\Url::toRoute(['/user/info/update']) ?>', '个人信息', 'fa fa-address-book-o');">
                                <i class="fa fa-fw fa-address-book-o" aria-hidden="true">
                                </i> 个人信息
                            </a>
                        </dd>
                        <dd>
                            <a href="javascript:openUrl('<?= \yii\helpers\Url::toRoute(['/user/info/password']) ?>', '修改密码', 'fa fa-lock');">
                                <i class="fa fa-fw fa-lock" aria-hidden="true">
                                </i> 修改密码
                            </a>
                        </dd>
                        <dd>
                            <a href="<?= \yii\helpers\Url::toRoute(['/user/default/logout']) ?>">
                                <i class="fa fa-fw fa-sign-out" aria-hidden="true"></i> 退出</a>
                        </dd>
                    </dl>
                </div>
            </div>
            <ul class="layui-nav admin-header-item-mobile">
                <li class="layui-nav-item">
                    <a href="<?= \yii\helpers\Url::toRoute(['/user/default/logout']) ?>">
                        <i class="fa fa-sign-out" aria-hidden="true"></i>登出</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="layui-side layui-bg-black" id="admin-side">
        <div class="layui-side-scroll" id="admin-navbar-side" lay-filter="side"></div>
    </div>
    <div class="layui-body" style="bottom: 0;border-left: solid 2px #1988fa;" id="admin-body">
        <div class="layui-tab admin-nav-card layui-tab-brief" lay-filter="admin-tab">
            <!--点击左边按钮-->
            <div class="go-left key-press pressKey" id="titleLeft" title="滚动至最右侧">
                <i class="layui-icon">&#xe603;</i>
            </div>

            <ul class="layui-tab-title" id="add_tab">
                <li class="layui-this">
                    <i class="fa fa-home" aria-hidden="true"></i>
                    <cite>欢迎登录</cite>
                </li>
            </ul>

            <!--点击右边按钮-->
            <div class="go-right key-press pressKey" id="titleRight" title="滚动至最左侧">
                <i class="layui-icon">&#xe602;</i>
            </div>

            <!--tab选显卡是定位 不占高度 需要撑起-->
            <div class="width-screen bgcolor-ff" style="height:40px;border-bottom: 1px solid #ddd;"></div>
            <div class="layui-tab-content" style="min-height: 150px; padding: 0;">
                <div class="layui-tab-item layui-show">
                    <iframe frameborder="0" src="<?= \yii\helpers\Url::to(['/main/default/welcome']) ?>"></iframe>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-footer footer footer-demo" id="admin-footer">
        <div class="layui-main">
            <p>©<?= date('Y') ?> <?= Yii::$app->systemConfig->getValue('COMPANY_NAME') ?> 版权所有 版本：V<?= VERSION ?></p>
        </div>
    </div>
    <div class="site-tree-mobile layui-hide">
        <i class="layui-icon">&#xe602;</i>
    </div>
    <div class="site-mobile-shade"></div>

    <!--锁屏模板 start-->
    <script type="text/template" id="lock-temp">
        <div class="admin-header-lock" id="lock-box">
            <div class="admin-header-lock-img">
                <img src="/static/images/0.jpg"/>
            </div>
            <div class="admin-header-lock-name" id="lockUserName">hello</div>
            <input type="text" class="admin-header-lock-input" value="输入密码解锁.." name="lockPwd" id="lockPwd"/>
            <button class="layui-btn  layui-btn-sm" id="unlock">解锁</button>
        </div>
    </script>
    <!--锁屏模板 end -->
</div>
<div id="sound" style="display: none;"></div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

