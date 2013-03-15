<?php

class mOauthBind extends mBase{
	protected $_dOauthBind = null;
	
	public function __construct() {
		$this->_dOauthBind = ClsFactory::Create('Data.dOauthBind');
	}
	
	public function getOauthBindById($ids) { 
	    if(empty($ids)) {
        	return false;
        }

        return $this->_dOauthBind->getOauthBindById($ids);
	}
	
	public function getOauthBindByClientAccount($account) { 
	    if(empty($account)) {
        	return false;
        }

        return $this->_dOauthBind->getOauthBindByClientAccount($account);
	}	
	
	public function getOauthBindBySocialAccount($account) { 
	    if(empty($account)) {
        	return false;
        }

        return $this->_dOauthBind->getOauthBindBySocialAccount($account);
	}	
	
	public function getOauthBindByClientAccountAndType($params) { 
	    if(empty($params)) {
        	return false;
        }

        return $this->_dOauthBind->getOauthBindByClientAccountAndType($params);
	}	

	public function getOauthBindBySocialAccountAndType($params) { 
	    if(empty($params)) {
        	return false;
        }

        return $this->_dOauthBind->getOauthBindBySocialAccountAndType($params);
	}		
	
    public function addOauthBind($datas) {
        if(empty($datas)) {
            return false;
        }
        
        return $this->_dOauthBind->addOauthBind($datas);
    }
    
    public function modifyOauthBindByClientAccount($datas, $client_account) {
        if(empty($datas)) {
            return false;
        }
        
        return $this->_dOauthBind->modifyOauthBindByClientAccount($datas, $client_account);
    }        
}