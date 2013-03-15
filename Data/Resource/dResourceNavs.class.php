<?php
class dResourceNavs extends dBase {
    protected $_pk = 'id';
    protected $_tablename = 'resource_navs';
    protected $_fields = array(
        'id',
        'nav_value',
        'add_time'
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    protected $_index_list = array(
    	'nav_value'  
    );
}