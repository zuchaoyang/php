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
    
    /**
     * 分组统计
     * @param $up_ids
     */
    public function getMoodCommentsChildrenStatByUpid($up_ids) {
        if(empty($up_ids)) {
            return false;
        }
        
        $table_name = $this->_dMoodComments->getTableName();
         //统计孩子节点的个数
        $stat_sql = "select up_id, count(*) as nums from $table_name where up_id in('" . implode("','", (array)$up_ids) . "') group by up_id";
        $stat_list = $this->_dMoodComments->query($stat_sql);
        
        $new_stat_list = array();
        if(!empty($stat_list)) {
            foreach($stat_list as $stat) {
                $new_stat_list[$stat['up_id']] = $stat['nums'];
            }
        }
        
        return !empty($new_stat_list) ? $new_stat_list : false;
    }
    
    /**
     * 通过上级id获取对应的最新的孩子结点数
     * @param $up_ids
     * @param $each_limit
     */
    public function getMoodCommentsChildrenByUpid($up_ids, $each_limit = 5) {
        if(empty($up_ids)) {
            return false;
        }
        
        $table_name = $this->_dMoodComments->getTableName();
        
        $select_children_sql = "select * from (select * from $table_name a where a.up_id in('" . implode("','", (array)$up_ids) . "')) as b where " .
        	   				   "$each_limit>(select count(*) from $table_name c where c.up_id=b.up_id and c.comment_id > b.comment_id)";
        
        $comment_list = $this->_dMoodComments->query($select_children_sql);
        
        $new_comment_list = array();
        if(!empty($comment_list)) {
            foreach($comment_list as $comment_id => $comment) {
                $new_comment_list[$comment['up_id']][$comment['comment_id']] = $comment;
                unset($comment_list[$comment_id]);
            }
        }
        
        return !empty($new_comment_list) ? $new_comment_list : false;
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