<?php

class dPersonlogs extends dBase {
	
    protected $_tablename = 'wmw_person_logs';
    protected $_fields = array(
        'log_id',
        'log_name',
    	'log_content', 
    	'log_type',
    	'read_count',
    	'log_status',
    	'add_account',
        'add_date',
        'upd_account',
        'upd_date',
        'contentbg',
	);
	protected $_pk = 'log_id';
    protected $_index_list = array(
        'log_id',
    	'add_account',
    );

	//按ID读取日志
	public function getPersonLogsById($log_ids) {
		return $this->getInfoByPk($log_ids);
	}
	
	//获取用户所有日志
	public function getPersonLogsByAddaccount($addAccount) {
		return $this->getInfoByFk($addAccount, 'add_account');
	}

	//更新日记阅读
	public function modifyPersonLogs($dataarr, $log_id) {
		return $this->modify($dataarr, $log_id);
	}

	//发布日记
    public function addPersonLogs($dataarr, $is_return_id = false) {
        return $this->add($dataarr, $is_return_id);
    }
    //删除日志
	public function delPersonLogs($logid) {
		return $this->delete($logid);
	}
}
