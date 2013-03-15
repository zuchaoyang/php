<?php
//郭学文
class mAccountLock extends mBase{
	protected $_dAccountLock = null;
	
	public function __construct() {
		$this->_dAccountLock = ClsFactory::Create('Data.dAccountLock');
	}
	
	public function getAccountLockById($ids) { 
	    if(empty($ids)) {
        	return false;
        }

        return $this->_dAccountLock->getAccountLockById($ids);
	}
	
    public function addAccountLock($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        
        return $this->_dAccountLock->addAccountLock($dataarr);
    }
    
    public function delAccountLock($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dAccountLock->delAccountLock($id);
    } 
	
	public function getAccountLock($offset = 0, $length = 10) {
	    return $this->_dAccountLock->getInfo(null, null, $offset, $length);
	}
	
    public function getAccountLockByAccountLength($account_length, $offset=0, $limit=10) {
        if(empty($account_length)) {
            return false;
        }
        
        $wheresql = "account_length='$account_length'";
        return $this->_dAccountLock->getInfo($wheresql, null, $offset, $limit);
    }
    
    //按account_length统计账号
    public function getAccountLockTotalByAlength($account_length) {
        if(empty($account_length)) {
            return false;
        }
        
        $wheresql = "account_length='$account_length'";
        return $this->_dAccountLock->getCount($wheresql);
    }
    
}