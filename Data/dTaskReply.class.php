<?php
class dTaskReply extends dBase {
	protected $_tablename = 'oa_task_reply';
    protected $_fields = array(
	    'reply_id',
	    'task_id',
	    'add_account',
	    'reply_content',
	    'add_time',
    );
    protected $_pk = 'reply_id';
	protected $_index_list = array(
	    'reply_id',
	    'task_id',
	);
    
    public function _initialize() {
        $this->connectDb('oa', true);
    }
    
    /**
     * 根据 回复id获取回复详情
     * 
     * @param $reply_ids 回复id 流水号主键
     * @return $new_reply_list 回复列表 （二维数组）
     **/
    public function getTaskReplyById($reply_ids) {
        return $this->getInfoByPk($reply_ids);
    }

    /**
     * 
     * 根据 回复id获取回复详情
     * 
     * @param $task_ids 工作id 外键
     * @param $offset limit 开始
     * @param $length 取的条数
     * @return $new_reply_list 回复列表 （二维数组）
     **/
    public function getTaskReplyByTaskId($task_ids, $offset = 0, $limit = 10) {
    	return $this->getInfoByFk($task_ids, 'task_id', 'reply_id desc', $offset, $limit);
    }
    
    /**
     * 添加 回复
     * 
     * @param $datas 回复内容
     * @param $offset limit 开始
     * @param $length 取的条数
     * @return  $i_num_rows，getLastInsID  默认返回影响记录的条数）
     **/								 
    public function addTaskReply($datas, $is_return_id=false) {
        return $this->add($datas, $is_return_id);
    }

    /**
     * 修改 回复
     * 
     * @param $datas 回复内容
     * @param $reply_id 回复id
     * @return  成功返回影响记录的行数 失败返回false;
     **/    
    public function modifyTaskReply($datas, $reply_id) {
        return $this->modify($datas, $reply_id);
    }

    /**
     * 删除回复
     * @param $reply_id 回复id
     * @return  成功返回影响记录的行数 失败返回false;
     **/     
    public function delTaskReply($reply_id) {
        return $this->delete($reply_id);
    }
}