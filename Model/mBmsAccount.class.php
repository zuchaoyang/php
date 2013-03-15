<?php
class mBmsAccount extends mBase {
	
	protected $_dBmsAccount = null;
	
	public function __construct() {
		$this->_dBmsAccount = ClsFactory::Create('Data.dBmsAccount');
	}
	
	
	//通过Id得到用户信息
	public function getUserInfoByUid($uid) {
	    if (empty($uid)) {
	    	return false;
	    } 
	    
	    $baseinfos=$this->_dBmsAccount->getUserInfoByUid($uid);
	    
	    return $baseinfos;
	}
	
	//检测密码是否真确
	public function checkPassword($password, $uid) {
	    if (empty($uid) || empty($password)) {
	    	return false;
	    }
	    
	    $userinfo = $this->getUserInfoByUid($uid);
	    $userinfo = array_shift($userinfo);
	    
	    return !empty($userinfo) && $userinfo['base_password'] == $password ? true : false;
	}
	
    public function modifyBmsAccountByAccount($dataArr, $base_account){
	    if (empty($base_account) ||empty($dataArr)) {
	        return false;
	    }
	    
	    return $this->_dBmsAccount->modifyBmsAccountByAccount($dataArr, $base_account);
    }
    
    public function addBmsAccount($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dBmsAccount->addBmsAccount($dataarr, $is_return_id);
    }
}
