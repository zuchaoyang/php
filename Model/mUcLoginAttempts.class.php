<?php
class mUcLoginAttempts extends mBase {
    protected $_dLoginAttmepts = null;
    
    public function __construct() {
        $this->_dLoginAttmepts = ClsFactory::Create('Data.dUcLoginAttempts');
    }

    public function getLoginAttemptsById($uids) {
        if(empty($uids)) {
            return false;
        }
        return $this->_dLoginAttmepts->getLoginAttemptsById($uids);
    }    
    
    public function addLoginAttempts($datas, $is_return_id = false) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        return $this->_dLoginAttmepts->addLoginAttempts($datas, $is_return_id);
    }    
    
    public function modifyLoginAttempts($datas, $uid) {
        if(empty($datas) || empty($uid) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dLoginAttmepts->modifyLoginAttempts($datas, $uid);
    }    
    
	public function delLoginAttempts($uid) {
		if (empty($uid)) {
			return false;
		}
		
	    return $this->_dLoginAttmepts->delLoginAttempts($uid);
	}    
} 