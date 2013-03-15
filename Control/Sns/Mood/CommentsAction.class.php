<?php

class CommentsAction extends SnsController {
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
        
        $mMoodComments = ClsFactory::Create('Model.Mood.mMoodComments');
        $comment_arr = $mMoodComments->getMoodCommentsByMoodId($mood_id, $where_appends, $offset, $perpage + 1);
        $comment_list = & $comment_arr[$mood_id];
        
        if(empty($comment_list)) {
            $this->ajaxReturn(null, '没有更多评论信息!', -1, 'json');
        }
        
        $comment_list = $this->appendChildComments($comment_list);
        $comment_list = $this->appendCommentsUserInfo($comment_list);
        $comment_list = $this->appendCommentsAccess($comment_list);
        $comment_list = $this->formatComments($comment_list);
        
        //判断是否存在下一页
        $has_next_page = count($comment_list) > $perpage ? true : false;
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
        
        $mMoodComments = ClsFactory::Create('Model.Mood.mMoodComments');
        $comment_id = $mMoodComments->addMoodComments($comment_datas, true);
        if(empty($comment_id)) {
            $this->ajaxReturn(null, '评论信息添加失败!', -1, 'json');
        }
        
        //更说说的评论数
        $mood_datas = array(
            'comments' => '%comments+1%'
        );
        $mMood = ClsFactory::Create('Model.Mood.mMood');
        $mMood->modifyMood($mood_datas, $mood_id);
        
        //获取发表成功后的说说评论信息
        $comment_list = $mMoodComments->getMoodCommentsById($comment_id);
        $comment_list = $this->appendCommentsUserInfo($comment_list);
        $comment_list = $this->appendCommentsAccess($comment_list);
        $comment_list = $this->formatComments($comment_list);
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
        
        //删除评论信息
        if(!$mMoodComments->delMoodComments($comment_id)) {
            $this->ajaxReturn(null, '系统繁忙，删除失败!', -1, 'json');
        }
        
        //如果是一级评论，删除评论下面的2级评论
        $child_comment_arr = $mMoodComments->getMoodCommentsByUpid($comment_id);
        $child_comment_list = & $child_comment_arr[$comment_id];
        if(!empty($child_comment_list)) {
            foreach($child_comment_list as $child_comment_id=>$child_comment) {
                $mMoodComments->delMoodComments($child_comment_id);
            }
            $nums = count($child_comment_list) + 1;
        } else {
            $nums = 1;
        }
        
        $mood_id = $comment['mood_id'];
        //更说说的评论数
        $mood_datas = array(
            'comments' => "%comments-$nums%"
        );
        $mMood = ClsFactory::Create('Model.Mood.mMood');
        $mMood->modifyMood($mood_datas, $mood_id);
        
        $this->ajaxReturn(null, '删除成功!', 1, 'json');
    }
    
    /**
     * 追加评论的2级评论信息
     * @param $comment_list
     */
    private function appendChildComments($comment_list) {
        if(empty($comment_list)) {
            return false;
        }
        
        $up_ids = (array)array_keys($comment_list);
        $mMoodComments = ClsFactory::Create('Model.Mood.mMoodComments');
        $child_comment_arr = $mMoodComments->getMoodCommentsByUpid($up_ids);
        if(!empty($child_comment_arr)) {
            foreach($comment_list as $comment_id=>$comment) {
                if(isset($child_comment_arr[$comment_id])) {
                    $comment['child_items'] = $child_comment_arr[$comment_id];
                }
                $comment_list[$comment_id] = $comment;
            }
        }
        
        return $comment_list;
    }
    
    /**
     * 追加评论的用户信息
     * @param $comment_list
     */
    private function appendCommentsUserInfo($comment_list) {
        if(empty($comment_list)) {
            return false;
        }
        
        //提取用户的账号信息
        $uids = array();
        foreach($comment_list as $comment) {
            $uids[$comment['client_account']] = $comment['client_account'];
            if(!empty($comment['child_items'])) {
                foreach($comment['child_items'] as $child_comment) {
                    $uids[$comment['client_account']] = $child_comment['client_account'];
                }
            }
        }
        //追加用户信息
        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getUserBaseByUid($uids);
        if(!empty($user_list)) {
            //精简用户信息相关的字段
            foreach($user_list as $uid=>$user) {
                $user_list[$uid] = array(
                    'client_name' => $user['client_name'],
                    'client_headimg_url' => $user['client_headimg_url'],
                );
            }
            
            foreach($comment_list as $comment_id=>$comment) {
                $comment['user_info'] = $user_list[$comment['client_account']];
                if(!empty($comment['child_items'])) {
                    foreach($comment['child_items'] as $child_comment_id=>$child_comment) {
                        $child_comment['user_info'] = $user_list[$child_comment['client_account']];
                        $comment['child_items'][$child_comment_id] = $child_comment;
                    }
                }
                $comment_list[$comment_id] = $comment;
            }
        }
        
        return $comment_list;
    }
    
    /**
     * 追加评论的权限信息
     * @param $comment_list
     */
    private function appendCommentsAccess($comment_list) {
        if(empty($comment_list)) {
            return false;
        }
        
        $current_uid = $this->user['client_account'];
        foreach($comment_list as $comment_id=>$comment) {
            $comment['can_del'] = ($comment['client_account'] == $current_uid) ? true : false;
            if(!empty($comment['child_items'])) {
                foreach($comment['child_items'] as $child_comment_id=>$child_comment) {
                    $child_comment['can_del'] = ($child_comment['client_account'] == $current_uid) ? true : false;
                    $comment['child_items'][$child_comment_id] = $child_comment;
                }
            }
            $comment_list[$comment_id] = $comment;
        }
        
        return $comment_list;
    }
    
    /**
     * 转换评论的相关信息
     * @param $comment
     */
    private function formatComments($comment_list) {
        if(empty($comment_list)) {
            return false;
        }
        
        import('@.Common_wmw.WmwFace');
        foreach($comment_list as $comment_id=>$comment) {
            if(!empty($comment['add_time'])) {
                $comment['add_time_format'] = date('Y.m.d H:i', $comment['add_time']);
            }
            $comment['content'] = WmwFace::parseFace($comment['content']);
            
            if(!empty($comment['child_items'])) {
                foreach($comment['child_items'] as $child_comment_id=>$child_comment) {
                    if(!empty($child_comment['add_time'])) {
                        $child_comment['add_time_format'] = date('Y.m.d H:i', $child_comment['add_time']);
                    }
                    $child_comment['content'] = WmwFace::parseFace($child_comment['content']);
                    
                    $comment['child_items'][$child_comment_id] = $child_comment;
                }
            }
            
            $comment_list[$comment_id] = $comment;
        }
        
        return $comment_list;
    }
    
}