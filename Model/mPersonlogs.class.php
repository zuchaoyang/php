<?php

class mPersonlogs extends mBase {
	
    protected $_dPersonlogs = null;
    
    public function __construct() {
        $this->_dPersonlogs = ClsFactory::Create('Data.dPersonlogs');
    }
    
    /**
     * 通过主键获取日志信息
     * @param $log_ids
     */
    public function getPersonLogsById($log_ids) {
        if(empty($log_ids)) {
            return false;
        }
        
        return $this->_dPersonlogs->getPersonLogsById($log_ids);
    }
    
	/**
	 * 通过添加人获取日志信息
	 * @param $add_accounts
	 * @param $filters
	 */
	public function getPersonLogsByAddaccount($add_accounts, $filters = array()) {
	    if(empty($add_accounts)) {
	        return false;
	    }
	    
	    $log_list = $this->_dPersonlogs->getPersonLogsByAddaccount($add_accounts);
	    if(!empty($log_list) && !empty($filters)) {
	        foreach($filters as $field=>$val) {
	            $val = (array)$val;
	            foreach($log_list as $add_account=>$list) {
	                foreach($list as $log_id=>$log) {
	                    if(!isset($log[$field])) {
	                        break 2;
	                    }
	                    if(!in_array($log[$field], $val)) {
	                        unset($list[$log_id]);
	                    }
	                }
	                $log_list[$add_account] = $list;
	            }
	        }
	    }
	    
	    return !empty($log_list) ? $log_list : false;
	}

	public function modifyPersonLogs($datas, $log_id) {
	    if(empty($datas) || !is_array($datas) || empty($log_id)) {
	        return false;
	    }
	    
	    return $this->_dPersonlogs->modifyPersonLogs($datas, $log_id);
	}

	/**
	 * 通过主键删除
	 * @param $log_id
	 */
	public function delPersonLogs($log_id) {
	    if(empty($log_id)) {
	        return false;
	    }
	    
	    return $this->_dPersonlogs->delPersonLogs($log_id);
	}

	/**
	 * 添加个人日志信息
	 * @param  $datas
	 * @param  $is_return_id
	 */
	public function addPersonLogs($datas, $is_return_id = false) {
	    if(empty($datas) || !is_array($datas)) {
	        return false;
	    }
	    
	    return $this->_dPersonlogs->addPersonLogs($datas, $is_return_id);
	}
}
