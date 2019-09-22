<?php

namespace system\modules\main\components;

use system\core\utils\Tool;
use yii\base\Component;
use yii;

class OfficeToPdf extends Component
{
    private $office_pdf_cmd = '';

    public function init()
    {
        parent::init();
        // 定义转码的路径
        $this->office_pdf_cmd = Yii::$app->systemConfig->getValue('OFFICE_PDF_CMD','');
    }

    /**
     * office转码pdf
     * @param $string $input_file 待转码文件
     * @param $string $options 配置项
     */
    public function trans($input_fle, $options)
    {
        $input_fle = \Yii::getAlias('@webroot').$input_fle;

        if (!file_exists($input_fle)) {
            return false;
        }

        $dir_relative = '/upload/trans/pdf/' . date('Y') . '/' . date('m') . '/' . date('d') . '/'; // 转码目录的相对路径
        $file_name = time() . rand(10000, 99999);

        $dir_absolute = \Yii::getAlias('@webroot' . $dir_relative); // 转换文件的绝对路径
        $trans_file = $dir_relative . $file_name; // 文件的相对路径
        $file_absolute = $dir_absolute . $file_name;   // 文件的绝对路径

        if (yii\helpers\BaseFileHelper::createDirectory($file_absolute)) {
            //将文档转成pdf格式

            $pdf_cmd = $this->office_pdf_cmd . " --headless --convert-to pdf {$input_fle} --outdir {$file_absolute}";

            //shell_exec($pdf_cmd);
            $retval = system($pdf_cmd . ' 2>&1', $return_var);
            if ($return_var === false) {
                return false;
            }

            $filename = basename($input_fle);

            $filename = str_replace(strrchr($filename, '.'), '', $filename);

            return [1 => $trans_file.'/'.$filename.'.pdf'];
        } else {

            return false;
        }
    }
}