<?php
class dTaskPush extends dBase {
	protected $_tablename = 'oa_task_push';
    protected $_fields = array(
		'push_id',
	    'client_account',	
	   	'task_id',
	    'is_viewed',
	    'is_replied',
	    'add_time',
	    'task_type'
    );
    protected $_pk = 'push_id';
	protected $_index_list = array(
	    'push_id',
	    'client_account',
	    'task_id'
	);
    
    public function _initialize() {
        $this->connectDb('oa', true);
    }
    
    /**
     *
     * 根据$uids 获取该用户的工作（只是工作ID 具体工作内容看工作详情表oa_task）
     * @param $uids 推送接收人(工作接收人)
     * @param $offset limit开始
     * @param $end 结束（分页每页条数）
     * 
     * @return $new_taskpush_list 工作推送列表
     * 
     * 
     **/
    public function getTaskPushByUid($uids, $orderby = null, $offset = null, $limit = null) {
    	return $this->getInfoByFk($uids, 'client_account', $orderby, $offset, $limit);
    }
    
	/**
	 * 
     * 通过task_id获取工作推送的详情 （按id倒序排列） 外键查询
     * @param $task_ids 工作task_id)
     * @return $new_taskpush_list 工作推送列表
     **/
    public function getTaskPushByTaskId($task_ids) {
    	return $this->getInfoByFk($task_ids, 'task_id', 'push_id desc');
    }
    
	/**
     * 添加工作推送（单条添加）
     * @param $datas 推送内容
     * @param $is_return_id 是否返回 最后插入数据的id
     * @return  $i_num_rows，getLastInsID  默认返回影响记录的条数
     **/    
    public function addTaskPush($datas, $is_return_id=false) {
        return $this->add($datas, $is_return_id);
    }

    /**
     * 更新工作推送
     * @param $datas 推送内容
     * @param $push_id 推送数据的id
     * @return  成功返回影响记录的行数 失败返回false
     **/ 
    public function modifyTaskPush($datas, $push_id) {
        return $this->modify($datas, $push_id);
    }

    /**
     * 删除工作推送
     * @param $push_id 推送数据的id
     * @return  成功返回影响记录的行数 失败返回false
     **/ 
    public function delTaskPush($push_id) {
        return $this->delete($push_id);
    }
}