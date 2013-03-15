<?php
class mBlogComments extends mBase {
    protected $_dBlogComments = null;
    
    public function __Construct() {
        $this->_dBlogComments = ClsFactory::Create('Data.Blog.dBlogComments');
    }
              
    //根据日志评论ID获取信息列表          
    public function getById($comment_ids) {
        if(empty($comment_ids)) {
            return false;
        }
        import('@.Common_wmw.WmwFace');
        $comment_list = $this->_dBlogComments->getById($comment_ids);
        
        if(!empty($comment_list)) {
            foreach($comment_list as $comment_id => $comment){
                $comment['content'] = WmwFace::parseFace($comment['content']);
                $comment_list[$comment_id] = $comment;
            }
        }
        
        return !empty($comment_list) ? $comment_list : false;
    }
    //根据blog_id获取信息列表
    public function getListByBlogId($blog_ids) {
        //三维
        if(empty($blog_ids)) {
            return false;
        }
        
        import('@.Common_wmw.WmwFace');
        $comment_list = $this->_dBlogComments->getListByBlogId($blog_ids, 'blog_id');
        if(!empty($comment_list)) {
            foreach($comment_list as $blog_id => $comments){
                foreach($comments as $comment_id => $comment) {
                    $comment['content'] = WmwFace::parseFace($comment['content']);
                    $comments[$comment_id] = $comment;
                }
                $comment_list[$blog_id] = $comments;
            }
        }
        
        return !empty($comment_list) ? $comment_list : false;
    }
    // 根据日志ID提取一级评论
    public function getFirstLevel($blog_id, $orderby=null, $offset=0,$limit=3) {
        if(empty($blog_id)) {
            return false;
        }
        
        import('@.Common_wmw.WmwFace');
        $comment_list = $this->_dBlogComments->getFirstLevel($blog_id, $orderby, $offset, $limit);
        
        if(!empty($comment_list)) {
            foreach($comment_list as $comment_id => $comment){
                $comment['content'] = WmwFace::parseFace($comment['content']);
                $comment_list[$comment_id] = $comment;
            }
        }
        
        return !empty($comment_list) ? $comment_list : false;
    }
    //根据一级评论ID提取二级评论
    public function getSecondLevel($comment_id, $orderby=null, $offset=null,$limit=null) {
        if(empty($comment_id)) {
            return false;
        }
        
        import('@.Common_wmw.WmwFace');
        $comment_list = $this->_dBlogComments->getSecondLevel($comment_id, $orderby, $offset, $limit);
        
        if(!empty($comment_list)) {
            foreach($comment_list as $comment_id => $comment){
                $comment['content'] = WmwFace::parseFace($comment['content']);
                $comment_list[$comment_id] = $comment;
            }
        }
        
        return !empty($comment_list) ? $comment_list : false;
    }
    //添加班级日志评论
    public function addComment($data, $is_return_id) {
        return $this->_dBlogComments->addComment($data, $is_return_id);
    }
    
    //根据评论ID修改信息
    public function modifyByCommentId($data, $comment_id) {
        return $this->_dBlogComments->modifyByCommentId($data, $comment_id);
    }
    
    //根据评论ID删除信息
    public function delByCommentId($comment_id) {
        return $this->_dBlogComments->delByCommentId($comment_id);   
    }
    
    //根据blog_id批量删除信息
    public function delAllByBlogId($blog_id) {
        if(empty($blog_id)) {
            return false;
        }
        
        return $this->_dBlogComments->delAllByBlogId($blog_id);
    }                                                         
}