<?php
/**
 * yd-service.
 * User: ligang
 * Date: 2018/1/23 下午5:44
 */
namespace system\modules\main\components;

use system\core\utils\Tool;
use yii\base\Component;
use yii;

class Ffmpeg extends Component
{
    private $ffmpeg_cmd = '/usr/local/bin/ffmpeg';// ffmpeg命令的路径

    public function init()
    {
        parent::init();

        // 定义FFmpeg的路径
        $this->ffmpeg_cmd = \Yii::$app->systemConfig->getValue('MEDIA_FFMPEG_CMD', '/usr/local/bin/ffmpeg');
    }

    /**
     * 屏幕截图(截取多张)
     * @param $video_file string 视频文件路径
     * @param $img_file string 截图保存路径
     * @param string $time 要截取的时间点，如果为空，那么截取第一帧
     */
    public function screenshots($video_file, $img_file, $file_thumb)
    {
        // 获取视频的分辨率，然后按照比例进行缩放，比如图片可以保持：500*307
        // 命令：获取指定时间的截图 ffmpeg -i xihumeijing.mp4  -vframes 1 -ss 00:01:00 -y -f mjpeg xihumeijing.jpg
        // 获取第一帧的图片 ffmpeg -i tuzi.mp4 -y -f image2 -t 0.001  tuzi2.jpg
        // 把宽度和高度按照比例限定一下
        $videoInfo = $this->getVideoInfo($video_file);

        $parsed = date_parse($videoInfo['duration']);
        $seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
        $num = round($seconds / 40);
        if ($num <= 160) {
            $num = round($seconds / 4);
        }

        $width = 500;
        $height = 308; // 默认的宽度和高度
        if (isset($videoInfo['width'], $videoInfo['height'])) {
            $arr = $this->_resetSize($videoInfo['width'], $videoInfo['height'], 500);
            if (isset($arr['width'], $arr['height'])) {
                $width = $arr['width'];
                $height = $arr['height'];
            } else {
                $width = $videoInfo['width'];
                $height = $videoInfo['height'];
            }
        }

        //ffmpeg -i "./cz.mp4" -ss 00:00:02 -t 00:00:04 -r 1 -f image2 -vf fps=fps=1 yiba_frame_%02d.png
        $command = $this->ffmpeg_cmd . "  -i {$video_file} -s {$width}*{$height} -y -f image2 -vframes 4 -vf fps=fps=1/{$num} {$img_file}%02d.png";
        $res = shell_exec($command);

        //var_dump($this->getVideoInfo($video_file));
    }

    /**
     * 屏幕截图
     * @param $video_file string 视频文件路径
     * @param $img_file string 截图保存路径
     * @param string $time 要截取的时间点，如果为空，那么截取第一帧
     */
    public function screenshot($video_file, $img_file, $time = '')
    {
        // 命令：获取指定时间的截图 ffmpeg -i xihumeijing.mp4  -vframes 1 -ss 00:01:00 -y -f mjpeg xihumeijing.jpg
        // 获取第一帧的图片 ffmpeg -i tuzi.mp4 -y -f image2 -t 0.001  tuzi2.jpg
        if ($time) {
            $command = sprintf('%s -i %s  -vframes 1 -ss %d -y -f mjpeg %s', $this->ffmpeg_cmd, $video_file, $time, $img_file);
        } else {
            $command = sprintf('%s -i %s -y -f image2 -t 0.001 %s', $this->ffmpeg_cmd, $video_file, $img_file);
        }
        $res = shell_exec($command);
        //var_dump($res);
        //var_dump($this->getVideoInfo($video_file));
    }

    /**
     * 根据给定的宽高和最大值计算出缩放后的宽高
     * @param $width
     * @param $height
     * @param $max
     * @return array|bool
     */
    private function _resetSize($width, $height, $max)
    {
        // 原始图片小于最大尺寸，则无需压缩
        if ($width <= $max && $height <= $max) {
            return false;
        }

        if ($width > $height) {
            $height = $height * ($max / $width);
            $width = $max;
        } else {
            $width = $width * ($max / $height);
            $height = $max;
        }

        return [
            'width' => floor($width),
            'height' => floor($height)
        ];
    }

