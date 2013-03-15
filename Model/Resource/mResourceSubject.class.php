<?php
class mResourceSubject extends mBase{
    protected $_dResourceSubject = null;
    public function __construct() {
        $this->_dResourceSubject = ClsFactory::Create("Data.Resource.dResourceSubject");
    }
    
    public function getAllResourceSubject() {
        return $this->_dResourceSubject->getInfo();
    }
    
    public function getResourceSubjectById($subject_id) {
        if(empty($subject_id)) {
            return false;
        }
        
        return $this->_dResourceSubject->getResourceSubjectById($subject_id);
    }
    
    public function addResourceSubject($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceSubject->addResourceSubject($dataarr, $is_return_id);
    }
    
    public function modifyResourceSubject($dataarr, $subject_id) {
        if(empty($subject_id) || empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceSubject->modifyResourceSubject($dataarr, $subject_id);
    }
    
    public function delResourceSubject($subject_id) {
        if(empty($subject_id)) {
            return false;
        }
        
        return $this->_dResourceSubject->delResourceSubject($subject_id);
    }
}