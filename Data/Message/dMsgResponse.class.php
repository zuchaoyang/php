<?php

class dMsgResponse  extends dBase{
	protected $_tablename = 'wmw_msg_response';
	protected $_pk = 'res_id';
	protected $_fields = array(
		'res_id',
		'content',
		'to_account',
		'add_account',
		'add_time',
	);
	protected $_index_list = array(
		'res_id',
		'to_account',
	);
	
    public function getMsgResponseById($res_id) {
		return $this->getInfoByPk($res_id);
	}
	
	public function getMsgResponseByToAccount($to_account) {
		return $this->getInfoByFk($to_account, 'to_account');
	}
	
	public function addMsgResponse($dataarr, $is_return_id) {
		return $this->add($dataarr, $is_return_id);
	}
	
	public function delMsgResponse($msg_id) {
		return $this->delete($msg_id);
	}
}
