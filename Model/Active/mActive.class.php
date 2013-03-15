<?php
class mActive extends mBase {
    
    protected $_dActive = null;
    public function __construct() {
        $this->_dActive = ClsFactory::Create("Data.Active.dActive");
    }
    public function getActiveById($active_id){
        if(empty($active_id)) {
            return false;
        }
        
        return $this->_dActive->getActiveById($active_id);
    }
    
    public function getActiveByClientAccount($client_account,$orderby = null,$offset=null ,$limit=null){
        if(empty($client_account)) {
            return false;
        }
        
        return $this->_dActive->getActiveByClientAccount($client_account,$orderby,$offset ,$limit);
    }
    
    public function addActiveBat($dataarr){
        if(empty($dataarr)) {
            return false;
        }
        
        return $this->_dActive->addBat($dataarr);
    }
    
    public function addActive($dataarr, $is_return_id = false){
        if(empty($dataarr)) {
            return false;
        }
        
        return $this->_dActive->addActive($dataarr, $is_return_id);
    }
    
    public function modifyActive($dataarr, $active_id){
        if(empty($dataarr) || empty($active_id)) {
            return false;
        }
        
        return $this->_dActive->modifyActive($dataarr, $active_id);
    }
    
    public function delActive($active_id){
        if(empty($active_id)) {
            return false;
        }
        
        return $this->_dActive->delActive($active_id);
    }
}