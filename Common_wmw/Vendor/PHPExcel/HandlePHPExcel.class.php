<?php
if(!defined('VENDOR_PHPEXCEL_DIR')) {
    define('VENDOR_PHPEXCEL_DIR', dirname(__FILE__));
}

include_once VENDOR_PHPEXCEL_DIR . "/PHPExcelInterface.php";
include_once VENDOR_PHPEXCEL_DIR . "/../WmwAutoLoader.class.php";

//PHPExcel操作类
class HandlePHPExcel implements PHPExcelInterface {
    //允许处理的文件名后缀和对应的content-type值
    protected $allow_mine_types = array(
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/octet-stream'
    );
    protected $memory_limit = 512;
    protected $max_execution_time = 60;
    
    /**
     * 文件的基本属性信息，包括文件名和文件路径
     * @param $file_attr
     */
    public function __construct($init_arr = array()) {
        if(isset($init_arr['memory_limit'])) {
            $this->memory_limit = intval($init_arr['memory_limit']);
        }
        if(isset($init_arr['max_execution_time'])) {
            $this->max_execution_time = intval($init_arr['max_execution_time']);
        }
        $this->_init_env();
    }
    
	/**
     * 初始化相应的环境
     */
    protected function _init_env() {
        //注册系统默认的自动处理函数
        spl_autoload_register("__autoload");
        include_once VENDOR_PHPEXCEL_DIR . "/PHPExcel.php";
        //设置内存大小
        $memory_limit = intval(ini_get('memory_limit'));
        if($memory_limit < $this->memory_limit) {
            ini_set('memory_limit', $this->memory_limit.'M');
        }
        //设置页面的最大执行时间
        $max_execution_time = ini_get('max_execution_time');
        if($max_execution_time < $this->max_execution_time) {
            ini_set('max_execution_time', $this->max_execution_time);
        }
    }
    
	/**
     * 检测Excel文件是否可读
     */
    public function canRead($pFileName) {
        if(empty($pFileName)) {
            return false;
        }
        
        $canRead = false;
        try {
            $PHPExcel_Reader = PHPExcel_IOFactory::createReaderForFile($pFileName);
            $canRead = $PHPExcel_Reader->canRead($pFileName);
        } catch(Exception $e) {
            $canRead = false;
        }
        
        return $canRead;
    }
    
    /**
     * 获取excel文件的工作区间的个数
     * @param unknown_type $pFileName
     */
    public function getSheetCount($pFileName) {
        if(empty($pFileName)) {
            return false;
        }
        
        $canRead = $this->canRead($pFileName);
        if(!$canRead) {
            return false;
        }
        $PHPExcel_Reader = PHPExcel_IOFactory::createReaderForFile($pFileName);
        //按照矩阵的方式处理Excel文件,并将数据转换成相应的php数组
        $PHPExcel = $PHPExcel_Reader->load($pFileName);
        $SheetCount = $PHPExcel->getSheetCount();
        return max($SheetCount, 0);
    }
    
    /**
     * 以工作区间索引的方式获取excel数据
     * @param  $pFileName
     * @param  $index
     */
    public function getSheetDatasByIndex($pFileName, $index = 0) {
        if(empty($pFileName) || !$this->canRead($pFileName)) {
            return false;
        }
        
        $index = max($index, 0);
        $PHPExcel_Reader = PHPExcel_IOFactory::createReaderForFile($pFileName);
        //按照矩阵的方式处理Excel文件,并将数据转换成相应的php数组
        $PHPExcel = $PHPExcel_Reader->load($pFileName);
        $currentSheet = $PHPExcel->getSheet($index);
        
        return $this->parseSheet($currentSheet);
    }
    
    /**
     * 将Excel中数据转换成数组
     */
    public function toArray($pFileName) {
        if(empty($pFileName) || !$this->canRead($pFileName)) {
            return false;
        }
        
        $PHPExcel_Reader = PHPExcel_IOFactory::createReaderForFile($pFileName);
        //按照矩阵的方式处理Excel文件,并将数据转换成相应的php数组
        $PHPExcel = $PHPExcel_Reader->load($pFileName);
        $SheetCount = $PHPExcel->getSheetCount();
        
        $excel_datas = array();
        for($index = 0; $index < $SheetCount; $index++) {
            $currentSheet = $PHPExcel->getSheet($index);
            $excel_datas[$index] = $this->parseSheet($currentSheet);
        }
        
        return !empty($excel_datas) ? $excel_datas : false;
    }
    
  	/**
     * 导出Excel文件内容
     */
    public function export($pFileName, $export_file_name = null) {
        if(empty($pFileName) || !$this->canRead($pFileName)) {
            return false;
        }
        
        //excel文件下载
        $objDownload = new Download();
        $objDownload->downfile($pFileName, $export_file_name);
    }
    
