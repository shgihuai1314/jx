<?php
/**
 * yd-service.
 * User: ligang
 * Date: 2018/1/15 下午2:17
 */
$bundle = \system\assets\VideoPlayerAsset::register($this);
\system\assets\VideoPlayerIe8Asset::register($this);
?>

<script type="text/javascript">
    $(function () {
        videojs.options.flash.swf = '<?= $bundle->baseUrl?>/video-js.swf';
        videojs.options.techOrder = ["html5","flash"];
    });
</script>

<video
    id="myVideoPlayer"
    class="video-js vjs-big-play-centered"
    preload="auto"
    poster="<?= $videoImg?>"
    >
    <source src="<?= $videoFile?>" type="video/mp4" />
</video>

<script type="text/javascript">

    $(function () {

        //播放器实例
        var player = videojs('#myVideoPlayer', {
            playbackRates: [0.5, 1, 1.5, 2, 3, 10], // 显示播放速率
            //width: '640px', // 高度
            //height: '480px', // 宽度
            auto: true, // 自动播放
            loop: true, // 循环播放
            muted: false,  // 静音
            //poster: '', // 海报图片
            //preload: '', // 是否预加载： auto自动，metadata元数据信息 ，比如视频长度，尺寸等，none  不预加载任何数据，直到用户开始播放才开始下载,
            //children: Array | Object  可选子组件  从基础的Component组件继承而来的子组件，数组中的顺序将影响组件的创建顺序哦。
            /*children: [
                'bigPlayButton',
                'controlBar'
            ],*/
            /*sources: [{
                src: '//path/to/video.mp4',
                type: 'video/mp4'
            }, {
                src: '//path/to/video.webm',
                type: 'video/webm'
            }],*/
            controlBar: {
                volumePanel: {
                    inline: false, // 音量横放
                    vertical: true  // 音量竖条
                },
                //LiveDisplay: true,
                remainingTimeDisplay: true,     // 显示播放剩余时间
                currentTimeDisplay: true,       // 显示当前播放的时间
                durationDisplay: true,
                progressControl: true,          // 显示播放的控制条
                fullscreenToggle: true,         // 显示全屏按钮
                playToggle: true,               // 播放，暂停控制
                muteToggle: false,               // 静音，无效
                timeDivider: true
            }
        });
    });


</script>