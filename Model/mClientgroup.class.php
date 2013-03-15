<?php
class mClientgroup extends mBase {
	
	protected $_dClientgroup = null;

	public function __construct(){
		$this->_dClientgroup = ClsFactory::Create('Data.dClientgroup');
	}
	
	//按名称ID
	public function getClientGroupById($group_id) {
		if(empty($group_id)) {
			return false;
		}
		
		return  $this->_dClientgroup->getClientGroupById($group_id);
	}
	
	/**
	 * 获取用户所有分组内容
	 * @param $account
	*/
	public function getClientGroupByUid($account) {
		if(empty($account)) {
			return false;
		}
		
		$clientgroupInfos = $this->_dClientgroup->getClientGroupByUid($account);
		
		return !empty($clientgroupInfos) ? $clientgroupInfos : false;
	}


	public function getClientGroupByGroupName($groupName,$account) {
		if(empty($groupName) || empty($account)) {
			return false;
		}
		
		$wheresql = array(
            "client_account in(" . implode(',' , (array)$account) . ")",
            "group_name='" . $groupName . "'"
        );
        
        return $this->_dClientgroup->getInfo($wheresql);
	}
	
	public function getClientGroupByaddAccount($add_account) {
	    if(empty($add_account)) {
	        return false;
	    }
	    
	    $wheresql = array(
            'add_account = ' . $add_account
        );
        
        return $this->_dClientgroup->getInfo($wheresql);
	    
	}


	//添加分组
	public function addClientGroup($datarr, $is_return_id) {
		if(empty($datarr)) {
			return false;
		}
		
		return  $this->_dClientgroup->addClientGroup($datarr, $is_return_id);
	}

	public function modifyClientGroup($datarr, $group_id) {
		if(empty($datarr) || empty($group_id)) {
			return false;
		}
		
		return  $this->_dClientgroup->modifyClientGroup($datarr, $group_id);
	}
	
	public function delClientGroup($group_id) {
        if(empty($group_id)) {
			return false;
        }
        
        return $this->_dClientgroup->delClientGroup($group_id);
    }
}
