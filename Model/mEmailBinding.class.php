<?php
class mEmailBinding extends mBase{
	
	protected $_dEmailBinding = null;
	
	public function __construct() {
		$this->_dEmailBinding = ClsFactory::Create('Data.dEmailBinding');
	}
	
	/**
	 * 根据email 外键  拿绑定数据
	 */
	public function getEmailBindingByEmail($email) {
		if (empty($email)) {
			return false;
		}
		
		return $this->_dEmailBinding->getEmailBindingByEmail($email);
	}
	
    //根据time33_key 拿绑定数据
	public function getEmailBindingByTime33Key($time33_key) {
		if (empty($time33_key)) {
			return false;
		}
		
		return $this->_dEmailBinding->getEmailBindingByTime33Key($time33_key);
	}
	
	/**
     * 根据client_account 拿绑定数据
     * @param $account
     */
	public function getEmailBindingByClientAccount($account) {
		if (empty($account)) {
			return false;
		}
		
		return $this->_dEmailBinding->getEmailBindingByClientAccount($account);
	}
	
	//根据Email 删除数据
	public function delEmailBinding($email) {
		if (empty($email)) {
			return false;
		}
		
		return $this->_dEmailBinding->delEmailBinding($email);
	}
	
	//add
	public function addEmailBinding($data) {
		if (empty($data)) {
			return false;
		}
		
		return $this->_dEmailBinding->addEmailBinding($data);
	}
	
	//update
	public function modifyEmailBinding($data, $bind_id){
		if (empty($data)) {
			return false;
		}
		
		return $this->_dEmailBinding->modifyEmailBinding($data, $bind_id);
	}	
}