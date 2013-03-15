<?php
class dResourceAutoIncrement extends dBase{
    protected $_pk = 'increment_id';
    protected $_tablename = 'resource_auto_increment';
    protected $_fields = array(
        'increment_id'
    );
    protected $_index_list = array(
        'increment_id'
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function createResourceId() {
       $this->execute("insert into $this->_tablename (increment_id)values(0)");
       $id = intval($this->getLastInsId());
       if ($id % 100 == 0) {
           $this->execute("delete from $this->_tablename");
       }
       return $id;
    }
}