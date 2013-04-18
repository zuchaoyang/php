<?php

class CommentsAction extends SnsController {
    
    /**
     * 通过日志id获取日志的评论信息
     * 注明：动态在获取日志的评论信息的时候不需要分页处理，因此将动态获取和日志的评论列表是获取分开
     */
    public function getBlogCommentsForFeedAjax() {
        $blog_id = $this->objInput->getInt('blog_id');
        
        if(empty($blog_id)) {
            $this->ajaxReturn(null, '日志信息不存在!', -1, 'json');
        }
        
        import('@.Control.Api.BlogApi');
        $BlogApi = new BlogApi();
        
        $comment_list = $BlogApi->getBlogCommentsByBlogId($blog_id, "level='1'", 0, 10);
        
        $comment_list = $this->appendCommentsAccess($comment_list);
        
        $this->ajaxReturn($comment_list, '获取成功!', 1, 'json');
    }
    
    /**
     * 发表日志的评论信息
     */
    public function publishBlogCommentAjax() {
        $blog_id = $this->objInput->postInt('blog_id');
        $up_id   = $this->objInput->postInt('up_id');
        $content = $this->objInput->postStr('content');
        
        if(empty($content)) {
            $this->ajaxReturn(null, '评论信息不能为空!', -1, 'json');
        }
        
        import('@.Common_wmw.WmwString');
        if(WmwString::mbstrlen($content, 1) > 140) {
            $this->ajaxReturn(null, '评论信息不能超过140字!', -1, 'json');
        }
        
        //查找日志信息是否存在
        $mBlog = ClsFactory::Create('Model.Blog.mBlog');
        $blog_list = $mBlog->getBlogById($blog_id);
        $blog_info = & $blog_list[$blog_id];
        if(empty($blog_info)) {
            $this->ajaxReturn(null, '日志信息不存在或已删除!', -1, 'json');
        }
        
        $comment_datas = array(
            'blog_id' => $blog_id,
            'content' => $content,
            'up_id'	  => $up_id,
            'client_account' => $this->user['client_account'],
            'add_time' => time(),
            'level' => !empty($up_id) ? 2 : 1,
        );
        
        import('@.Control.Api.BlogApi');
        $BlogApi = new BlogApi();
        
        $comment_id = $BlogApi->addBlogComments($comment_datas);
        if(empty($comment_id)) {
            $this->ajaxReturn(null, '日志评论发表失败!', -1, 'json');
        }
        
        //获取日志的评论信息
        $comment_list = $BlogApi->getBlogCommentsById($comment_id);
        //追加日志评论的权限信息
        $comment_list = $this->appendCommentsAccess($comment_list);
        
        $comment_info = & $comment_list[$comment_id];
        
        $this->ajaxReturn($comment_info, '评论发表成功!', 1, 'json');
    }
    
    /**
     * 删除日志的评论信息
     */
    public function deleteBlogCommentsAjax() {
        $comment_id = $this->objInput->getInt('comment_id');
        
        if(empty($comment_id)) {
            $this->ajaxReturn(null, '日志的评论信息不存在!', -1, 'json');
        }
        
        $mBlogComments = ClsFactory::Create('Model.Blog.mBlogComments');
        $comment_list = $mBlogComments->getBlogCommentsById($comment_id);
        $comment_info = & $comment_list[$comment_id];
        
        if(empty($comment_info)) {
            $this->ajaxReturn(null, '评论信息不存在或已删除!', -1 , 'json');
        }
        
        //判断评论的删除权限: 评论的发表人获取时日志的发表人
        if($comment_info['client_account'] != $this->user['client_account']) {
            $this->ajaxReturn(null, '您暂时没有权限删除该评论信息!', -1, 'json');
        }
        
        import('@.Control.Api.BlogApi');
        $BlogApi = new BlogApi();
        if(!$BlogApi->delBlogComments($comment_id)) {
            $this->ajaxReturn(null, '评论信息删除失败!', -1 , 'json');
        }
        
        $this->ajaxReturn(null, '评论删除成功!', 1, 'json');
    }
    
    /**
     * 追加评论对应的权限信息
     * @param $comment_list
     */
    private function appendCommentsAccess($comment_list) {
        if(empty($comment_list)) {
            return false;
        } else if(!is_array($comment_list)) {
            return $comment_list;
        }
        
        foreach($comment_list as $comment_id=>$comment) {
            if($comment['client_account'] == $this->user['client_account']) {
                $comment['cal_del'] = true;
            }
            
            if(!empty($comment['child_items'])) {
                foreach($comment['child_items'] as $child_comment_id=>$child_comment) {
                    if($child_comment['client_account'] == $this->user['client_account']) {
                        $child_comment['can_del'] = true;
                    }
                    $comment['child_items'][$child_comment_id] = $child_comment;
                }
            }
            
            $comment_list[$comment_id] = $comment;
        }
        
        return $comment_list;
    }
    
}