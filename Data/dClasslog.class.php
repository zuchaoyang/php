<?php

class dClasslog extends dBase {
		protected $_tablename = 'wmw_class_log';
		protected $_fields = array(
			'class_log_id', 
			'log_id', 
			'class_code', 
			'add_time'
		);
		protected $_pk = 'class_log_id';
		protected $_index_list = array(
		    'class_log_id',
		    'log_id',
		    'class_code',
		);

	/**
	 * 按班级编号获取日志内容列表
	 * @param $logid
	*/
	public function getLogInfoByClassCode($class_code) {
	    return $this->getInfoByFk($class_code, 'class_code');
	}
	
	/**
	 * 查找日志是否分享到班级
	 * @param $logid
	*/
	public function getClassLogByLogId($log_ids) {
	    return $this->getInfoByFk($log_ids, 'log_id');
	}

	//取消用户日志分享
	//todolist 特殊应用 通过非主键删除记录
	public function delClassLog($classlogid) {
        if (empty($classlogid)) {
           return false; 
        }
        $this->delete($classlogid);
		return true;
	}	

	public function addClassLogInfo($LogInfoData, $is_return_id = false) {
        if(empty($LogInfoData) && !is_array($LogInfoData)){
        	return false;	
        }
		$effect_rows = $this->add($LogInfoData,$is_return_id);
		
        return !empty($effect_rows) ? $effect_rows : false;
    }

}
