<?php
class mClasslog extends mBase {
	
	protected $_dClasslog = null;
	
	public function __construct() {
		$this->_dClasslog = ClsFactory::Create('Data.dClasslog');
	}
	/**
	 * 查找日志是否分享到班级
	 * @param $logid
	*/
   
   public function getClassLogByLogId($logid) {
   		if (empty($logid)) {
   			return false;
   		}
   		
		return $this->_dClasslog->getClassLogByLogId($logid);
    }

	/**
	 * 查找日志对应的班级分享是否存在
	 * @param $logid
	*/
    //todolist 特殊应用处理
	public function findLogexistsByClassCodeLogid($logid, $class_code) {
        if (empty($logid) || empty($class_code)) {
           return false; 
        }
        
	    $logid = array_unique((array)$logid);
		$condition = " log_id in(".implode(',',$logid).") and class_code=".$class_code;
		
        $class_log_arr = $this->_dClasslog->getInfo($condition);
        
		return !empty($class_log_arr) ? $class_log_arr : false;
	}

	/**
	 * 查找班级所有日志列表
	 * @param $class_code
	*/
   
   public function getLogInfoByClassCode($class_code) {
   		if (empty($class_code)) {
   			return false;
   		}
   		
		return $this->_dClasslog->getLogInfoByClassCode($class_code);
    }


	
	/**
	 * 取消用户日志分享
	 * @param $logid
	*/
   
   public function delClassLog($classlogid) {
   		if (empty($classlogid)) {
   			return false;
   		}
		return $this->_dClasslog->delClassLog($classlogid);
    }

	public function addClassLogInfo($LogInfoData, $is_return_id = false) {
   		if (empty($LogInfoData)) {
   			return false;
   		}
   		
		return $this->_dClasslog->addClassLogInfo($LogInfoData, $is_return_id);
    }

}
