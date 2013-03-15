<?php
class mResourceColumn extends mBase{
    protected $_dResourceColumn = null;
    public function __construct() {
        $this->_dResourceColumn = ClsFactory::create("Data.Resource.dResourceColumn");
    }
    
    public function getAllResourceColumn() {
        return $this->_dResourceColumn->getInfo();
    }
    
    public function getResourceColumnByProductId($product_id) {
        if(empty($product_id)) {
            return false;
        }
        
        return $this->_dResourceColumn->getResourceColumnByProductId($product_id, 'product_id');
    }
    
    public function addResourceColumn($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) { 
            return false;
        }
        
        return $this->_dResourceColumn->addResourceColumn($dataarr, $is_return_id);
    }
    
    public function modifyResourceColumn($dataarr, $column_id) {
        if(empty($column_id) || empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceColumn->modifyResourceColumn($dataarr, $column_id);
    }
    
    public function delResourceColumn($column_id) {
        if(empty($column_id)) {
            return false;
        }
        
        return $this->_dResourceColumn->delResourceColumn($column_id);
    }
}