<?php
define('WMW_PHPEXCEL_DIR', dirname(__FILE__));
include_once WMW_PHPEXCEL_DIR . "/Vendor/PHPExcel/PHPExcelInterface.php";
include_once WMW_PHPEXCEL_DIR . "/Vendor/WmwAutoLoader.class.php";

//PHPExcel操作类
class WmwPHPExcel implements PHPexcelInterface {
    private $objPHPExcel = null;
    
    public function __construct() {
        $this->objPHPExcel = new HandlePHPExcel();
    }

	/**
     * 检测Excel文件是否可读
     */
    public function canRead($pFileName) {
        return $this->objPHPExcel->canRead($pFileName);
    }
    
    /**
     * 获取excel文件的工作区间的个数
     * @param unknown_type $pFileName
     */
    public function getSheetCount($pFileName) {
        return $this->objPHPExcel->getSheetCount($pFileName);
    }
    
    /**
     * 以工作区间索引的方式获取excel数据
     * @param  $pFileName
     * @param  $index
     */
    public function getSheetDatasByIndex($pFileName, $index = 0) {
        return $this->objPHPExcel->getSheetDatasByIndex($pFileName, $index);
    }
    
    /**
     * 将Excel中数据转换成数组
     */
    public function toArray($pFileName) {
        return $this->objPHPExcel->toArray($pFileName);
    }
    
    /**
     * 导出Excel文件内容
     * @param $pFileName			Excel文件名包括完整的文件路径
     * @param $export_file_name		导出时的文件命名,默认为Excel的文件名
     */
    public function export($pFileName, $export_file_name = null) {
        return $this->objPHPExcel->export($pFileName, $export_file_name);
    }
    
    /**
     * 将Excel相关的数据直接输出到浏览器
     * @param $datas
     * @param $export_file_name
     */
    public function outputExcel($datas, $export_file_name) {
        return $this->objPHPExcel->outputExcel($datas, $export_file_name);
    }
    
    /**
     * 将相应的数据保存到Excel文件中
     * @param $dataarr
     * @param $filename
     */
    public function saveToExcelFile($datas, $filename) {
        return $this->objPHPExcel->saveToExcelFile($datas, $filename);
    }
}
