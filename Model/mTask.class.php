<?php
class mTask extends mBase {
	protected $_dTask = null;
	
	public function __construct() {
		$this->_dTask = ClsFactory::Create('Data.dTask');
	}
	
	/**
     * 根据ID($tasek_ids) 获取工作详细信息(倒序排列)
     * @param $task_ids 工作id 
     * @return $tasklist 工作的详细信息
     */
    public function getTaskById($task_ids, $orderby = 'task_id desc') {
        if (empty($task_ids)) {
            return false;
        }
        
        $tasklist = $this->_dTask->getTaskById($task_ids, $orderby);
        if (!empty($tasklist) && is_array($tasklist)) {
            foreach($tasklist as $task_id=>$task) {
                $tasklist[$task_id] = $this->parseTask($task);
            }
        }
        
        return !empty($tasklist) ? $tasklist : false;
    }
       
    /**
     * 根据ID($tasek_ids) 获取工作详细信息(正序排列)
     * @param $task_ids 工作id 
     * @return $tasklist 工作的详细信息
     */
	public function getTaskByIdTime($task_ids) {
		return $this->getTaskById($task_ids, 'task_id asc');
	}
    
    /**
     * 根据ID($tasek_ids) 获取到期的工作(根据到期时间正序排列)
     * @param $task_ids 工作id 
     * @return $tasklist 工作的详细信息
     */
	public function getTaskByRemindTime($task_ids) {
    	$task_ids = $this->_dTask->checkIds($task_ids);
    	if (empty($task_ids)) {
            return false;
        }

        $start_time = time();
		$end_time = $start_time + 3600 * 24 * 3;
        $wherearr = array(
        	"task_id in(" . implode(",", $task_ids) . ")",
        	"expiration_time < $end_time",
        	"expiration_time > $start_time",
        );
        
        $orderby = "expiration_time asc";
        $task_list = $this->_dTask->getInfo($wherearr, $orderby);
        
        if (!empty($task_list) && is_array($task_list)) {
            foreach($task_list as $task_id=>$task) {
                $task_list[$task_id] = $this->parseTask($task);
            }
        }
        
        return !empty($task_list) ? $task_list : false;
    }
    
	/**
     * 根据学校id, 获取工作信息列表
     * @param $school_ids  学校id 
     * @return $tasklist 工作
     */
    public function getTaskBySchoolId($school_ids) {
        if (empty($school_ids)) {
            return false;
        }
        
        $taskarr = $this->_dTask->getTaskBySchoolId($school_ids);
        if (!empty($taskarr) && is_array($taskarr)) {
            foreach($taskarr as $school_id=>$tasklist) {
                foreach($tasklist as $task_id=>$task) {
                    $tasklist[$task_id] = $this->parseTask($task);
                }
                $taskarr[$school_id] = $tasklist;
            }
        }
        
        return !empty($taskarr) ? $taskarr : false;
    }
    
    /**
     * 根据添加人账号($add_accounts) 获取工作信息列表
     * @param $add_accounts  添加人账号
     * @param $offset 开始编号
     * @param $length 取得记录条数（用于分页）
     * @return $task_arr 工作
     */
     public function getTaskByAddAccount($add_accounts, $filters = array(), $offset = 0, $limit = 10) {
        if (empty($add_accounts)) {
            return false;
        }
        
        $task_arr = $this->_dTask->getTaskByAddAccount($add_accounts, $offset, $limit);
        if (!empty($filters) && is_array($filters) && !empty($task_arr)) {
            foreach($filters as $field=>$val) {
                $val = (array)$val;
                
                foreach($task_arr as $uid=>$list) {
                    foreach($list as $task_id=>$task) {   
                    	           	
                        if (isset($task[$field]) && !in_array($task[$field], $val)) {
                            unset($list[$task_id]);
                        }
                    }
                    $task_arr[$uid] = $list;
                }
            }
        }

         if (!empty($task_arr) && is_array($task_arr)) {
            foreach($task_arr as $add_account=>$tasklist) {
                foreach($tasklist as $task_id=>$task) {
                    $tasklist[$task_id] = $this->parseTask($task);
                }
                $task_arr[$add_account] = $tasklist;
            }
        }
        
        return !empty($task_arr) ? $task_arr : false;
     }
    
    /**
     * 根据账号和工作类型, 获取工作信息列表
     * @param $uid 账号
     * @param $task_type  工作类型
     * @param $offset 开始编号
     * @param $length 取得记录条数（用于分页）
     * 
     * @return $task_arr 工作的详细信息
     */	
    public function getTaskByAddAccountAndTaskType($add_account, $task_type, $offset = 0, $limit = 10) {
    	$add_account = is_array($add_account) ? array_shift($add_account) : $add_account;
        if (empty($add_account)) {
            return false;
        }
        
        $wherearr = array(
        	"add_account='$add_account'",
        );
        if (!empty($task_type)) {
        	$wherearr[] = "task_type='$task_type'";
        } 
        $orderby = "task_id desc";
  
        $task_arr = $this->_dTask->getInfo($wherearr, $orderby, $offset, $limit);
            
        return !empty($task_arr) ? $task_arr : false;
    }
    
