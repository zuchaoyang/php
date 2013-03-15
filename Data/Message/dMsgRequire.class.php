<?php

class dMsgRequire extends dBase{
	protected $_tablename = 'wmw_msg_require';
	protected $_pk = 'req_id';
	protected $_fields = array(
		'req_id',
		'content',
		'to_account',
		'add_account',
		'add_time',
	);
	protected $_index_list = array(
		'req_id',
		'to_account',
	    'add_account',
	);
	
    public function getMsgRequireById($req_id) {
		return $this->getInfoByPk($req_id);
	}
	
	public function getMsgRequireByToAccount($to_account,$offset,$limit) {
		return $this->getInfoByFk($to_account, 'to_account',null,$offset,$limit);
	}
	
    public function getMsgRequireByAddAccount($wheresql) {
		return $this->getInfo($wheresql);
	}
	
	public function addMsgRequire($dataarr, $is_return_id) {
		return $this->add($dataarr, $is_return_id);
	}
	
	public function modifyMsgRequire($datarr,$req_id) {
	    return $this->modify($datarr,$req_id);
	}
	
	public function delMsgRequire($msg_id) {
		return $this->delete($msg_id);
	}
}
