<?php
class mTaskReply extends mBase {
	protected $dTaskReply = null;
	
	public function __construct() {
		$this->dTaskReply = ClsFactory::Create('Data.dTaskReply');
	}
	
	//通过id获取工作回复详情
    public function getTaskReplyById($reply_ids) {
        if (empty($reply_ids)) {
            return false;
        }
        
        return $this->dTaskReply->getTaskReplyById($reply_ids);
    }
    
    //通过id获取工作回复详情（用于分页）
    public function getTaskReplyByTaskId($task_ids, $offset = 0, $limit = 10) {
        if (empty($task_ids)) {
            return false;
        }
        
        return $this->dTaskReply->getTaskReplyByTaskId($task_ids, $offset, $limit);
    }
    
    //添加工作回复
    public function addTaskReply($datas, $is_return_id=false) {
        if (empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->dTaskReply->addTaskReply($datas, $is_return_id);
    }
    
    //修改工作回复
    public function modifyTaskReply($datas, $reply_id) {
        if (empty($datas) || !is_array($datas) || empty($reply_id)) {
            return false;
        }
        
        return $this->dTaskReply->modifyTaskReply($datas, $reply_id);
    }
    
    //删除工作回复
    public function delTaskReply($reply_id) {
        if (empty($reply_id)) {
            return false;
        }
        
        return $this->dTaskReply->delTaskReply($reply_id);
    }
}