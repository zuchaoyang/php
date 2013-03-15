<?php
/**
 *wms 角色设置  	
 */
class mRoleInfo extends mBase{
	protected $_dRoleInfo = null;
	
	public function __construct() {
		$this->_dRoleInfo = ClsFactory::Create('Data.dRoleInfo');
	}

    //通过主键查询
	public function getRoleInfoById($role_code) {
		if (empty($role_code)) {
			return false;
		}
		
		return $this->_dRoleInfo->getRoleInfoById($role_code);
	}
	
	public function getRoleInfo($offset=0, $limit=15) {
	    return $this->_dRoleInfo->getInfo(null, null, $offset, $limit);
	}
	
    public function getRoleInfoByRoleName($role_name) {
	    if(empty($role_name)) {
	        return false;
	    }
	    $wheresql = array(
	    	"role_name='$role_name'",
	    );
	    $list = $this->_dRoleInfo->getInfo($wheresql);

	    return !empty($list) ? $list :false;
    }
	
	public function addRoleInfo($dataarr) {
	    if(empty($dataarr)) {
	        return false;
	    }
	    
	    $effect_rows = $this->_dRoleInfo->addRoleInfo($dataarr);

	    return !empty($effect_rows) ? $effect_rows :false;
	}
}