    /**
     * 根据账号和工作类型, 获取工作信息列表
     * @param $uid 账号
     * @param $task_type  工作类型
     * @param $offset 开始编号
     * @param $length 取得记录条数（用于分页）
     * 
     * @return $task_arr 工作的详细信息
     */	
    public function getTaskByUidAndType($add_account, $task_type, $offset = 0, $limit = 10) {
    	return $this->getTaskByAddAccountAndTaskType($add_account, $task_type, $offset, $limit);
    }
     
    /**
     * 根据工作类型, 获取工作信息列表
     * 
     * @param $add_accounts  添加人账号
     * @param $offset 开始编号
     * @param $length 取得记录条数（用于分页）
     * 
     * @return $tasklist 工作的详细信息
     */
     public function getTaskByType($task_type, $school_id, $offset = 0, $limit = 10) {
     	if (empty($task_type) || empty($school_id)) {
     		return false;
     	}
     	
     	$wherearr = array(
     		"task_type=$task_type",
     		"school_id=$school_id"
     	);
     	$orderby = "task_id desc";
     	$task_list = $this->_dTask->getInfo($wherearr, $orderby, $offset, $limit);

     	if (!empty($task_list)) {
     	    foreach($task_list as $task_id=>$task) {
     	        $task_list[$task_id] = $this->parseTask($task);
     	    }
     	}
     	
     	return !empty($task_list) ? $task_list : false;
     }
     
    /**
     * 添加工作
     * @param $datas  要添加的内容 
     * @param $is_return_id 是否返回最后插入数据的id
     * @return $effect_row或者$insert_id 根据 $is_return_id 返回
     */  
    public function addTask($datas, $is_return_id = false) {
        if (empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dTask->addTask($datas, $is_return_id);
    }
    
    /**
     * 更新工作
     * @param $datas   要更新的内容 
     * @param $task_id 工作id
     * @return 是否成功 成功返回影响的记录条数 否则返回false
     */
    public function modifyTask($datas, $task_id) {
        if (empty($datas) || !is_array($datas) || empty($task_id)) {
            return false;
        }
        
        return $this->_dTask->modifyTask($datas, $task_id);
    }
    
    /**
     * 根据id 删除工作
     * @param $task_id 工作id
     * @return 是否成功 成功返回影响的记录条数 否则返回false
     */
    public function delTask($task_id) {
        if (empty($task_id)) {
            return false;
        }
        
        return $this->_dTask->delTask($task_id);
    }
    
    /**
     * HTML 实体转换为字符
     *  
     */
    protected function parseTask($task) {
        if (empty($task)) {
            return false;
        }
        
        if (!empty($task['to_accounts'])) {
            $task['to_accounts'] = json_decode($task['to_accounts'], true);
        }
        
        if (!empty($task['task_content'])) {
            $task['task_content'] = htmlspecialchars_decode($task['task_content']);
        }
        
        return $task;
    }
    
    /**
     * 通过时间段查询工作
     * @param $taskids 工作ids
     * @param $q_date 开始时间
     * @param $h_date 结束时间
     * @return $new_task_list 工作列表(二维数组
     */
    public function getTaskByTaskIdAndAddTime($taskids, $q_date, $h_date) {
    	$taskids = $this->_dTask->checkIds($taskids);
    	if (empty($taskids)) {
    		return false;
    	}
		
    	$wherearr[] =  "task_id in('" . implode("','", $taskids) . "')";
    	if (!empty($q_date)) {
    		if (empty($h_date)) {
		        $wherearr[] = "add_time=$q_date";
		    } else if ($h_date > $q_date) {
		        $wherearr[] = "add_time > $q_date and add_time < $h_date";
		    }
    	}
    	
    	$task_list = $this->_dTask->getInfo($wherearr);
    	
    	if (!empty($task_list)) {
    	    foreach($task_list as $task_id=>$task) {
    	        $task_list[$task_id] = $this->parseTask($task);
    	    }
    	}
    	
    	return !empty($task_list) ? $task_list : false; 
    }   
    
    /**
     * 通过时间段查询工作
     * @param $taskids 工作ids
     * @param $q_date 开始时间
     * @param $h_date 结束时间
     * @return $new_task_list 工作列表(二维数组
     */
    public function getTaskByDate($taskids, $q_date, $h_date) {
    	
    	return $this->getTaskByTaskIdAndAddTime($taskids, $q_date, $h_date);
    }
}