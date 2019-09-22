<?php

namespace system\modules\main\migrations;

use console\models\Migration;

class M181229021357Add_Trans_config extends Migration
{
    // 所属模块
    public $module_id = 'main';

    // 更新说明
    public $description = '添加转码配置';

    public $config = [
        //配置
        'systemConfig' => [
            // 全局
            [
                'name' => 'WATER_TYPE',
                'title' => '水印类型',
                'type' => 'number',
                'value' => '0',
                'remark' => '0：文本类型，1：图片类型',
            ],
            [
                'name' => 'TRANS_VALUES',
                'title' => '转码文件属性值',
                'type' => 'array',
                'value' => "x=23\r\ny=30\r\npage=all\r\ntype=mp4\r\npage=all\r\nlocation=lowerLeft\r\nwidth=25\r\nheight=35\r\nwater_content=武汉城市职业学院\r\nvideo_water=视频水印",
                'remark' => 'x： svg水印横坐标， y：svg水印纵坐标， page：pdf页数(默认：all)，  type：视频转码类型， location：视频水印位置，water_content：水印内容，video_water：视频水印',
            ],
            [
                'name' => 'MEDIA_FFMPEG_CMD',
                'title' => 'ffmpeg命令',
                'type' => 'string',
                'value' => '/usr/bin/ffmpeg',
                'remark' => 'ffmpeg命令',
            ],
            [
                'name' => 'PDF_SVG_CMD',
                'title' => 'svg命令',
                'value' => '/usr/local/bin/pdf2svg',
                'remark' => 'pdf2svg命令',
            ],
            [
                'name' => 'OFFICE_PDF_CMD',
                'title' => 'libreoffice命令',
                'type' => 'string',
                'value' => '/usr/bin/libreoffice',
                'remark' => 'libreoffice命令',
            ],
            [
                'name' => 'TRANS_EXTENSIONS',
                'title' => '转码类',
                'type' => 'array',
                'value' => "docx,xlsx,pptx=system\modules\main\components\OfficeToPdf\r\npdf=system\modules\main\components\PdfToSvg\r\nmp4,m3u8=system\modules\main\components\Ffmpeg",
                'remark' => '前面转码文件类型，后面是相对应的转码类',
            ],
            [
                'name' => 'IS_USER_WATER',
                'title' => '是否使用水印',
                'type' => 'number',
                'value' => '0',
                'remark' => '是否使用水印(0：否，1：是)',
            ],
        ],
    ];

    // 版本号
    public $version = '1.0';

    public function up()
    {
        parent::up();
    }

    public function down()
    {
        parent::down();
    }
}
