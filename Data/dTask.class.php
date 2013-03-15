<?php
class dTask extends dBase {
	protected $_tablename = 'oa_task';
    protected $_fields = array(
	    'task_id',
	    'task_title',
	    'task_content',
	    'task_type',
	    'to_accounts',
	    'expiration_time',
	    'deadline_hours',
	    'add_time',
        'upd_time',
        'need_reply',
        'need_sms_remind',
        'need_sms_push',
        'tag_ids',
        'add_account',
        'school_id',
        'is_draft',
    );
    protected $_pk = 'task_id';
	protected $_index_list = array(
	    'task_id',
	    'add_account',
	);
	
    public function _initialize() {
        $this->connectDb('oa', true);
    }

    /**
     * 根据ID($tasek_ids) 获取工作详细信息
     * @param $task_ids 工作id
     * @param $orderby 排序
     * @return $new_task_list 工作的详细信息
     */ 
    public function getTaskById($task_ids, $orderby = null) {
    	return $this->getInfoByPk($task_ids, $orderby);
    }
        
    /**
     * 
     * 根据添加人账号($add_accounts) 获取工作信息列表
     * @param $add_accounts  添加人账号
     * @param $offset 开始编号
     * @param $length 取得记录条数（用于分页）
     * @return $new_task_list 工作的详细信息
     */
    public function getTaskByAddAccount($add_accounts, $offset = 0, $limit = 10) {
    	return $this->getInfoByFk($add_accounts, 'add_account', 'task_id desc', $offset, $limit);
    }

    /**
     * 根据学校id, 获取工作信息列表
     * @param $school_ids  学校id 
     * @return $new_task_list 工作的详细信息
     */
    public function getTaskBySchoolId($school_ids) {
        return $this->getInfoByFk($school_ids, 'school_id');
    }
    
    /**
     * 添加工作
     * @param $datas  要添加的内容 
     * @param $is_return_id 是否返回最后插入数据的id
     * @return $effect_row或者$insert_id 根据 $is_return_id 返回
     */
    public function addTask($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }

    /**
     * 更新工作
     * @param $datas   要更新的内容 
     * @param $task_id 工作id
     * @return 是否成功 成功返回影响的记录条数 否则返回false
     */
    public function modifyTask($datas, $task_id) {
        return $this->modify($datas, $task_id);
    }
      
    /**
     * 根据id 删除工作
     * @param $task_id 工作id
     * @return 是否成功 成功返回影响的记录条数 否则返回false
     */
    public function delTask($task_id) {
        return $this->delete($task_id);
    }
}