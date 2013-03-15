<?php

class mOauthClient extends mBase{
	protected $_dOauthClient = null;
	
	public function __construct() {
		$this->_dOauthClient = ClsFactory::Create('Data.dOauthClient');
	}
	
	public function getOauthClientById($ids) { 
	    if(empty($ids)) {
        	return false;
        }

        return $this->_dOauthClient->getOauthClientById($ids);
	}
	
    public function addOauthClient($datas) {
        if(empty($datas)) {
            return false;
        }
        
        return $this->_dOauthClient->addOauthClient($datas);
    }    
}