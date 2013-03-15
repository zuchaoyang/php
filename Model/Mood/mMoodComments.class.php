<?php
class mMoodComments extends mBase{
    protected $_dMoodComments = null;
    
    public function __construct() {
        $this->_dMoodComments = ClsFactory::Create('Data.Mood.dMoodComments');
    }
    
    public function getMoodCommentsById($comment_ids) {
        if(empty($comment_ids)) {
            return false;
        }
        
        return $this->_dMoodComments->getMoodCommentsById($comment_ids);
    }
    
    /**
     * 通过mood_id获取相关的评论信息
     * @param $mood_ids
     * @param $where_appends
     * @param $offset
     * @param $limit
     */
    public function getMoodCommentsByMoodId($mood_ids, $where_appends, $offset = 0, $limit = 10) {
        if(empty($mood_ids)) {
            return false;
        }
        
        $where_arr = array();
        $where_arr[] = "mood_id in('" . implode("','", (array)$mood_ids) . "')";
        if(!empty($where_appends)) {
            $where_arr = array_merge($where_arr, (array)$where_appends);
        }
        
        $comment_list = $this->_dMoodComments->getInfo($where_arr, 'comment_id desc', $offset, $limit);
        $comment_arr = array();
        if(!empty($comment_list)) {
            foreach($comment_list as $key=>$comment) {
                $comment_arr[$comment['mood_id']][$comment['comment_id']] = $comment;
                unset($comment_list[$key]);
            }
        }
        
        return !empty($comment_arr) ? $comment_arr : false;
    }
    
    public function getMoodCommentsByUpid($up_ids) {
        if(empty($up_ids)) {
            return false;
        }
        return $this->_dMoodComments->getMoodCommentsByUpid($up_ids);
    }
    
    public function addMoodComments($datas, $is_return_id = false) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dMoodComments->addMoodComments($datas, $is_return_id);
    }
    
    public function modifyMoodComments($datas, $comment_id) {
        if(empty($datas) || !is_array($datas) || empty($comment_id)) {
            return false;
        }
        
        return $this->_dMoodComments->modifyMoodComments($datas, $comment_id);
    }
    
    public function delMoodComments($comment_id) {
        if(empty($comment_id)) {
            return false;
        }
        
        return $this->_dMoodComments->delMoodComments($comment_id);
    }
    
    
    
}