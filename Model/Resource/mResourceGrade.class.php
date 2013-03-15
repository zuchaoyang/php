<?php
class mResourceGrade extends mBase{
    protected $_dResourceGrade = null;
    
    public function __construct() {
        $this->_dResourceGrade = ClsFactory::Create("Data.Resource.dResourceGrade");
    }
    
    public function getAllResourceGrade() {
        return $this->_dResourceGrade->getInfo();
    }
    
    public function getResourceGradeById($grade_ids) {
        if(empty($grade_ids)) {
            return false;
        }
        
        return $this->_dResourceGrade->getResourceGradeById($grade_ids);
    }
    
    public function addResourceGrade($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceGrade->addResourceGrade($dataarr, $is_return_id);
    }
    
    public function modifyResourceGrade($dataarr, $grade_id) {
        if(empty($dataarr) || !is_array($dataarr) || empty($grade_id)) {
            return false;
        }
        
        return $this->_dResourceGrade->modifyResourceGrade($dataarr, $grade_id);
    }
    
    public function delResourceGrade($grade_id) {
        if(empty($grade_id)) {
            return false;
        }
        
        return $this->_dResourceGrade->delResourceGrade($grade_id);
    }
}