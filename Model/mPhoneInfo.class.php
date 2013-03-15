<?php
class mPhoneInfo extends mBase {
    
    protected $_dPhoneInfo = null;
    
    public function __construct() {
        $this->_dPhoneInfo = ClsFactory::Create("Data.dPhoneInfo");
    }
    
    public function addPhoneInfo($dataarr, $is_return_id = false) {
	    if(empty($dataarr) || !is_array($dataarr)) {
	        return false;
	    }
	    
	    return $this->_dPhoneInfo->addPhoneInfo($dataarr, $is_return_id);
	}
	
	public function addPhoneInfoBat($dataarr){
	    if(empty($dataarr) || !is_array($dataarr)) {
	        return false;
	    }
	    
	    return $this->_dPhoneInfo->addBat($dataarr);
	}
	
	public function getPhoneInfoById($phone_ids) {
	    if(empty($phone_ids)) {
	        return false;
	    }
	    
	    return $this->_dPhoneInfo->getPhoneInfoById($phone_ids);
	}
	
	public function modifyPhoneInfo($dataarr, $phone_id) {
	    if(empty($phone_id) || empty($dataarr) || !is_array($dataarr)) {
	        return false;
	    }
	    
	    return $this->_dPhoneInfo->modifyPhoneInfo($dataarr, $phone_id);
	}
	
	public function delPhoneInfo($phone_id) {
	    if(empty($phone_id)) {
	        return false;
	    }
	    
	    return $this->_dPhoneInfo->delPhoneInfo($phone_id);
	}
	
	
}