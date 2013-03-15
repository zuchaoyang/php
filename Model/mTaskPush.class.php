<?php
class mTaskPush extends mBase {
	protected $_dTaskPush = null;
	
	public function __construct() {
		$this->_dTaskPush = ClsFactory::Create('Data.dTaskPush');
	}

    /**
     * 根据$uids 获取该用户的工作（只是工作ID 具体工作内容看工作详情表oa_task）
     * @param $uids 推送接收人(工作接收人)
     * @param $offset limit开始
     * @param $end 结束（分页每页条数）
     * 
     * @return $new_taskpush_list 工作推送列表
     **/
    public function getTaskPushByUid($uids, $orderby = null, $offset = 0, $limit = 0) {
        if (empty($uids)) {
            return false;
        }
        
        return $this->_dTaskPush->getTaskPushByUid($uids, $orderby, $offset, $limit);
    }

    public function getTaskPushByTaskId($task_ids) {
        if (empty($task_ids)) {
            return false;
        }
        
        return $this->_dTaskPush->getTaskPushByTaskId($task_ids);
    }
    
    //添加工作推送
    public function addTaskPush($datas, $is_return_id=false) {
        if (empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dTaskPush->addTaskPush($datas, $is_return_id);
    }
    
    //批量添加 工作推送
    public function addTaskPushBat($dataarr) {
        if (empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dTaskPush->addBat($dataarr);
    }

    //修改工作推送
    public function modifyTaskPush($datas, $push_id) {
        if (empty($datas) || !is_array($datas) || empty($push_id)) {
            return false;
        }
        
        return $this->_dTaskPush->modifyTaskPush($datas, $push_id);
    }
    
    //删除工作推送
    public function delTaskPush($push_id) {
        if (empty($push_id)) {
            return false;
        }
        
        return $this->_dTaskPush->delTaskPush($push_id);
    }
    
    //通过账号和工作分类id获取task_id
    public function getTaskPushByClientAccountAndTaskType($client_account, $task_type, $offset = 0, $limit = 10) {
    	if (empty($client_account) || empty($task_type)) {
    		return false;
    	}
    	
		$wherearr = array(
			"client_account='$client_account'",
			"task_type = $task_type"
		);
		$orderby = "push_id desc";
		
    	$task_push_list = $this->_dTaskPush->getInfo($wherearr, $orderby, $offset, $limit);
    	
		return !empty($task_push_list) ? $task_push_list : false;
    }
        
    //通过时间段查询
    public function getTaskPushByAddTime($uid, $q_date, $h_date, $offset = 0, $limit = 10) {
    	$uid = $this->_dTaskPush->checkIds($uid);
    	if (empty($uid)) {
    		return false;
    	}

    	$wherearr[] = "client_account in(" . implode(',', $uid) . ")";
    	if (!empty($q_date) && empty($h_date)) {
			$wherearr[] = " add_time = $q_date";
		} elseif (!empty($q_date) && !empty($h_date)) {
			$wherearr[] =  "add_time > $q_date and add_time < $h_date";
		}

		$orderby = "push_id asc";
		
    	return $this->_dTaskPush->getInfo($wherearr, $orderby, $offset, $limit);
    }
}