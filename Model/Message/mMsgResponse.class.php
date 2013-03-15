<?php

class mMsgResponse {
	protected $_dMsgResponse = null;
	public function __construct() {
		$this->_dMsgResponse = ClsFactory::create("Data.Message.dMsgResponse");
	}
	
    public function getMsgResponseById($res_id) {
        if(empty($res_id)){
			return false;
		}
		
		return $this->_dMsgResponse->getInfoByPk($res_id);
	}
	
	public function getMsgResponseByToAccount($to_account) {
		if(empty($to_account)){
			return false;
		}
		
		return $this->_dMsgResponse->getMsgResponseByToAccount($to_account);
	}
	
	public function addMsgResponse($dataarr, $is_return_id = false) {
		if(empty($dataarr) || !is_array($dataarr)) {
			return false;
		}
		
		return $this->_dMsgResponse->addMsgResponse($dataarr, $is_return_id);
	}
	
	public function delMsgResponse($msg_id) {
		if(empty($msg_id)) {
			return false;
		}
		
		return $this->_dMsgResponse->delMsgResponse($msg_id);
	}
	
	public function delMsgResponseForMe($to_account) {
		if(empty($to_account)) {
			return false;
		}
		
		$msg_list = $this->getMsgResponseByToAccount($to_account);
		if(empty($msg_list)) {
			$msg_arr = $msg_list[$to_account];
			foreach($msg_arr as $msg_id => $msg_info) {
				$this->delMsgResponse($msg_id);
			}
		}
	}
}