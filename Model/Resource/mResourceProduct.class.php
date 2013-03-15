<?php
class mResourceProduct extends mBase{
    protected $_dResourceProduct = null;
    
    public function __construct() {
        $this->_dResourceProduct = ClsFactory::Create("Data.Resource.dResourceProduct");
    }
    
    public function getResourceProductById($product_ids) {
        if(empty($product_ids)) {
            return false;
        }
        
        return $this->_dResourceProduct->getResourceProductById($product_ids);
    }
} 