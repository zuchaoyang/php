<?php
class mPasswordAppeal extends mBase{
    protected $_dPasswordAppeal = null;
    
    public function __construct() {
        $this->_dPasswordAppeal = ClsFactory::Create("Data.dPasswordAppeal");
    }
    
    public function getPasswordAppealAll() {   
        return $this->getInfo('','order by add_time desc');
    }
    
    public function getPasswordAppealById($appeal_ids) {
        if(empty($appeal_ids)) {
            return false;
        }
        
        return $this->_dPasswordAppeal->getPasswordAppealById($appeal_ids);
    }
    
    public function addPasswordAppeal($dataarr, $is_return_id) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dPasswordAppeal->addPasswordAppeal($dataarr, $is_return_id);
    }
    
    public function delPasswordAppeal($appeal_id) {
        if(empty($appeal_id)) {
            return false;
        }
        
        return $this->_dPasswordAppeal->delPasswordAppeal($appeal_id);
    }
}