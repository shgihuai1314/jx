<?php

namespace system\modules\main\components;

use yii\base\Component;
use yii;

class OfficeToImg extends Component
{
    private $office_pdf_cmd = '';

    /**
     * 初始化
     *
     */
    public function init()
    {
        parent::init();
        // 定义转码的路径
        $this->office_pdf_cmd = Yii::$app->systemConfig->getValue('OFFICE_PDF_CMD','');
    }

    /**
     * office转码svg
     * @param $string $input_file 待转码文件
     * @param $string $options 配置项
     */
    public function trans($input_fle, $options)
    {
        //调用文件转码pdf类
        $pdf_object = Yii::createObject([
            'class' => 'system\modules\main\components\OfficeToPdf',
        ]);

        $pdf_file = $pdf_object->trans($input_fle, $options);

        if (!$pdf_file) {
            return false;
        } else {
            $pdf_file = reset($pdf_file);
        }

        //调用pdf转码svg类
        $pdf_object = Yii::createObject([
            'class' => 'system\modules\main\components\PdfToImg',
        ]);

        $svg_pdf = $pdf_object->trans($pdf_file, $options);

        return $svg_pdf;
    }
}