    /**
     * 直接输出Excel文件到浏览器
     * @param $datas
     * @param $filename
     */
    public function outputExcel($datas, $export_file_name) {
        if(empty($datas)) {
            return false;
        }
        
        if(empty($export_file_name)) {
            $export_file_name = "Excel文件.xls";
        }
        
        //以"."作为分割标志
        if(($pos = strrpos($export_file_name, '.')) !== false) {
            $file_name = substr($export_file_name, 0, $pos);
            $suffix = substr($export_file_name, $pos + 1);
        } else {
            $file_name = $export_file_name;
            $suffix = 'xls';
        }
        
        $suffix = strtolower($suffix);
        $suffix = in_array($suffix, array_keys($this->allow_mine_types)) ? $suffix : 'xls';
        
        //处理下载文件名的乱码问题
        $downname = $file_name . '.' . $suffix;
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if(stripos($user_agent, 'MSIE') !== false) {
            $downname = 'filename="' . str_replace('+', '%20', urlencode($downname)) . '"';
        } elseif(stripos($user_agent, 'Firefox') !== false) {
            $downname = 'filename*="utf8\'\'' . $downname . '"';
        } else {
            $downname = 'filename="' . $downname . '"';
        }
        
        //输出文件内容信息
        ob_end_clean();
        header('Cache-control: max-age=31536000'); 
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT'); 
		header('Content-Type: ' . $this->allow_mine_types[$suffix]);
		header('Content-Disposition:attachment;' . $downname);
		
		$PHPExcel = $this->createPHPExcelHandle($datas);
		if($PHPExcel instanceof PHPExcel) {
            $PHPExcel_Writer = PHPExcel_IOFactory::createWriter($PHPExcel, $suffix == 'xls' ? 'Excel5' : 'Excel2007');
            $PHPExcel_Writer->save("php://output");
		}
    }
    
    /**
     * 将相应的数据保存到Excel文件中
     * @param $dataarr
     * @param $filename
     */
    public function saveToExcelFile($datas, $filename) {
        if(empty($datas)) {
            return false;
        }
       
        $PHPExcel = $this->createPHPExcelHandle($datas);
        if($PHPExcel instanceof PHPExcel) {
            list($suffix, $excel_version) = $this->getExcelSuffixAndVersion($filename);
            $PHPExcel_Writer = PHPExcel_IOFactory::createWriter($PHPExcel, $excel_version);
            $PHPExcel_Writer->save($filename);
        }
        
        return file_exists($filename) ? true : false;
    }
    
    /**
     * 通过数据创建一个Excel的操作句柄
     */
    private function createPHPExcelHandle($datas) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        $PHPExcel = new PHPExcel();
        $PHPExcel->removeSheetByIndex(0);
        $index = 0;
        foreach($datas as $sheet) {
            $cols = $sheet['cols'];
            $rows = $sheet['rows'];
            $title = $sheet['title'];

            $PHPExcel->createSheet($index);
            $PHPExcel->setActiveSheetIndex($index);
            $objActiveSheet = $PHPExcel->getActiveSheet();
            $objActiveSheet->setTitle($title);
            
            //设置对应的数据显示的格式
            for($k = 0; $k < $cols; $k++) {
                $colname = chr($k + 65);
                $objActiveSheet->getStyle($colname)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
                $objActiveSheet->getColumnDimension($colname)->setWidth(20);
            }
            
            //将对应的数字类型的数据转换成相应的字符串，注意为了防止被科学化表示，字符串前面加空格
            for($i = 1; $i <= $rows; $i++) {
                for($j = 0; $j < $cols; $j++) {
                    $val = " " . strval($sheet['datas'][$i][$j]);
                    $objActiveSheet->setCellValueByColumnAndRow($j, $i, $val);
                }
            }
            
            $index++;
        }
        
        return $PHPExcel;
    }
    
    /**
     * 解析工作区
     * @param $sheet
     */
    private function parseSheet($sheet) {
        if(empty($sheet) || !($sheet instanceof PHPExcel_Worksheet)) {
            return false;            
        }
        
        $allColumn = PHPExcel_cell::columnIndexFromString($sheet->getHighestColumn());
        $allRow = $sheet->getHighestRow();
        
        $sheet_datas = array(
            'title' => $sheet->getTitle(),
            'cols'  => $allColumn,
            'rows'	=> $allRow,
            'datas' => array(),
        );
        
        for($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
            $row = array();
            for($currentColumn=0; $currentColumn < $allColumn; $currentColumn++) {
                $cell = $sheet->getCellByColumnAndRow($currentColumn, $currentRow);
                $row[$currentColumn] = ($cell->getValue() instanceof PHPExcel_RichText) ? $cell->getValue()->getPlainText() : $cell->getValue();
            }
            $sheet_datas["datas"][$currentRow] = $row;
        }
        
        return $sheet_datas;
    }
    
	/**
     * 解析和初始化文件的相关信息
     */
    private function getFileAttribute($pFileName) {
        if(empty($pFileName)) {
            return false;
        }
        
        $pFileName = trim($pFileName);
        $file_name = end(explode('/', $pFileName));
        $file_path = dirname($pFileName);
        
        //获取excel文件的后缀名和版本信息
        list($suffix, $excel_version) = $this->getExcelSuffixAndVersion($file_name);
        
        //文件的基本属性信息
        $file_attr = array(
            'file_name' => $file_name,
            'file_path' => $file_path,
            'real_file' => $pFileName,
            'suffix' => $suffix,
            'version' => $excel_version,
        );
        
        return $file_attr;
    }
    
    /**
     * 获取文件的后缀名和excel文件的版本信息
     * @param $file_name excel的文件名
     */
    private function getExcelSuffixAndVersion($file_name) {
        if(empty($file_name)) {
            return false;
        }
        
        $suffix = $excel_version = "";
        if(stripos($file_name, '.') !== false) {
            $suffix = strtolower(end(explode('.', $file_name)));
        }
        
        //检测文件后缀名是否正确
        $allow_types = (array)array_keys($this->allow_mine_types);
        if(!empty($allow_types) && !in_array($suffix, $allow_types)) {
            throw new Exception("文件后缀名错误!", -1);
        }
        
        if($suffix == 'xls') {
            $excel_version = 'Excel5';
        } elseif($suffix == 'xlsx') {
            $excel_version = 'Excel2007';
        }
        
        return array($suffix, $excel_version);
    }
}
?>