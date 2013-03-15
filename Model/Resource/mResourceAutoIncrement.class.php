<?php
class mResourceAutoIncrement extends mBase{
    protected $_dResourceAutoIncrement = null;
    public function __construct() {
        $this->_dResourceAutoIncrement = ClsFactory::Create("Data.Resource.dResourceAutoIncrement");
    }
    
    public function createResourceId() {
       return $this->_dResourceAutoIncrement->createResourceId();
    }
}