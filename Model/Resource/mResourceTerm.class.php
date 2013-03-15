<?php
class mResourceTerm extends mBase{
    protected $_dResourceTerm = null;
    public function __construct() {
        $this->_dResourceTerm = ClsFactory::Create("Data.Resource.dResourceTerm");
    }
    
    public function getAllResourceTerm() {
        return $this->_dResourceTerm->getInfo();
    }
    
    public function getResourceTermById($term_ids) {
        if(empty($term_ids)) {
            return false;
        }
        
        return $this->_dResourceTerm->getResourceTermById($term_ids);
    }
    
    public function addResourceTerm($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceTerm->addResourceTerm($dataarr, $is_return_id);
    }
    
    public function modifyResourceTerm($dataarr, $term_id) {
        if(empty($dataarr) || !is_array($dataarr) || empty($term_id)) {
            return false;
        }
        
        return $this->_dResourceTerm->modifyResourceTerm($dataarr, $term_id);
    }
    
    public function delResourceTerm($term_id) {
        if(empty($term_id)) {
            return false;
        }
        
        return $this->_dResourceTerm->delResourceTerm($term_id);
    }
}