<?php
class dBlogComments extends dBase {
    protected $_pk = 'comment_id';
    protected $_tablename = 'wmw_blog_comments';
    protected $_fields = array(
                    'comment_id',
                    'blog_id',
                    'content',
                    'up_id',
                    'client_account',
                    'add_time',
                    'level',
              );
    protected $_index_list = array(
                    'comment_id',
                    'blog_id'
              );
              
    //根据日志评论ID获取信息列表          
    public function getById($comment_id) {
        return $this->getInfoByPk($comment_id);
    }
    //根据blog_id获取信息列表
    public function getListByBlogId($blog_id) {
        //三维
        return $this->getInfoByFk($blog_id, 'blog_id');
    }
    
    // 根据日志ID提取一级评论
    public function getFirstLevel($blog_id, $orderby, $offset=0,$limit=3) {
        $where_arr[] = 'blog_id='.$blog_id;
        $where_arr[] = 'level=1';
        
        return $this->getInfo($where_arr, $orderby, $offset, $limit);
    }
    //根据一级评论ID提取二级评论
    public function getSecondLevel($comment_id, $orderby, $offset=null,$limit=null) {
        $where_arr[] = 'up_id=' . $comment_id;
        $where_arr[] = 'level=2';
        
        return $this->getInfo($where_arr, $orderby, $offset, $limit);
    }
    //添加班级日志评论
    public function addComment($data, $is_return_id) {
        return $this->add($data, $is_return_id);
    }
    
    //根据评论ID修改信息
    public function modifyByCommentId($data, $comment_id) {
        return $this->modify($data, $comment_id);
    }
    
    //根据评论ID删除信息
    public function delByCommentId($comment_id) {
        return $this->delete($comment_id);   
    }
    
    //根据blog_id批量删除信息
    public function delAllByBlogId($blog_id) {
        if(empty($blog_id)) {
            return false;
        }
        
        $blog_id_str = implode(',', (array)$blog_id);
        $sql = "delete from {$this->_tablename} where blog_id in({$blog_id_str})";
        
        return $this->execute($sql);
    }                                                 
}