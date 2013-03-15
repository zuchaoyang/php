<?php
interface PHPExcelInterface {
	/**
     * 检测Excel文件是否可读
     */
    public function canRead($pFileName);
    /**
     * 获取excel文件的工作区间的个数
     * @param unknown_type $pFileName
     */
    public function getSheetCount($pFileName);
    /**
     * 以工作区间索引的方式获取excel数据
     * @param  $pFileName
     * @param  $index
     */
    public function getSheetDatasByIndex($pFileName, $index = 0);
    /**
     * 将Excel中数据转换成数组
     */
    public function toArray($pFileName);
  	/**
     * 导出Excel文件内容
     */
    public function export($pFileName, $export_file_name = null);
    /**
     * 将相应的数据保存到Excel文件中
     * @param $dataarr
     * @param $filename
     */
    public function saveToExcelFile($datas, $filename);
}