<?php
class dBmsAccount extends dBase {
	
    protected $_tablename = 'wmw_bms_account';
	protected $_fields = array(
		'base_account',
		'base_password',
		'base_name',
		'add_account',
		'add_time',
		'base_email'
	);
	protected $_pk = 'base_account';
	protected $_index_list = array(
	    'base_account',
	);

    public function _initialize() {
        $this->connectDb('bms', true);
    }
    
    //todolist数据的维度变化了
	public function getUserInfoByUid($uid) {
		return $this->getInfoByPk($uid);
		
	}
	
	//todolist exchange params
	public function modifyUserInfoByUid($uid, $datas) {
		return $this->modify($datas, $uid);
	}
	
	//todolist change param
	public function modifyBmsAccountByAccount($dataArr, $base_account) {
	    return $this->modify($dataArr, $base_account);
	}
	
	public function addBmsAccount($dataarr, $is_return_id) {
	    return $this->add($dataarr, $is_return_id);
	}
}