    /**
     * 获取视频信息
     * @param $video_file string 视频文件路径
     * @return array
     */
    public function getVideoInfo($video_file)
    {
        $command = sprintf('%s -i %s 2>&1', $this->ffmpeg_cmd, $video_file);

        $video_info = shell_exec($command);

        // 使用输出缓冲，获取ffmpeg所有输出内容
        $ret = array();

        // Duration: 00:33:42.64, start: 0.000000, bitrate: 152 kb/s
        if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $video_info, $matches)) {
            //print_r($matches);//exit;
            $ret['duration'] = $matches[1]; // 视频长度
            $duration = explode(':', $matches[1]);
            $ret['seconds'] = $duration[0] * 3600 + $duration[1] * 60 + intval($duration[2]); // 转为秒数
            $ret['start'] = $matches[2]; // 开始时间
            $ret['bitrate'] = $matches[3]; // bitrate 码率 单位kb
        }

        // Stream #0:1: Video: rv20 (RV20 / 0x30325652), yuv420p, 352x288, 117 kb/s, 15 fps, 15 tbr, 1k tbn, 1k tbc
        // 把括号对和括号里面的内容删掉
        $video_info2 = preg_replace('/\(.*?\)/', '', $video_info);
        if (preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $video_info2, $matches)) {
            //print_r($matches);//exit;
            $ret['vcodec'] = $matches[1];     // 编码格式
            $ret['vformat'] = $matches[2];    // 视频格式
            $ret['resolution'] = $matches[3]; // 分辨率
            list($width, $height) = explode('x', $matches[3]);
            $ret['width'] = $width;
            $ret['height'] = $height;
        }

        // Stream #0:0: Audio: cook (cook / 0x6B6F6F63), 22050 Hz, stereo, fltp, 32 kb/s
        if (preg_match("/Audio: (.*), (\d*) Hz/", $video_info2, $matches)) {
            //print_r($matches);
            $ret['acodec'] = $matches[1];      // 音频编码
            $ret['asamplerate'] = $matches[2]; // 音频采样频率
        }

        if (isset($ret['seconds']) && isset($ret['start'])) {
            $ret['play_time'] = $ret['seconds'] + $ret['start']; // 实际播放时间
        }

        //$ret['size'] = filesize($video_file); // 视频文件大小

        array_walk($ret, function (&$item) {
            $item = trim($item);
        });

        return $ret;
    }

    /**
     * 转码，将视频转码成mp4或者m3u8格式
     * @param $video_file string 视频源地址
     * @param $trans_file string 转码地址
     * @param string $type string 转码类型，支持mp4或者m3u8,如果不设置或者不支持，默认是m3u8
     * @param $watermark_id int 水印id
     */
    public function trans($video_file,$options)
    {
        $video_file = \Yii::getAlias('@webroot') . $video_file;

        // 转码，转码格式
        $trans_type = isset($options['type']) ? $options['type'] : 'mp4';

        if ($trans_type == 'mp4') {
            // 指定到一个目录即可，/upload/media/trans/2018/03/26/time().mp4
            $dir_relative = '/upload/trans/media/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' .  time() . rand(10000, 99999) . '/'; // 转码目录的相对路径
            $file_name = time() . rand(10000, 99999) . '.mp4';
        } else {
            // m3u8需要指定到一个单独的目录中 /upload/media/trans/2018/03/26/time()/stream.m3u8
            $dir_relative = '/upload/trans/media/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . time() . rand(10000, 99999) . '/'; // 转码目录的相对路径
            $file_name = 'stream.m3u8';
        }

        //定义文件转码路径
        $dir_absolute = \Yii::getAlias('@webroot' . $dir_relative);

        //获取视频信息
        $info = $this->getVideoInfo($video_file);

        //生成三种不同的码率
        if ($info['bitrate'] <= 3500) {
            //$bitRate = ['-b:v 1800k'];
            $bitRate = ['-s 720x480'];
        } elseif ($info['bitrate'] >= 3500 && $info['bitrate'] <= 8500) {
            $bitRate = ['-s 720x480', '-s 1280x720'];
        } elseif ($info['bitrate'] >= 8500) {
            $bitRate = ['-s 720x480', '-s 1280x720', '-s 1920x1080'];
        }

        $video_clear = ['sd', 'hd', 'fhd'];

        $trans_file = $item = [];
        foreach ($bitRate as $k => $v) {
            $item[$k]['dir_absolute'] = $dir_absolute . $video_clear[$k];
            $item[$k]['path'] = $dir_absolute . $video_clear[$k] .'/'. $file_name; // 文件的绝对路径
            $trans_file['trans_file'][$video_clear[$k]] = $dir_relative . $video_clear[$k] .'/'. $file_name; // 文件的相对路径
            $item[$k]['bitrate'] = $bitRate[$k];
        }

        foreach ($item as $k => $v) {
            if (yii\helpers\BaseFileHelper::createDirectory($v['dir_absolute'])) {
                //判断是否打水印
                $is_user_water = Yii::$app->systemConfig->getValue('IS_USER_WATER');
                if ($is_user_water) {
                    // 默认显示在右上角
                    // main_w	  视频单帧图像宽度
                    // main_h	  视频单帧图像高度
                    // overlay_w  水印图片的宽度
                    // overlay_h  水印图片的高度
                    // 左上角	10:10
                    // 右上角	main_w-overlay_w-10:10
                    // 左下角	10:main_h-overlay_h-10
                    // 右下角	main_w-overlay_w-10 : main_h-overlay_h-10
                    $position = isset($options['position']) ? $options['position'] : 'upperRight';
                    if ($position == 'upperLeft') {
                        // 左上角 upperLeft
                        $overlay = '10:10';
                    } else if ($position == 'lowerLeft') {
                        // 左下角 lowerLeft
                        $overlay = '10:main_h-overlay_h-10';
                    } else if ($position == 'lowerRight') {
                        // 右下角 lowerRight
                        $overlay = 'main_w-overlay_w-10:main_h-overlay_h-10';
                    } else {
                        // 默认右上角 upperRight
                        $overlay = 'main_w-overlay_w-10:10';
                    }

                    // 获取水印图片
                    //$watermark = MediaWatermark::findOne($watermark_id);
                    //Yii::$app->systemFileInfo->get($watermark['images'], 'src');

                    $pic = $options['video_water'];
                    $systemName = Tool::getSystemName();

                    // 判断文件是否存在
                    if (is_file(\Yii::getAlias('@webroot').$pic)) {
                        $water_pic = $systemName == 'windows' ? 'web' . $pic : \Yii::getAlias('@webroot' . $pic);
                    } else {
                        $water_pic = $systemName == 'windows' ? 'web/static/images/logo.png' : \Yii::getAlias('@webroot' . '/static/images/logo.png');
                    }

                    // 如果是windows，因为https://blog.csdn.net/anbinger2013/article/details/49079983的原因，所以需要把图片写成相对路径
                    //$water_pic = 'web/static/images/logo.png';

                    //$water = ' "movie='.$water_pic.',scale=334:61[watermark];[in][watermark] overlay='.$overlay.'[out]" ';
                    $water = '-i ' . $water_pic . ' -filter_complex [1:v]scale=125:25[ovrl],[0:v][ovrl]overlay=' . $overlay . ' ';

                    $command = $this->ffmpeg_cmd . " -i {$video_file}  {$water}   -c:v libx264 -vcodec libx264 -strict -2 -hls_list_size 0 -hls_time 2 {$v['path']}";
                } else {
                    if ($options['type'] == 'mp4') {
                        $command = $this->ffmpeg_cmd . " -i {$video_file} {$v['bitrate']}  -c:v libx264 -vcodec libx264 -strict -2  {$v['path']}";
                    } else {
                        // -hls_list_size 0 在m3u8文件中保留所有的ts切片文件；-hls_time 20 每个切片的时长20秒
                        //$command = $this->ffmpeg_cmd." -i {$video_file} -vf -hls_list_size 0 -hls_time 20  {$trans_file}";
                        $command = $this->ffmpeg_cmd . " -i {$video_file} {$v['bitrate']}  -c:v libx264 -vcodec libx264 -strict -2 -hls_list_size 0 -hls_time 2 {$v['path']}";
                    }
                }

                shell_exec($command);
            }
        }

        return $trans_file;
    }
}