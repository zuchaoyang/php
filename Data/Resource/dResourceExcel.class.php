<?php
class dResourceExcel extends dBase{
    protected $_tablename = 'resource_excel';
    protected $_pk = 'excel_id';
    protected $_fields = array(
        'excel_id',
        'excel_name',
        'origin_file_path',
        'resource_ids',
        'sucess_nums',
        'fail_nums',
        'fail_file_path',
        'state',
        'add_time'
    );
    protected $_index_list = array(
        'excel_id'
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getResourceExcelById($excel_ids) {
        return $this->getInfoByPk($excel_ids);
    }
    
    public function addResourceExcel($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
        
    public function modifyResourceExcel($dataarr, $excel_id) {
        return $this->modify($dataarr, $excel_id);
    }

    public function delResourceExcel($excel_id) {
        return $this->delete($excel_id);
    }
}