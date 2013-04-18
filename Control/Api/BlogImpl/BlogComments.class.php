<?php

class BlogComments {
    
    /**
     * 通过主键获取评论的相关信息
     * @param $comment_ids
     */
    public function getBlogCommentsById($comment_ids) {
        if(empty($comment_ids)) {
            return false;
        }
        
        $mBlogComments = ClsFactory::Create("Model.Blog.mBlogComments");
        $comment_list = $mBlogComments->getBlogCommentsById($comment_ids);
        
        return $this->parseCommentList($comment_list);
    }
    
    /**
     * 通过日志id获取日志的评论信息
     * @param $blog_id
     * @param $where_appends
     * @param $offset
     * @param $limit
     */
    public function getBlogCommentsByBlogId($blog_id, $where_appends, $offset = 0, $limit = 10) {
        if(empty($blog_id)) {
            return false;
        }
        
        $mBlogComments = ClsFactory::Create("Model.Blog.mBlogComments");
        $comment_arr = $mBlogComments->getBlogCommentsByBlogId($blog_id, $where_appends, $offset, $limit);
        $comment_list = & $comment_arr[$blog_id];
        
        return $this->parseCommentList($comment_list);
    }
    
    /**
     * 添加日志的评论信息
     * @param $comment_datas
     */
    public function addBlogComments($comment_datas) {
        if(empty($comment_datas) || !is_array($comment_datas)) {
            return false;
        }
        
        $mBlogComments = ClsFactory::Create("Model.Blog.mBlogComments");
        $comment_id = $mBlogComments->addComment($comment_datas, true);
        
        if(empty($comment_id)) {
            return false;
        }
        
        //更新日志对应的评论数
        $blog_id = $comment_datas['blog_id'];
        $mBlog = ClsFactory::Create("Model.Blog.mBlog");
        $dataarr = array(
            'comments' => '%comments+1%'
        );
        $mBlog->modifyBlog($dataarr, $blog_id);
        
        return $comment_id;
    }
    
    /**
     * 删除日志的评论信息
     * @param $comment_id
     */
    public function delBlogComments($comment_id) {
        if(empty($comment_id)) {
            return false;
        }
        
        $mBlogComments = ClsFactory::Create("Model.Blog.mBlogComments");
        $comment_list = $mBlogComments->getBlogCommentsById($comment_id);
        $comment_info = & $comment_list[$comment_id];
        
        //评论信息不存在或者删除评论信息本身失败
        if(empty($comment_info) || !$mBlogComments->delByCommentId($comment_id)) {
            return false;
        }
        
        $delete_comment_nums = 1;
        //如果是一级评论，删除评论的二级评论信息
        if($comment_info['level'] == 1) {
            $child_comment_arr = $mBlogComments->getBlogCommentsByUpId($comment_id);
            $child_comment_list = & $child_comment_arr[$comment_id];
            //由于一级评论被删除，二级评论无法找到，因此尝试删除时都认为二级的删除是成功的
            foreach($child_comment_list as $child_comment_id=>$child_comment) {
                $mBlogComments->delByCommentId($child_comment_id);
                $delete_comment_nums++;
            }
        }
        
        //修改日志的评论数
        $dataarr = array(
            'comments' => "%comments-$delete_comment_nums"
        );
        $mBlog = ClsFactory::Create("Model.Blog.mBlog");
        $mBlog->modifyBlog($dataarr, $comment_info['blog_id']);
        
        return true;
    }
    
    /**
     * 解析日志的评论列表
     * @param $comment_list
     */
    private function parseCommentList($comment_list) {
        if(empty($comment_list)) {
            return false;
        }
        
        $comment_list = $this->appendChildComments($comment_list);
        $comment_list = $this->formatComments($comment_list);
        
        return $comment_list;
    }
    
    /**
     * 获取评论的孩子节点
     * 注明：暂时不考虑2级评论过多的情况
     * @param $comment_list
     */
    private function appendChildComments($comment_list) {
        if(empty($comment_list)) {
            return false;
        }
        
        $each_limit = 5;
        $up_ids = array_keys($comment_list);
        
        $mBlogComments = ClsFactory::Create('Model.Blog.mBlogComments');
        
        $stat_list = $mBlogComments->getBlogCommentsChildrenStatByUpid($up_ids);
        $child_comment_arr = $mBlogComments->getBlogCommentsChildrenByUpid($up_ids, $each_limit);
        
        foreach($comment_list as $comment_id=>$comment) {
            if(isset($child_comment_arr[$comment_id])) {
                $comment['child_items'] = (array)$child_comment_arr[$comment_id];
                $remain_nums = $stat_list[$comment_id] - $each_limit;
                if($remain_nums > 0) {
                    $comment['remain_nums'] = $remain_nums;
                }
            } else {
                $comment['child_items'] = array();
            }
            $comment_list[$comment_id] = $comment;
        }
        
        return $comment_list;
    }
    
    /**
     * 格式化评论信息
     * 1. 添加评论的用户信息
     * 2. 格式化评论的时间
     * 3. 解析评论的表情信息
     * @param  $comment_list
     */
    private function formatComments($comment_list) {
        if(empty($comment_list)) {
            return false;
        }
        
        import('@.Common_wmw.WmwFace');
        import('@.Common_wmw.Date');
        
        //获取评论相关的用户信息
        $uids = array();
        foreach($comment_list as $comment_id => $comment) {
            $uids[$comment['client_account']] = $comment['client_account'];
            if(isset($comment['child_items'])) {
                foreach($comment['child_items'] as $child_comment_id=>$child_comment) {
                    $uids[$child_comment['client_account']] = $child_comment['client_account'];
                }
            }
        }
        $mUser = ClsFactory::Create("Model.mUser");
        $user_list = $mUser->getUserBaseByUid($uids);
        if(!empty($user_list)) {
            foreach($user_list as $uid=>$user) {
                $user_list[$uid] = array(
                    'client_name'        => $user['client_name'],
                    'client_headimg_url' => $user['client_headimg_url'],
                );
            }
        }
        
        foreach($comment_list as $comment_id=>$comment) {
            $uid = $comment['client_account'];
            if(isset($user_list[$uid])) {
                $comment['user_info'] = $user_list[$uid];
                $comment = array_merge($comment, (array)$user_list[$uid]);
            }
            
            //转换时间格式和解析表情信息
            $comment['add_time_format'] = Date::timestamp($comment['add_time']);
            $comment['content'] = WmwFace::parseFace($comment['content']);
            
            if(!empty($comment['child_items'])) {
                foreach($comment['child_items'] as $child_comment_id=>$child_comment) {
                    $child_uid = $child_comment['client_account'];
                    if(isset($user_list[$child_uid])) {
                        $child_comment['user_info'] = $user_list[$child_uid];
                        $child_comment = array_merge($child_comment, (array)$user_list[$child_uid]);
                    }
                    $child_comment['add_time_format'] = Date::timestamp($child_comment['add_time']);
                    $child_comment['content'] = WmwFace::parseFace($child_comment['content']);
                    
                    $comment['child_items'][$child_comment_id] = $child_comment;
                }
            }
            $comment_list[$comment_id] = $comment;
        }
        
        return $comment_list;
    }
}