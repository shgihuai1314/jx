/**
 * index.js
 * main菜单布局
 */

var tab, $, layer, element, navbar;
if (self == top) {

layui.config({
    base: basePath+'/js/',
    version: new Date().getTime()
}).use(['element', 'layer', 'navbar', 'tab'], function () {
        element = layui.element,
            $ = layui.jquery,
            layer = layui.layer,
            navbar = layui.navbar();
        tab = layui.tab({
            elem: '.admin-nav-card' //设置选项卡容器
            /*, maxSetting: {
             max: 10
             //, tipMsg: '只能开10个哇，不能再开了'
             }*/
            , contextMenu: true
        });

        //iframe自适应
        $(window).on('resize', function () {
            var $content = $('.admin-nav-card .layui-tab-content');
            $content.height($(this).height() - 143);
            $content.find('iframe').each(function () {
                $(this).height($content.height());
            });

            // 左右切换
            var ispeed = 0;//右边点击的时候移动的距离

            $('.pressKey').bind("click", function () {

                var ELEM = $(".layui-tab-title");//操作的对象
                var tabWidth = $(".layui-tab").width() - $("#titleLeft").width() - $("#titleRight").width() - 2;//可视区ul的宽度
                var ulWidth = ELEM.width();//ul的宽度

                var removeUlWidth;//移动之后剩余的宽度

                if ($(this).attr('id') == 'titleLeft') {


                    if (ulWidth >= tabWidth) {

                        ispeed -= 500;

                        var minIspeed = 0;//能移动都左边最大的距离

                        removeUlWidth = ulWidth - (ispeed);//移动之后剩余的宽度

                        //切换至最左侧
                        if (ispeed >= minIspeed) {

                            if (removeUlWidth > tabWidth) {//当移动之后剩余的ul的宽度大于可视区的宽度

                                ELEM.css("marginLeft", -ispeed + "px");
                            } else {
                                ispeed = minIspeed;
                                ELEM.css("marginLeft", -ispeed + "px");
                            }

                        } else {
                            ispeed = minIspeed;
                            ELEM.css("marginLeft", -ispeed + "px");
                            // //console.log("我是左边最后一轮的宽度了");
                        }
                    }
                }
                if ($(this).attr('id') == 'titleRight') {

                    if (ulWidth >= tabWidth) {

                        var maxIspeed = (ulWidth - tabWidth);//能移动的最大的距离

                        ispeed += 500;//移动的速度

                        removeUlWidth = ulWidth - ispeed;//移动之后剩余的距离

                        if (ispeed <= maxIspeed) {
                            if (removeUlWidth > tabWidth) {//当移动之后剩余的ul的宽度大于可视区的宽度
                                ELEM.css("marginLeft", -ispeed + "px");
                            } else {
                                ispeed = maxIspeed;
                                ELEM.css("marginLeft", -ispeed + "px");
                            }
                        } else {
                            ispeed = maxIspeed;
                            ELEM.css("marginLeft", -ispeed + "px");
                            // //console.log("我是右边最后一轮的宽度了");
                        }
                    }
                }
            });

        }).resize();

        //一级菜单样式切换
        $("#menu-nav .layui-nav-item").click(function () {
            var id = $(this).data('first_id');
            $(this).addClass("layui-this").siblings().removeClass('layui-this');

            renderNav(id);
        });
        renderNav(id);
        /*var nav_test = {

         };*/

        function renderNav(first_id) {
            if (first_id == '') {
                return false;
            }
            navbar.set({
                spreadOne: false, // 只展开一个二级菜单
                elem: '#admin-navbar-side',
                cached: true,
                data: navs[first_id].children,
                // data:nav_test[first_id],
                /*cached:true,
                 url: 'datas/nav.json'*/
            });

            //渲染navbar
            navbar.render();

            //监听点击事件
            navbar.on('click(side)', function (data) {
                tab.tabAdd(data.field);

                // 左右自动切换
                var ELEM = $(".layui-tab-title");//操作的对象
                var ELEMWidth = ELEM.find(".layui-this").outerWidth(true);//li自身的宽度
                var tabWidth = $(".layui-tab").width() - $("#titleLeft").width() - $("#titleRight").width() - 2;//可视区的宽度
                var ulWidth = ELEM.width();//ul当前的宽度
                var liMarginLeft = ELEM.find(".layui-this").offset().left
                    - $("#admin-side").outerWidth(true) - $("#titleLeft").outerWidth(true) - 2;//得到当前的li到左边的距离


                if (liMarginLeft < 0) {//li到左边的距离小于可视区
                    var prevsumWidth = 0;//当前里前面的所有的li的宽度

                    var LiPrevAll = ELEM.find(".layui-this").prevAll("li");

                    LiPrevAll.each(function () {
                        prevsumWidth += $(this).outerWidth(true);
                    });


                    ELEM.css("marginLeft", -prevsumWidth + "px");//当前点击的li前面的所有的li

                } else if (liMarginLeft + ELEMWidth > tabWidth) {//li到左边的距离大于可视区

                    var nextsumWidth = 0;//当前里前面的所有的li的宽度

                    var LiPrevAll = ELEM.find(".layui-this").nextAll("li");//当前点击的li的后面的所有li的

                    LiPrevAll.each(function () {
                        nextsumWidth += $(this).outerWidth(true);
                    });

                    ELEM.css("marginLeft", -(ulWidth - tabWidth - nextsumWidth) + "px");

                } else {

                    return;//不大于也不下于的时候不做处理
                }

            });
        }

        //设置navbar
        /*navbar.set({
         spreadOne: false, // 只展开一个二级菜单
         elem: '#admin-navbar-side',
         cached: true,
         data: navs
         /!*cached:true,
         url: 'datas/nav.json'*!/
         });
         //渲染navbar
         navbar.render();*/


        $('.admin-side-toggle').on('click', function () {
            var sideWidth = $('#admin-side').width();
            if (sideWidth === 200) {
                $('#admin-body').animate({
                    left: '0'
                }); //admin-footer
                $('#admin-footer').animate({
                    left: '0'
                });
                $('#admin-side').animate({
                    width: '0'
                });
            } else {
                $('#admin-body').animate({
                    left: '200px'
                });
                $('#admin-footer').animate({
                    left: '200px'
                });
                $('#admin-side').animate({
                    width: '200px'
                });
            }
        });
        $('.admin-side-full').on('click', function () {
            var docElm = document.documentElement;
            //W3C
            if (docElm.requestFullscreen) {
                docElm.requestFullscreen();
            }
            //FireFox
            else if (docElm.mozRequestFullScreen) {
                docElm.mozRequestFullScreen();
            }
            //Chrome等
            else if (docElm.webkitRequestFullScreen) {
                docElm.webkitRequestFullScreen();
            }
            //IE11
            else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
            layer.msg('按Esc即可退出全屏', {offset: '150px'});
        });

        //锁屏
        $(document).on('keydown', function () {
            var e = window.event;
            if (e.keyCode === 76 && e.altKey) {
                //alert("你按下了alt+l");
                lock($, layer);
            }
        });
        $('#lock').on('click', function () {
            lock($, layer);
        });

        //手机设备的简单适配
        var treeMobile = $('.site-tree-mobile'),
            shadeMobile = $('.site-mobile-shade');
        treeMobile.on('click', function () {
            $('body').addClass('site-mobile');
        });
        shadeMobile.on('click', function () {
            $('body').removeClass('site-mobile');
        });

        // 消息查询
        var getNewNotify = function () {
            $.get(getNotifyUrl, function (res) {
                //var res = JSON.parse(res);
                if (res.code == 0) {
                    if (res.data.count > 0) {
                        $("#notifyCount").html('<span class="badge layui-bg-red">'+res.data.count+'</span>');
                    } else {
                        $("#notifyCount").html('');
                    }
                    $("#notifyList").html(res.data.html);

                    var oldCount = Cookies.get('notifyCount');
                    if (res.data.count > oldCount) {
                       // playNotify();
                        layer.msg('您有新的消息，请注意查看', {offset: '150px'});
                    }
                    Cookies.set('notifyCount', res.data.count);
                }
            }, 'json')
        };

        // 欢迎页面动态打开
        //openUrl('/main/default/welcome', '欢迎登录', 'fa fa-home');

        // @TODO 生产环境下 60s取一次数据
        getNewNotify();
        /*setInterval(function () {
            getNewNotify();
        }, 10*1000);*/
        $('#notifyList').on('click', '.moduleNotifyList', function () {
            var url = $(this).data('url');
            openUrl(url, '我的消息', 'iconfont icon-bell', true);
        });
    }
);

var isShowLock = false;
function lock($, layer) {
    if (isShowLock)
        return;
    //自定页
    layer.open({
        title: false,
        type: 1,
        closeBtn: 0,
        anim: 6,
        content: $('#lock-temp').html(),
        shade: [0.9, '#393D49'],
        success: function (layero, lockIndex) {
            isShowLock = true;
            //给显示用户名赋值
            layero.find('div#lockUserName').text('admin');
            layero.find('input[name=lockPwd]').on('focus', function () {
                var $this = $(this);
                if ($this.val() === '输入密码解锁..') {
                    $this.val('').attr('type', 'password');
                }
            })
                .on('blur', function () {
                    var $this = $(this);
                    if ($this.val() === '' || $this.length === 0) {
                        $this.attr('type', 'text').val('输入密码解锁..');
                    }
                });
            //在此处可以写一个请求到服务端删除相关身份认证，因为考虑到如果浏览器被强制刷新的时候，身份验证还存在的情况
            //do something...
            //e.g.
            /*
             $.post(url,params,callback,'json');
             */
            //绑定解锁按钮的点击事件
            layero.find('button#unlock').on('click', function () {
                var $lockBox = $('div#lock-box');

                var userName = $lockBox.find('div#lockUserName').text();
                var pwd = $lockBox.find('input[name=lockPwd]').val();
                if (pwd === '输入密码解锁..' || pwd.length === 0) {
                    layer.msg('请输入密码..', {
                        icon: 2,
                        time: 1000,
                        offset: '150px', anim: 6
                    });
                    return;
                }
                unlock(userName, pwd);
            });
            /**
             * 解锁操作方法
             * @param {String} 用户名
             * @param {String} 密码
             */
            var unlock = function (un, pwd) {
                //这里可以使用ajax方法解锁
                /*$.post('api/xx',{username:un,password:pwd},function(data){
                 //验证成功
                 if(data.success){
                 //关闭锁屏层
                 layer.close(lockIndex);
                 }else{
                 layer.msg('密码输入错误..',{icon:2,time:1000});
                 }
                 },'json');
                 */
                isShowLock = false;
                //演示：默认输入密码都算成功
                //关闭锁屏层
                layer.close(lockIndex);
            };
        }
    });
};

// 打开一个tab
function openUrl(url, title, icon, refresh) {
    //这是核心的代码。
    tab.tabAdd({
        href: url, //地址
        icon: icon,
        title: title,
        refresh: refresh
    });
}

/*function openUrl(url, title, icon, refresh) {
    //这是核心的代码。
    parent.tab.tabAdd({
        href: url, //地址
        icon: icon,
        title: title,
        refresh: refresh
    });
}*/

// 播放消息提示
//swfobject.embedSWF(basePath + "/static/lib/SwfObject/sound.swf", "sound", "1", "1", "9.0.0", basePath + "/static/lib/SwfObject/expressInstall.swf", {}, {wmode: "transparent"}, {});

// function playNotify() {
//     var sound = swfobject.getObjectById("sound");
//     if (sound) {
//         sound.SetVariable("f", basePath + '/static/lib/SwfObject/msg.mp3');
//         sound.GotoFrame(1);
//     }
// }
}
