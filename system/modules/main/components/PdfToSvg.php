<?php

namespace system\modules\main\components;

use system\core\utils\Tool;
use yii\base\Component;
use yii;

class PdfToSvg extends Component
{
    private $pdf_svg_cmd = '';

    /**
     * 初始化
     *
     */
    public function init()
    {
        parent::init();

        $this->pdf_svg_cmd = Yii::$app->systemConfig->getValue('PDF_SVG_CMD', '');
    }

    /**
     * pdf转码svg
     * @param $string $input_file 待转码文件
     * @param $string $options 配置项
     */
    public function trans($input_fle, $options)
    {

        $input_fle = \Yii::getAlias('@webroot').$input_fle;

        //sleep(8);

         if(file_exists($input_fle)){
             echo 1;
         }else{
             echo $input_fle;
         };
        //配置文件的路径
        $dir_relative = '/upload/trans/svg/' . date('Y') . '/' . date('m') . '/' . date('d') . '/'; // 转码目录的相对路径
        $file_name = time() . rand(10000, 99999);

        $dir_absolute = \Yii::getAlias('@webroot' . $dir_relative); // 转换文件的绝对路径
        $trans_file = $dir_relative . $file_name; // 文件的相对路径
        $file_absolute = $dir_absolute . $file_name;   // 文件的绝对路径

        if (yii\helpers\BaseFileHelper::createDirectory($file_absolute)) {
            //将pdf转换成svg矢量图
            $page = isset($options['page']) ? $options['page'] : 'all';

            $path = $file_absolute . '/' . $file_name . '%d.svg ' . $page;

            //$input_fle = "D:\WWW\yd_mooc/web/upload/trans/pdf/2019/01/08/154693010619750/world.pdf";

            $svg_cmd = $this->pdf_svg_cmd . " {$input_fle}  {$path}";


            $res = system($svg_cmd . ' 2>&1', $return_var);

            if ($return_var === false) {
                return false;
            }

            if($page == 'all'){
                $result = [];
                for ($i = 1; $i < 1000; $i++) {
                    if (is_file($file_absolute . '/' . $file_name . $i . '.svg')) {
                        $result['trans_file'][$i] = $trans_file . '/' . $file_name . $i . '.svg';
                        $result['trans_type'] = 'svg';
                        $file = $file_absolute. '/' . $file_name . $i . '.svg';

                        //打水印
                        //$this->userWater($file , $options);
                    }
                }
            }else{
                $result['trans_file'][1] = $trans_file = $trans_file . '/' . $file_name . '%d.svg';
                $result['trans_type'] = 'svg';
                $file = $file_absolute . '/' . $file_name . '%d.svg';

                //$this->userWater($file , $options);
            }

            return $result;
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