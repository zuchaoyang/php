<?php
class mLogtypes extends mBase {
		
	protected $_dLogtypes = null;
	
	public function __construct() {
	    $this->_dLogtypes = ClsFactory::Create('Data.dLogtypes');
	}
	
	/**
	 * 通过主键获取获取信息
	 * @param $logtype_ids
	 */
	public function getLogTypesById($logtype_ids) {
	    if(empty($logtype_ids)) {
	        return false;
	    }
	    
	    return $this->_dLogtypes->getLogTypesById($logtype_ids);
	}
	
	/**
	 * 通过添加人获取日志分类信息
	 * @param $add_accounts
	 */
	public function getLogTypesByAddaccount($add_accounts) {
	    if(empty($add_accounts)) {
	        return false;
	    }
	    
	    return $this->_dLogtypes->getLogTypesByAddaccount($add_accounts);
	}
	
	/**
	  * 删除日志分类
	  * @param $logtype_id
	  * @return $effect_row 影响行数
	  */
	public function delLogTypes($logtype_id) {
	    if(empty($logtype_id)) {
	        return false;
	    }
	    
	    return $this->_dLogtypes->delLogTypes($logtype_id);
	}
	
	/*添加分类
     * @param $LogTypesData
     * @param $is_return_insert_id
     * return $effect_rows OR $insertId
     */
    public function addLogTypes($LogTypesData, $is_return_id) {
        if(empty($LogTypesData) || !is_array($LogTypesData)) {
            return false;
        }
        
		return $this->_dLogtypes->addLogTypes($LogTypesData, $is_return_id);
    }
    
    /**
     * 修改分类信息
     * @param $datas
     * @param $logtypes_id
     */
	public function modifyLogTypes($datas, $logtypes_id) {
	    if(empty($datas) || !is_array($datas) || empty($logtypes_id)) {
	        return false;
	    }
	    
	    return $this->_dLogtypes->modifyLogTypes($datas, $logtypes_id);
	}

	/**************************************************************************
	 * 特殊业务函数
	 *************************************************************************/
	/**
	 * 通过添加人和类型获取日志信息
	 * @param $add_account
	 * @param $create_type
	 */
	public function getLogTypesByAddaccountAndCreatetype($add_account, $create_type = LOG_SYS_CREATE) {
	    if(empty($add_account) || empty($create_type)) {
	        return false;
	    }
	    
	    $create_type = is_array($create_type) ? array_shift($create_type) : $create_type;
	    $create_type = in_array($create_type, array(LOG_USER_CREATE, LOG_SYS_CREATE)) ? $create_type : LOG_SYS_CREATE;
	    
	    $add_account = (array)$add_account;
	    $wheresql = "add_account in('" . implode("','", $add_account) . "') and log_create_type='$create_type'";
	    $logtypes_list = $this->_dLogtypes->getInfo($wheresql);
	    $new_logtypes_list = array();
	    if(!empty($logtypes_list)) {
	        foreach($logtypes_list as $logtype) {
	            $new_logtypes_list[$logtype['add_account']][$logtype['logtype_id']] = $logtype;
	        }
	    }
	    
	    return !empty($new_logtypes_list) ? $new_logtypes_list : false;
	}
	
	/**
	 * 通过添加人日志名称获取日志信息
	 * @param $add_account
	 * @param $logtype_name
	 * 
	 * rename getLogTypesByAddaccountAndTypeName to checkNameIsExist
	 */
	//todolist 根据具体的业务可以考虑替换改方法，估计是用于用户添加分类是判断名字是否重复
	
	public function checkNameIsExist($add_account, $logtype_name) {
	    if(empty($add_account) || empty($logtype_name)) {
	        return false;
	    }
	    
	    $add_account = is_array($add_account) ? array_shift($add_account) : $add_account;
	    $logtype_name = is_array($logtype_name) ? array_shift($logtype_name) : $logtype_name;
	    $logtype_name = trim(str_replace(array('_', '%'), "", $logtype_name));
	    
	    $wheresql = "add_account='$add_account' and logtype_name='$logtype_name'";
	    $logtypes_list = $this->_dLogtypes->getInfo($wheresql, null, 0, 1);
	    
	    return !empty($logtypes_list) ? true : false;
	}
	
	public function getLogTypeListAndLogNums($add_accounts) {
	    
	    return $this->getInfoByFk($add_accounts, 'add_account');
	}
}
