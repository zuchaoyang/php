<?php
class mBlogComments extends mBase {
    protected $_dBlogComments = null;
    
    public function __Construct() {
        $this->_dBlogComments = ClsFactory::Create('Data.Blog.dBlogComments');
    }
    
    /**
     * 通过blog_id获取日志的评论信息
     * @param $blog_id
     * @param $where_appends
     * @param $offset
     * @param $limit
     */
    public function getBlogCommentsByBlogId($blog_id, $where_appends, $offset = 0, $limit = 10) {
        if(empty($blog_id)) {
            return false;
        }
        
        $where_arr = array();
        $where_arr[] = "blog_id in('" . implode("','", (array)$blog_id) . "')";
        if(!empty($where_appends)) {
            $where_arr = array_merge($where_arr, (array)$where_appends);
        }
        
        $comment_list = $this->_dBlogComments->getInfo($where_arr, 'comment_id desc', $offset, $limit);
        $new_comment_list = array();
        if(!empty($comment_list)) {
            foreach($comment_list as $comment_id=>$comment) {
                $new_comment_list[$comment['blog_id']][$comment_id] = $comment;
                unset($comment_list[$comment_id]);
            }
        }
        
        return !empty($new_comment_list) ? $new_comment_list : false;
    }
    
    /**
     * 通过主键获取评论信息
     * @param $comment_ids
     */
    public function getBlogCommentsById($comment_ids) {
        if(empty($comment_ids)) {
            return false;
        }
        
        return $this->_dBlogComments->getById($comment_ids);
    }
    
    
    /**
     * 分组统计
     * @param $up_ids
     */
    public function getBlogCommentsChildrenStatByUpid($up_ids) {
        if(empty($up_ids)) {
            return false;
        }
        
        $table_name = $this->_dBlogComments->getTableName();
         //统计孩子节点的个数
        $stat_sql = "select up_id, count(*) as nums from $table_name where up_id in('" . implode("','", (array)$up_ids) . "') group by up_id";
        $stat_list = $this->_dBlogComments->query($stat_sql);
        
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
    public function getBlogCommentsChildrenByUpid($up_ids, $each_limit = 5) {
        if(empty($up_ids)) {
            return false;
        }
        
        $table_name = $this->_dBlogComments->getTableName();
        
        $select_children_sql = "select * from (select * from $table_name a where a.up_id in('" . implode("','", (array)$up_ids) . "')) as b where " .
        	   				   "$each_limit>(select count(*) from $table_name c where c.up_id=b.up_id and c.comment_id > b.comment_id)";
        
        $comment_list = $this->_dBlogComments->query($select_children_sql);
        
        $new_comment_list = array();
        if(!empty($comment_list)) {
            foreach($comment_list as $comment_id => $comment) {
                $new_comment_list[$comment['up_id']][$comment['comment_id']] = $comment;
                unset($comment_list[$comment_id]);
            }
        }
        
        return !empty($new_comment_list) ? $new_comment_list : false;
    }
    
    
    /**
     * 获取二级评论信息
     * @param $up_ids
     */
    public function getBlogCommentsByUpId($up_ids) {
        if(empty($up_ids)) {
            return false;
        }
        
        return $this->_dBlogComments->getBlogCommentsByUpId($up_ids);
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