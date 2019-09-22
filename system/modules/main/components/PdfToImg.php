<?php

namespace system\modules\main\components;

use system\core\utils\Tool;
use yii\base\Component;
use yii;

class PdfToImg extends Component
{
    private $pdf_img_cmd = '';
    private $is_play_pdf = '';

    /**
     * 初始化
     *
     */
    public function init()
    {
        parent::init();

        $this->pdf_img_cmd = Yii::$app->systemConfig->getValue('PDF_IMG_CMD', '');
        $this->is_play_pdf = Yii::$app->systemConfig->getValue('IS_PLAY_PDF','');
    }

    /**
     * pdf转码svg
     * @param $string $input_file 待转码文件
     * @param $string $options 配置项
     */
    public function trans($input_file, $options)
    {

        $input_relative_file = \Yii::getAlias('@webroot').$input_file;


        //sleep(8);

        //配置文件的路径
        $dir_relative = '/upload/trans/img/' . date('Y') . '/' . date('m') . '/' . date('d') . '/'; // 转码目录的相对路径
        $file_name = time() . rand(10000, 99999);

        $dir_absolute = \Yii::getAlias('@webroot' . $dir_relative); // 转换文件的绝对路径
        $trans_file = $dir_relative . $file_name; // 文件的相对路径
        $file_absolute = $dir_absolute . $file_name;   // 文件的绝对路径

        if (yii\helpers\BaseFileHelper::createDirectory($file_absolute)) {
            $result = [];

            //是否播放pdf
            if($this->is_play_pdf == 1){
                $result['trans_file'] = [1 => $input_file];
                $result['trans_type'] = 'pdf';
            }else{
                //将pdf转换成图片
                $path = $file_absolute . '/' . $file_name . '%d.png';

                $img_cmd = $this->pdf_img_cmd . ' -trim -bordercolor white -border 25x25 '. " {$input_relative_file}  {$path}";
                echo $img_cmd.PHP_EOL;
                //shell_exec($img_cmd);
                system($img_cmd . ' 2>&1', $return_var);

                if ($return_var === false) {
                    return false;
                }

                for ($i = 0; $i < 1000; $i++) {
                    $filePath = $file_absolute . '/' . $file_name . $i . '.png';
                    if (is_file($filePath)) {
                        // 给png文件加上白色背景
                        $img_cmd = $this->pdf_img_cmd . ' -flatten '. " {$filePath}  {$filePath}";
                        //shell_exec($img_cmd);
                        system($img_cmd . ' 2>&1', $return_var);

                        $result['trans_file'][$i+1] = $trans_file . '/' . $file_name . $i . '.png';
                        $result['trans_type'] = 'image';

                        //$file = $file_absolute. '/' . $file_name . $i . '.svg';

                        //打水印
                        //$this->userWater($file , $options);
                    } else {
                        break;
                    }
                }
            }

            return $result;
        } else {
            return false;
        }
    }

    /*
     * 打水印
     *
     * */
    public function userWater($file,$options)
    {
        $is_user_water = Yii::$app->systemConfig->getValue('IS_USER_WATER');
        $water_type = Yii::$app->systemConfig->getValue('WATER_TYPE');

        $x = isset($options['x']) ? $options['x'] : 23.5;//横坐标
        $y = isset($options['y']) ? $options['y'] : 50;//纵坐标
        $width = isset($options['width']) ? $options['width'] : 50;//宽度
        $height = isset($options['height']) ? $options['height'] : 60;//高度

        $svg = file_get_contents($file);

        //是否使用水印
        if($is_user_water){
            if($water_type == 0){
                //文本水印
                $svg = str_replace("</svg>",'<text x="'.$x.'" y="'.$y.'" transform="scale(2)" fill="rgba(0,0,0,.2)">'.$options['water_content'].'</text></svg>',$svg);
                file_put_contents($file,$svg);
            }elseif($water_type == 1){
                //图片水印
                $base64_img = Tool::base64EncodeImage($options['water_content']);
                $file_img = '<image x="'.$x.'" y="'.$y.'" width="'.$width.'" height="'.$height.'" xlink:href="' . $base64_img . '" />';
                $svg = str_replace("</svg>",$file_img.'</svg>',$svg);
                file_put_contents($file,$svg);
            }

        }
    }
}