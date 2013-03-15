<?php
class dResourceProduct extends dBase{
    protected $_pk = 'product_id';
    protected $_tablename = 'resource_product';
    protected $_fields = array(
        'product_id',
        'product_name',
        'add_time'
    );
    protected $_index_list = array(
        'product_id'
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getResourceProductById($product_ids) {
        return $this->getInfoByPk($product_ids);
    }
}