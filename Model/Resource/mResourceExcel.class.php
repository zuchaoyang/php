<?php
class mResourceExcel extends mBase{
    protected $_dResourceExcel = null;
    
    public function __construct() {
        $this->_dResourceExcel = ClsFactory::Create("Data.Resource.dResourceExcel");
    }
    
    public function getResourceExcelById($excel_ids) {
        if(empty($excel_ids)) {
            return false;
        }
        
        return $this->_dResourceExcel->getResourceExcelById($excel_ids);
    }
    
    public function addResourceExcel($dataarr, $is_return_id=false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceExcel->addResourceExcel($dataarr, $is_return_id);
    }
        
    public function modifyResourceExcel($dataarr, $excel_id) {
        if(empty($excel_id) || empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceExcel->modifyResourceExcel($dataarr, $excel_id);
    }

    public function delResourceExcel($excel_id) {
        if(empty($excel_id)) {
            return false;
        }
        
        return $this->_dResourceExcel->delResourceExcel($excel_id);
    }
}