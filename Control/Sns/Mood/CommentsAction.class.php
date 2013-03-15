<?php

class CommentsAction extends SnsController {
    
    /**
     * 获取说说的评论信息
     */
    public function getMoodCommentsForFeedAjax() {
        $mood_id        = $this->objInput->getInt('mood_id');
        
        import('@.Control.Api.MoodApi');
        $MoodApi = new MoodApi();
        $comment_list = $MoodApi->getMoodCommentsByMoodId($mood_id, "level='1'", 0, 10);
        
        if(empty($comment_list)) {
            $this->ajaxReturn(null, '没有更多评论信息!', -1, 'json');
        }
        
        $this->ajaxReturn($comment_list, '获取成功!', 1, 'json');
    }
    
    /**
     * 获取说说的评论信息
     * 注明:
     * 1. 暂时没有考虑2级评论过多的情况
     */
    public function getMoodCommentsAjax() {
        $page           = $this->objInput->getInt('page');
        $mood_id        = $this->objInput->getInt('mood_id');
        $max_comment_id = $this->objInput->getInt('max_comment_id');
        
        $max_comment_id = $max_comment_id > 0 ? $max_comment_id : 0;
        $page = max(1, $page);
        
        $perpage = 1;
        $offset = ($page - 1) * $perpage;
        $where_appends = array();
        $where_appends[] = "level='1'";
        if($max_comment_id > 0) {
            $where_appends[] = "comment_id<='$max_comment_id'";
        }
        
        import('@.Control.Api.MoodApi');
        $MoodApi = new MoodApi();
        
        $comment_list = $MoodApi->getMoodCommentsByMoodId($mood_id, $where_appends, $offset, $perpage + 1);
        
        if(empty($comment_list)) {
            $this->ajaxReturn(null, '没有更多评论信息!', -1, 'json');
        }
        
        //判断是否存在下一页
        $has_next_page = false;
        if(count($comment_list) > $perpage) {
            $has_next_page = true;
            $comment_list = array_slice($comment_list, 0, $perpage, true);
        }
        //获取最大的comment_id值，注意以第一次获取数据时的最大comment_id为基准
        $max_comment_id = !empty($max_comment_id) ? $max_comment_id : max((array)array_keys($comment_list));
        
        $ret_datas = array(
            'has_next_page' => $has_next_page,
            'max_comment_id' => $max_comment_id,
            'comment_list' => $comment_list  
        );
        
        $this->ajaxReturn($ret_datas, '获取成功!', 1, 'json');
    }
    
    /**
     * 发布说说的评论信息
     */
    public function publishMoodCommentsAjax() {
        $mood_id = $this->objInput->postInt('mood_id');
        $up_id   = $this->objInput->postInt('up_id');
        $content = $this->objInput->postStr('content');
        
        if(empty($content)) {
            $this->ajaxReturn(null, '评论内容不能为空!', -1, 'json');
        } else if(empty($mood_id)) {
            $this->ajaxReturn(null, '说说信息已删除或不存在!', -1, 'json');
        }
        
        $comment_datas = array(
            'mood_id' => $mood_id,
            'up_id' => $up_id,
            'content' => $content,
            'client_account' => $this->user['client_account'],
            'add_time' => time(),
            'level' => empty($up_id) ?  1 : 2,
        );
        
        import('@.Control.Api.MoodApi');
        $MoodApi = new MoodApi();
        
        $comment_id = $MoodApi->addMoodComments($comment_datas);
        
        if(empty($comment_id)) {
            $this->ajaxReturn(null, '评论信息添加失败!', -1, 'json');
        }
        
        $comment_list = $MoodApi->getMoodCommentsById($comment_id);
        $comment_info = & $comment_list[$comment_id];
        
        $this->ajaxReturn($comment_info, '评论发布成功!', 1, 'json');
    }
    
    /**
     * 删除说说评论信息
     */
    public function deleteMoodCommentsAjax() {
        $comment_id = $this->objInput->getInt('comment_id');
        
        $mMoodComments = ClsFactory::Create('Model.Mood.mMoodComments');
        $comment_list = $mMoodComments->getMoodCommentsById($comment_id);
        $comment = & $comment_list[$comment_id];
        if(empty($comment)) {
            $this->ajaxReturn(null, '评论信息不存在!', -1, 'json');
        }
        
        //权限判断
        if($comment['client_account'] != $this->user['client_account']) {
            $this->ajaxReturn(null, '您暂时没有权限删除该评论信息!', -1, 'json');
        }
        
        import('@.Control.Api.MoodApi');
        $MoodApi = new MoodApi();
        //删除评论信息
        if(!$MoodApi->delMoodComments($comment_id)) {
            $this->ajaxReturn(null, '系统繁忙,删除失败!', -1, 'json');
        }
        
        $this->ajaxReturn(null, '删除成功!', 1, 'json');
    }
}