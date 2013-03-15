<?php
class dEmailBinding extends dBase{
	protected $_pk = 'bind_id';
	protected $_tablename = 'wmw_email_binding';
	protected $_fields = array(
	    'bind_id',
    	'email',
	    'time33_key',
    	'client_account',
    	'add_time',
	);
	protected $_index_list = array(
        'bind_id',
		'email',
	    'client_account',
	    'time33_key',
	);
    
	//根据email 拿绑定数据 外键
	public function getEmailBindingByEmail($email) {
		if (empty($email)) {
			return false;
		}
		
		return $this->getInfoByFk($email, 'email');
	}
	
	/**
	 * 根据time33_key 拿绑定数据
	 * @param $time33_key
	 */
	public function getEmailBindingByTime33Key($time33_key) {
		if (empty($time33_key)) {
			return false;
		}
		
		return $this->getInfoByFk($time33_key, 'time33_key');
	}
	
    /**
     * 根据client_account 拿绑定数据
     * @param $account
     */
	public function getEmailBindingByClientAccount($account) {
		if (empty($account)) {
			return false;
		}
		
		return $this->getInfoByFk($account, 'client_account');
	}
	
	//根据Email 删除数据
	public function delEmailBinding($email) {
		if (empty($email)) {
			return false;
		}
		
		return $this->delete($email);
	}
	
	//add
	public function addEmailBinding($data, $is_return_id = false) {
		if (empty($data)) {
			return false;
		}
		
		return $this->add($data,$is_return_id);
	}
	
	//update
	public function modifyEmailBinding($data, $bind_id) {
		if (empty($data)) {
			return false;
		}
		
		return $this->modify($data, $bind_id);
	}	
}