<?php

class mOauthToken extends mBase{
	protected $_dOauthToken = null;
	
	public function __construct() {
		$this->_dOauthToken = ClsFactory::Create('Data.dOauthToken');
	}
	
	public function getOauthTokenById($ids) { 
	    if(empty($ids)) {
        	return false;
        }

        return $this->_dOauthToken->getOauthTokenById($ids);
	}
	
	public function getOauthTokenByAccessToken($ids) { 
	    if(empty($ids)) {
        	return false;
        }

        return $this->_dOauthToken->getOauthTokenByAccessToken($ids);
	}	
	
    public function addOauthToken($datas) {
        if(empty($datas)) {
            return false;
        }
        
        return $this->_dOauthToken->addOauthToken($datas);
    }    
}