<?php
class dResourceFileType extends dBase{
    protected $_pk = 'file_type_id';
    protected $_tablename = 'resource_file_type';
    protected $_fields = array(
        'file_type_id',
        'file_type_name',
        'upd_time',
        'add_time',
    );
    protected $_index_list = array(
        'file_type_id'
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getResurceFileTypeById($file_type_ids) {
        return $this->getInfoByPk($file_type_ids);
    }
    
    public function addResourceFileType($dataarr, $is_return_id) {
        return $this->add($dataarr);
    }
    
    public function modifyResourceFileType($dataarr, $file_type_id) {
        return $this->modify($dataarr, $file_type_id);
    }
    
    public function delResourceFileType($file_type_id) {
        return $this->delete($file_type_id);
    }
}