<?php
class dMoodComments extends dBase{
    protected $_tablename = 'wmw_mood_comments'; //主表
    protected $_fields = array(
        'comment_id',
        'up_id',
        'mood_id',
        'content',
        'client_account',
        'add_time',
        'level'
    );
    protected $_pk = 'comment_id';
    protected $_index_list = array(
        'comment_id',
        'up_id',
        'mood_id',
    );
    
    public function getMoodCommentsById($comment_ids) {
        return $this->getInfoByPk($comment_ids);
    }
    
    public function getMoodCommentsByUpid($up_ids) {
        return $this->getInfoByFk($up_ids, 'up_id', 'comment_id desc');
    }
    
    public function addMoodComments($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }
    
    public function modifyMoodComments($datas, $comment_id) {
        return $this->modify($datas, $comment_id);
    }
    
    public function delMoodComments($comment_id) {
        return $this->delete($comment_id);
    }
}