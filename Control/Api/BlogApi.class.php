<?php
/**
* author:sailong<shailong123@126.com>
* 功能：Blog manage
* 说明：作为日志操作的统一接口
* 
* @return json
*/
class BlogApi extends ApiController{
   
    /**
     * 通过日志id获取日志的评论信息
     * 注明：只支持单个日志的评论信息获取
     * @param $blog_id
     * @param $where_appends
     * @param $offset
     * @param $limit
     */
    public function getBlogCommentsByBlogId($blog_id, $where_appends, $offset = 0, $limit = 10) {
        if(empty($blog_id)) {
            return false;
        }
        
        $blog_id = is_array($blog_id) ? array_shift($blog_id) : $blog_id;
        
        import('@.Control.Api.BlogImpl.BlogComments');
        $BlogComments = new BlogComments();
        
        return $BlogComments->getBlogCommentsByBlogId($blog_id, $where_appends, $offset, $limit);
    }
    
    /**
     * 通过主键获取日志的评论信息
     * @param $comment_ids
     */
    public function getBlogCommentsById($comment_ids) {
        if(empty($comment_ids)) {
            return false;
        }
        
        import('@.Control.Api.BlogImpl.BlogComments');
        $BlogComments = new BlogComments();
        
        return $BlogComments->getBlogCommentsById($comment_ids);
    }
    
    /**
     * 添加日志评论信息
     * @param $comment_datas
     * @return 成功: 评论id；失败：false
     */
    public function addBlogComments($comment_datas) {
        if(empty($comment_datas) || !is_array($comment_datas)) {
            return false;
        }
        
        import('@.Control.Api.BlogImpl.BlogComments');
        $BlogComments = new BlogComments();
        
        return $BlogComments->addBlogComments($comment_datas);
    }
    
    /**
     * 删除日志的评论信息
     * @param $comment_id
     * @return   boolean true | false
     */
    public function delBlogComments($comment_id) {
        if(empty($comment_id)) {
            return false;
        }
        
        $comment_id = is_array($comment_id) ? array_shift($comment_id) : $comment_id;
        
        import('@.Control.Api.BlogImpl.BlogComments');
        $BlogComments = new BlogComments();
        
        return $BlogComments->delBlogComments($comment_id);
    }
    
    
    
}
