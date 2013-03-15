<?php
class dResourceColumn extends dBase{
    protected $_pk = 'column_id';
    protected $_tablename = 'resource_column';
    protected $_fields = array(
        'column_id',
        'column_name',
        'product_id',
        'upd_time',
        'add_time',
    );
    protected $_index_list = array(
        'column_id',
    	'product_id',
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getResourceColumnByProductId($product_id) {
        return $this->getInfoByFk($product_id, 'product_id');
    }
    
    public function addResourceColumn($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function modifyResourceColumn($dataarr, $column_id) {
        return $this->modify($dataarr, $column_id);
    }
    
    public function delResourceColumn($column_id) {
        return $this->delete($column_id);
    }
}