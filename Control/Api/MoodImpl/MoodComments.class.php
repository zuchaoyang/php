<?php
/**
 * api中实现的操作不能涉及到权限的控制
 * @author Administrator
 *
 */
class MoodComments {
    
    /**
     * 通过主键获取说说评论的相关信息
     * @param $comment_ids
     */
    public function getMoodCommentsById($comment_ids) {
        if(empty($comment_ids)) {
            return false;
        }
        
        $mMoodComments = ClsFactory::Create('Model.Mood.mMoodComments');
        $comment_list = $mMoodComments->getMoodCommentsById($comment_ids);
        
        return $this->parseCommentList($comment_list);
    }
    
    /**
     * 获取说说的评论信息,只处理单个说说的评论获取
     */
    public function getMoodCommentsByMoodId($mood_id, $where_appends, $offset = 0, $limit = 10) {
        if(empty($mood_id)) {
            return false;
        }
        
        $mood_id = is_array($mood_id) ? array_shift($mood_id) : $mood_id;
        
        $mMoodComments = ClsFactory::Create('Model.Mood.mMoodComments');
        $comment_arr = $mMoodComments->getMoodCommentsByMoodId($mood_id, $where_appends, $offset, $limit);
        $comment_list = & $comment_arr[$mood_id];
        
        return $this->parseCommentList($comment_list);
    }
    
    /**
     * 发布说说的评论信息
     */
    public function addMoodComments($comment_datas) {
        if(empty($comment_datas) || !is_array($comment_datas)) {
            return false;
        }
        
        $mMoodComments = ClsFactory::Create('Model.Mood.mMoodComments');
        $comment_id = $mMoodComments->addMoodComments($comment_datas, true);
        if(empty($comment_id)) {
            return false;
        }
        
        //更说说的评论数
        $mood_datas = array(
            'comments' => '%comments+1%'
        );
        $mMood = ClsFactory::Create('Model.Mood.mMood');
        $mMood->modifyMood($mood_datas, $comment_datas['mood_id']);
        
        //获取发表成功后的说说评论信息
        return $comment_id;
    }
    
    /**
     * 删除说说评论信息
     */
    public function delMoodComments($comment_id) {
        if(empty($comment_id)) {
            return false;
        }
        
        $mMoodComments = ClsFactory::Create('Model.Mood.mMoodComments');
        $comment_list = $mMoodComments->getMoodCommentsById($comment_id);
        $comment = & $comment_list[$comment_id];
        //删除评论信息
        if(empty($comment) || !$mMoodComments->delMoodComments($comment_id)) {
            return false;
        }
        
        $delete_comment_nums = 1;
        //如果是一级评论，删除评论下面的2级评论
        $child_comment_arr = $mMoodComments->getMoodCommentsByUpid($comment_id);
        $child_comment_list = & $child_comment_arr[$comment_id];
        if(!empty($child_comment_list)) {
            foreach($child_comment_list as $child_comment_id=>$child_comment) {
                $mMoodComments->delMoodComments($child_comment_id);
            }
            $delete_comment_nums += count($child_comment_list);
        }
        
        $mood_id = $comment['mood_id'];
        //更说说的评论数
        $mood_datas = array(
            'comments' => "%comments-$delete_comment_nums%"
        );
        $mMood = ClsFactory::Create('Model.Mood.mMood');
        $mMood->modifyMood($mood_datas, $mood_id);
        
        return true;
    }
    
    /**
     * 处理评论列表信息
     * @param $comment_list
     */
    private function parseCommentList($comment_list) {
        if(empty($comment_list)) {
            return false;
        }
        
        $comment_list = $this->appendChildComments($comment_list);
        $comment_list = $this->appendCommentsUserInfo($comment_list);
        $comment_list = $this->formatComments($comment_list);
        
        return $comment_list;
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
     * 转换评论的相关信息
     * @param $comment
     */
    private function formatComments($comment_list) {
        if(empty($comment_list)) {
            return false;
        }
        
        import('@.Common_wmw.WmwFace');
        import('@.Common_wmw.Date');
        
        foreach($comment_list as $comment_id=>$comment) {
            if(!empty($comment['add_time'])) {
                $comment['add_time_format'] = Date::timestamp($comment['add_time']);
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