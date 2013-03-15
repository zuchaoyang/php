<?php

class dClasstalkcomment extends dBase {
	
	protected $_tablename = 'wmw_class_talkcomment';
    protected $_fields = array(
        'comment_id',
        'talk_id',
        'comment_content', 
        'add_account', 
        'add_date',
	 );
	 protected $_pk = 'comment_id';
	 protected $_index_list = array(
	     'comment_id',
	     'talk_id',
	 );
	
	/*获取评论内容*/
	public function getCommentListByTalkId($talk_ids) {
	    return $this->getInfoByFk($talk_ids, 'talk_id');
	}

	/**删除评论
	 * 
	 */
	public function delCommentById($comment_id) {
		return $this->delete($comment_id);
	}

	//todolist 非规则 MMM
    public function addClassTalkcomment($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }
	
}
