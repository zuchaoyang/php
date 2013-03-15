<?php

class mOauthCode extends mBase{
	protected $_dOauthCode = null;
	
	public function __construct() {
		$this->_dOauthCode = ClsFactory::Create('Data.dOauthCode');
	}
	
	public function getOauthCodeById($ids) { 
	    if(empty($ids)) {
        	return false;
        }

        return $this->_dOauthCode->getOauthCodeById($ids);
	}
	
    public function addOauthCode($datas) {
        if(empty($datas)) {
            return false;
        }
        
        return $this->_dOauthCode->addOauthCode($datas);
    }    
}