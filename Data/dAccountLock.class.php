<?php
//郭学文
class dAccountLock extends dBase{
	
	protected $_tablename = 'wmw_account_lock';
    protected $_fields = array(
      'lock_account',
      'account_length',
      'add_account',
      'add_date'
    );
    protected $_pk = 'lock_account';
    protected $_index_list = array(
        'lock_account'
    );
    

    //通过uid查询数据
    public function getAccountLockById($ids) {
        return $this->getInfoByPk($ids);
    }
    
    public function addAccountLock($dataarr) {
        return $this->add($dataarr);
    }
    
    public function delAccountLock($id) {
        return $this->delete($id); 
    }
    
}