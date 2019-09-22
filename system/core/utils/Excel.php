<?php
/**
 * excel操作类
 */

namespace system\core\utils;

error_reporting(E_ALL ^ E_NOTICE);

class Excel
{
    private function __construct()
    {
    }

    /**
     * 读取excel文件内容
     * @param $file
     * @param string $type
     * @return array
     */
    public static function set_file($file, $type = 'Excel5')
    {
        $arr = [];
        if (file_exists($file)) {
            $array = explode('.', $file);
//            var_dump($array);die;
            if (in_array($array[count($array) - 1], ['xls'])) {
                $arr = self::excelToArray($file, $type);
            } else {
                header('Content-Type: text/html; charset=utf-8');
                echo "<script>alert('文件格式错误');window.location.replace('');</script>";
                exit;
            }
        }
        return $arr;
    }

    /**
     * 将表格文件读入数组
     * @param $file
     * @param $type
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    private static function excelToArray($file, $type)
    {
        $objReader = \PHPExcel_IOFactory::createReader($type);
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($file);
        $excelData = [];
        //文档数量
        $sheetCount = $objPHPExcel->getSheetCount();
        for ($i = 0; $i <= $sheetCount - 1; $i++) {
            $objWorksheet = $objPHPExcel->getSheet($i);
            $highestRow = $objWorksheet->getHighestRow();
            if ($highestRow > 0) {
                $highestColumn = $objWorksheet->getHighestColumn();
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
                for ($row = 1; $row <= $highestRow; $row++) {
                    for ($col = 0; $col < $highestColumnIndex; ++$col) {
                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                        $str = trim($cell->getValue());
                        //$str =preg_replace("/[[:space:]|\r|\n]+/","",$str);
                        //if(!empty($str)){
                        $excelData[$i][$row][$col] = $str;
                        //}
                    }
                }
            }
        }
        return $excelData;
    }

    /**
     * 表格写入到服务器文件
     * @param $doc
     * @param $file
     * @param $title
     * @param string $type
     * @return bool
     */
    public static function arrayToExcel($doc, $file, $title, $type = 'Excel5')
    {
        if (!empty($doc)) {
            $objWriter = self::writer_excel($doc, $title, $type);
            $objWriter->save($file);
            return true;
        } else {
            return false;
        }
    }

    /**
     * EXCEL写入操作
     * @param $doc
     * @param $title
     * @param string $type
     * @return \PHPExcel_Writer_IWriter
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    private static function writer_excel($doc, $title, $type = 'Excel5')
    {
        /*if($type=='2007'){
            require_once 'PHPExcel/Reader/Excel2007.php';
        }
        else{
            require_once 'PHPExcel/Reader/Excel5.php';
        }*/
        $objPHPExcel = new \PHPExcel();
        $count = count($doc[0]);
        $zm = [];
        for ($i = 'A'; $i <= 'Z'; $i++) {
            $zm[] = $i;
        }
        for ($j = 0; $j < $count; $j++) {
            $objPHPExcel->getActiveSheet()->getStyle($zm[$j])->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($j, 1)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($j, 1)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            if (is_array($doc[0][$j])) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($zm[$j])->setWidth(isset($doc[0][$j]['width']) ? $doc[0][$j]['width'] : 20);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, 1, $doc[0][$j]['label']);
            } else {
                $objPHPExcel->getActiveSheet()->getColumnDimension($zm[$j])->setWidth(20);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, 1, $doc[0][$j]);
            }

            for ($i = 2; $i < count($doc) + 1; $i++) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $i, " " . isset($doc[$i - 1][$j]) ? $doc[$i - 1][$j] : '');
            }

        }
        $objPHPExcel->createSheet();
        //$objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle($title);
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, $type);
        return $objWriter;

    }

    /**
     * 发送excel文件到文件头，数据格式为:
     * array('0'=>array('标题1','标题2'),//索引0为表格文件的标题
     *      '1'=>array('数据1','数据2'), //具体数据 以下皆是
     *      ……………………………………………………
     * );
     * @param $doc array 写入的数据
     * @param $file string 写入的数据
     * @param $title string 文件标题
     * @param string $type excel文件类型
     */
    public static function header_file($doc, $file, $title, $type = 'Excel5')
    {
        if (!empty($doc)) {
            $objWriter = self::writer_excel($doc, $title, $type);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            $ua = $_SERVER["HTTP_USER_AGENT"];
            $encoded_file = urlencode($file);
            $encoded_file = str_replace("+", "%20", $encoded_file);
            if (preg_match("/MSIE/", $ua)) {
                header('Content-Disposition: attachment; filename="' . $encoded_file . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . $file . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $file . '"');
            }

            header("Content-Transfer-Encoding: binary");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            $objWriter->save('php://output');
        }
    }
}