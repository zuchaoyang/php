<?php
class mResourceFileType extends mBase{
    protected $_dResrouceFileType = null;
    public function __construct() {
        $this->_dResrouceFileType = ClsFactory::Create('Data.Resource.dResourceFileType');
    }
    
    public function getAllResourceFileType() {
        return $this->_dResrouceFileType->getInfo();
    }
    
    public function getResurceFileTypeById($file_type_ids) {
        if(empty($file_type_ids)) {
            return false;
        }
        
        return $this->_dResrouceFileType->getResurceFileTypeById($file_type_ids);
    }
    
    public function addResourceFileType($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;    
        }
        
        return $this->_dResrouceFileType->addResourceFileType($dataarr);
    }
    
    public function modifyResourceFileType($dataarr, $file_type_id) {
        if(empty($dataarr) || !is_array($dataarr) || empty($file_type_id)) {
            return false;
        }
        
        return $this->_dResrouceFileType->modifyResourceFileType($dataarr, $file_type_id);
    }
    
    public function delResourceFileType($file_type_id) {
        if(empty($file_type_id)) {
            return false;
        }
        
        return $this->_dResrouceFileType->delResourceFileType($file_type_id);
    }
}