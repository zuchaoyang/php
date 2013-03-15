<?php
class mMsgRequire extends mBase{
	protected $_dMsgRequire = null;
	public function __construct() {
		$this->_dMsgRequire = ClsFactory::Create("Data.Message.dMsgRequire");
	}
	
    public function getMsgRequireById($req_id) {
        if(empty($req_id)){
			return false;
		}
		$this->_dMsgRequire->getInfoByPk($req_id);
		return $this->_dMsgRequire->getInfoByPk($req_id);
	}
	
	public function getMsgRequireByToAccount($to_account,$offset,$limit) {
		if(empty($to_account)){
			return false;
		}
		
		return $this->_dMsgRequire->getMsgRequireByToAccount($to_account,$offset,$limit);
	}
	
	//依据add_account查询
    public function getMsgRequireByAddAccount($add_account,$new_friend_friend_arr) {
		if(empty($add_account) ||empty($new_friend_friend_arr) ){
			return false;
		}
		
		$wheresql[] = "add_account={$add_account}";
		$wheresql[] = "to_account in('".implode("','",(array)$new_friend_friend_arr)."')";
		
		return $this->_dMsgRequire->getMsgRequireByAddAccount($wheresql);
    }
	
	
	public function addMsgRequire($dataarr, $is_return_id = false) {
		if(empty($dataarr) || !is_array($dataarr)) {
			return false;
		}
		
		return $this->_dMsgRequire->addMsgRequire($dataarr, $is_return_id);
	}
	
	public function delMsgRequire($msg_id) {
		if(empty($msg_id) || is_array($msg_id)) {
			return false;
		}
		
		return $this->_dMsgRequire->delMsgRequire($msg_id);
	}
	
	public function modifyMsgRequire($datarr,$req_id) {
	    if(empty($req_id) || empty($datarr)) {
	        return false;
	    }
	    
	    return $this->_dMsgRequire->modifyMsgRequire($datarr,$req_id);
	}
	
	public function delMsgRequireForMe($to_account) {
		if(empty($to_account)) {
			return false;
		}
		
		$msg_list = $this->getMsgRequireByToAccount($to_account);
		if(empty($msg_list)) {
			$msg_arr = $msg_list[$to_account];
			foreach($msg_arr as $msg_id => $msg_info) {
				$this->delMsgRequrie($msg_id);
			}
		}
	}
	
}