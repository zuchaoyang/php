<?php

define('FEED_NUMS', 5);
define('COMMENT_NUMS', 5);

class ListAction extends SnsController {
    
    /**
     * 获取评论的列表信息一级二级评论的条数统计
     */
//    public function getFeedCommentsAjax() {
//        $feed_id = $this->objInput->getInt('feed_id');
//        $level   = $this->objInput->getInt('level');
//        
//        $level = in_array($level, array(1, 2)) ? $level : 1;
//        
//        $where_appends = array(
//            "level='$level'"
//        );
//        
//        $feed_id = 727;
//        
//        //获取动态的评论信息
//        $mFeedComments = ClsFactory::Create('Model.Feed.mFeedComments');
//        $feed_comment_arr = $mFeedComments->getFeedCommentsByFeedId($feed_id, $where_appends, 0, COMMENT_NUMS);
//        $feed_comment_list = & $feed_comment_arr[$feed_id];
//        
//        //获取对应的动态信息的全部的动态信息的统计值
//        $comment_stat_arr = $mFeedComments->getFeedCommentsStatByFeedId($feed_id);
//        $comment_stat_list = & $comment_stat_arr[$feed_id];
//        
//        //获取评论的二级评论数
//        foreach($feed_comment_list as $comment_id=>$comment) {
//            $child_nums = isset($comment_stat_list[$comment_id]) ? $comment_stat_list[$comment_id] : 0;
//            $comment['child_nums'] = $child_nums;
//            
//            $feed_comment_list[$comment_id] = $comment;
//        }
//        
//        $feed_comment_list = $this->appendFeedCommentsChildList($feed_comment_list);
//        $feed_comment_list = $this->parseFeedComments($feed_comment_list);
//        $feed_comment_list = $this->appendFeedCommentsUserInfo($feed_comment_list);
//        
//        $this->ajaxReturn($feed_comment_list, '获取成功', 1, 'json');
//    }
    
    /**
     * 发布动态的评论信息,动态的评论没有权限的限制
     */
//    public function publishCommentAjax() {
//        $feed_id = $this->objInput->postInt('feed_id');
//        $up_id   = $this->objInput->postInt('up_id');
//        $content = $this->objInput->postStr('content');
//        
//        $up_id = $up_id > 0 ? $up_id : 0;
//        
//        if(empty($feed_id)) {
//            $this->ajaxReturn(null, '动态信息不存在!', -1, 'json');
//        } else if(empty($content)) {
//            $this->ajaxReturn(null, '内容不能为空!', -1, 'json');
//        }
//        
//        //检验参数的依赖关系
//        $mFeed = ClsFactory::Create('Model.Feed.mFeed');
//        $feed_list = $mFeed->getFeedById($feed_id);
//        if(empty($feed_list)) {
//            $this->ajaxReturn(null, '动态信息不存在!', -1, 'json');
//        }
//        
//        $comment_datas = array(
//            'feed_id'        => $feed_id,
//            'up_id'	         => $up_id,
//            'content'        => $content,
//            'client_account' => $this->user['client_account'],
//            'add_time'       => time(),
//            'level'          => empty($up_id) ? 1 : 2,
//            'upd_time'       => time(), 
//        );
//        $mFeedComments = ClsFactory::Create('Model.Feed.mFeedComments');
//        $comment_id = $mFeedComments->addFeedComments($comment_datas, true);
//        if(empty($comment_id)) {
//            $this->ajaxReturn(null, '系统繁忙，发表失败!', -1, 'json');
//        }
//        
//        $comment_datas['comment_id'] = $comment_id;
//        $comment_list = array(
//            $comment_id => $comment_datas,
//        );
//        $comment_list = $this->appendFeedCommentsUserInfo($comment_list);
//        $comment_list = $this->parseFeedComments($comment_list);
//        
//        $comment_datas = & $comment_list[$comment_id];
//        
//        $this->ajaxReturn($comment_datas, '发表成功!', 1, 'json');
//    }
    
    /**
     * 删除动态的评论信息
     */
//    public function deleteCommentAjax() {
//        $comment_id = $this->objInput->getInt('comment_id');
//        
//        if(empty($comment_id)) {
//            $this->ajaxReturn(null, '评论信息不存在!', -1, 'json');
//        }
//        
//        //判断用户的删除权限
//        $mFeedComments = ClsFactory::Create('Model.Feed.mFeedComments');
//        $comment_list = $mFeedComments->getFeedCommentsById($comment_id);
//        $comment_info = & $comment_list[$comment_id];
//        if(empty($comment_info)) {
//            $this->ajaxReturn(null, '评论信息不存在!', -1, 'json');
//        }
//        
//        $feed_id = $comment_info['feed_id'];
//        $mFeed = ClsFactory::Create('Model.Feed.mFeed');
//        $feed_list = $mFeed->getFeedById($feed_id);
//        $feed_info = & $feed_list[$feed_id];
//        if($feed_info['add_account'] != $this->user['client_account']) {
//            $this->ajaxReturn(null, '您暂时没有权限删除该评论!', -1, 'json');
//        }
//        
//        if(!$mFeedComments->delFeedComments($comment_id)) {
//            $this->ajaxReturn(null, '系统繁忙，删除失败!', -1, 'json');
//        }
//        
//        //删除评论的子评论信息
//        if($comment_info['level'] == 1) {
//            $sub_comment_list = $mFeedComments->getFeedCommentsByUpid($comment_id);
//            foreach((array)$sub_comment_list as $sub_comment_id=>$sub_comment) {
//                $mFeedComments->delFeedComments($sub_comment_id);
//            }
//        }
//        
//        $this->ajaxReturn(null, '评论删除成功!', 1, 'json');
//    }
    
    /**
     * 删除动态信息
     */
    public function deleteFeedAjax() {
        $feed_id = $this->objInput->getInt('feed_id');
        
        if(empty($feed_id)) {
            $this->showError(null, '动态信息不存在!', -1, 'json');
        }
        
        $mFeed = ClsFactory::Create('Model.Feed.mFeed');
        $feed_list = $mFeed->getFeedById($feed_id);
        $feed_info = & $feed_list[$feed_id];
        if(empty($feed_info) || $feed_info['add_account'] != $this->user['client_account']) {
            $this->ajaxReturn(null, '您暂时没有权限删除该评论!', -1, 'json');
        }
        
        if(!$mFeed->delFeed($feed_id)) {
            $this->ajaxReturn(null, '系统繁忙，删除失败!', -1, 'json');
        }
        
        $this->ajaxReturn(null, '动态删除成功!', 1, 'json');
    }
    
    /**
     * 获取动态评论统计信息
     * @param $feed_comment_list
     */
//    private function appendFeedCommentsChildList($feed_comment_list) {
//        if(empty($feed_comment_list)) {
//            return false;
//        }
//        
//        //根据一级动态的评论数的多少对查询进行分组处理
//        $common_groups = $special_groups = array();
//        foreach($feed_comment_list as $comment_id=>$comment) {
//            if($comment['child_nums'] > 200) {
//                $special_groups[] = $comment_id;
//            } else {
//                $common_groups[] = $comment_id;
//            }
//        }
//        
//        $total_comment_arr = $child_comment_arr = array();
//        
//        $mFeedComments = ClsFactory::Create('Model.Feed.mFeedComments');
//        if(!empty($common_groups)) {
//            $child_comment_arr[] = $mFeedComments->getFeedCommentsByUpid($common_groups);
//        }
//        foreach((array)$special_groups as $up_id) {
//            $child_comment_arr[] = $mFeedComments->getFeedCommentsByUpid($up_id, null, 0, 10);
//        }
//        foreach((array)$child_comment_arr as $child_comment_list) {
//            foreach($child_comment_list as $up_id=>$list) {
//                $total_comment_arr[$up_id] = $list;
//            }
//        }
//        
//        //将二级评论进行合并
//        foreach($feed_comment_list as $comment_id=>$comment) {
//            $remain_nums = 0;
//            $child_comment_list = array();
//            if(isset($total_comment_arr[$comment_id])) {
//                $child_comment_list = $total_comment_arr[$comment_id];
//                if(count($child_comment_list) > 10) {
//                    $child_comment_list = array_slice($child_comment_list, 0, 10, true);
//                    $remain_nums = count($child_comment_list) - 10;
//                }
//            }
//            $comment['remain_nums'] = $remain_nums;
//            $comment['child_list'] = $child_comment_list;
//            
//            $feed_comment_list[$comment_id] = $comment;
//        }
//        
//        return $feed_comment_list;
//    }
    
    /**
     * 转换动态评论的相关信息
     * @param $feed_comment_list
     */
//    private function parseFeedComments($feed_comment_list) {
//        if(empty($feed_comment_list)) {
//            return false;
//        }
//        
//        //转换动态评论的时间格式
//        import('@.Common_wmw.Date');
//        import('@.Common_wmw.WmwFace');
//        
//        $dateObj = new Date();
//        foreach($feed_comment_list as $comment_id=>$comment) {
//            $comment['add_time_diff'] = $dateObj->timeDiff(intval($comment['add_time']) + 1);
//            //处理孩子节点相关的信息
//            foreach((array)$comment['child_list'] as $sub_comment_id=>$sub_comment) {
//                $sub_comment['add_time_diff'] = $dateObj->timeDiff(intval($sub_comment['add_time']));
//                $sub_comment['content'] = WmwFace::parseFace($sub_comment['content']);
//                $comment['child_list'][$sub_comment_id] = $sub_comment;
//            }
//            $comment['content'] = WmwFace::parseFace($comment['content']);
//            
//            $feed_comment_list[$comment_id] = $comment;
//        }
//        
//        return $feed_comment_list;
//    }
    
    /**
     * 追加用户的基本信息
     * @param $feed_comment_list
     */
//    private function appendFeedCommentsUserInfo($feed_comment_list) {
//        if(empty($feed_comment_list)) {
//            return false;
//        }
//        
//        //提取相应的账号信息
//        $uids = array();
//        foreach($feed_comment_list as $comment_id=>$comment) {
//            $uids[$comment['client_account']] = $comment['client_account'];
//            if(!empty($comment['child_list'])) {
//                foreach($comment['child_list'] as $sub_comment_id=>$sub_comment) {
//                    $uids[$sub_comment['client_account']] = $sub_comment['client_account'];
//                }
//            }
//        }
//        
//        //获取用户的基本信息
//        $mUser = ClsFactory::Create('Model.mUser');
//        $user_list = $mUser->getClientAccountById($uids);
//        foreach((array)$user_list as $uid=>$user) {
//            $user_list[$uid] = array(
//                'client_name'        => $user['client_name'],
//                'client_headimg_url' => $user['client_headimg_url'],
//            );
//        }
//        
//        //将用户的账号信息合并到评论的基本信息中
//        foreach($feed_comment_list as $comment_id=>$comment) {
//            if(isset($user_list[$comment['client_account']])) {
//                $comment = array_merge($comment, $user_list[$comment['client_account']]);
//            }
//            if(!empty($comment['child_list'])) {
//                foreach($comment['child_list'] as $sub_comment_id=>$sub_comment) {
//                    if(isset($user_list[$sub_comment['client_account']])) {
//                        $sub_comment = array_merge($sub_comment, $user_list[$sub_comment['client_account']]);
//                    }
//                    $comment['child_list'][$sub_comment_id] = $sub_comment;
//                }
//            }
//            $feed_comment_list[$comment_id] = $comment;
//        }
//        
//        return $feed_comment_list;
//    }
    
    /**
     * 添加动态的用户信息
     * @param $feed_list
     */
    private function appendFeedUserInfo($feed_list) {
        if(empty($feed_list)) {
            return false;
        }
        
        $uids = array();
        foreach($feed_list as $feed) {
            $uids[$feed['add_account']] = $feed['add_account'];
        }
        
        //获取用户的基本信息
        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getClientAccountById($uids);
        foreach((array)$user_list as $uid=>$user) {
            $user_list[$uid] = array(
                'client_name'        => $user['client_name'],
                'client_headimg_url' => $user['client_headimg_url'],
            );
        }
        
        foreach($feed_list as $feed_id=>$feed) {
            $add_account = $feed['add_account'];
            if(isset($user_list[$add_account])) {
                 $feed = array_merge($feed, (array)$user_list[$add_account]);
            }
            $feed_list[$feed_id] = $feed;
        }
        
        return $feed_list;
    }
    
    //todolist 需要完善动态的内容链接部分
    /**
     * 解析动态信息
     * @param $feed_list
     */
    private function parseFeed($feed_list) {
        if(empty($feed_list)) {
            return false;
        }
        
        import('@.Common_wmw.WmwFace');
        
        foreach($feed_list as $feed_id=>$feed) {
            $feed['add_time_format'] = date('Y.m.d', $feed['add_time']);
            
            //转换动态的资源链接
            $feed_type = intval($feed['feed_type']);
            if($feed_type == FEED_ALBUM) {
                $feed['src_url'] = "/Sns/Album";
            } else if($feed_type == FEED_BLOG) {
                $feed['src_url'] = "/Sns/Blog/";
            } else if($feed_type == FEED_MOOD) {
                $feed['src_url'] = "/Sns/";
            }
            
            $feed['feed_content'] = WmwFace::parseFace($feed['feed_content']);
            
            $feed_list[$feed_id] = $feed;
        }
        
        return $feed_list;
    }
    
    /**
     * 获取动态的统计信息
     * @param $feed_list
     * todolis
     */
    private function appendFeedStat($feed_list) {
        if(empty($feed_list)) {
            return false;
        }
        
        return $feed_list;
        
        //统计动态的评论数
        $where_appends = array(
            "level='1'"
        );
        $feed_ids = array_keys($feed_list);
        $mFeedComments = ClsFactory::Create('Model.Feed.mFeedComments');
        $stat_list = $mFeedComments->getFeedCommentsStatByFeedId($feed_ids, $where_appends);
        foreach($feed_list as $feed_id=>$feed) {
            if(isset($stat_list[$feed_id])) {
                $feed['comment_nums'] = $stat_list[$feed_id][0];
            } else {
                $feed['comment_nums'] = 0;
            }
            $feed_list[$feed_id] = $feed;
        }
        
        //todolist
        //统计动态的分享次数
        
        return $feed_list;
    }
    
    /**
     * 获取用户的全部动态信息
     */
    public function getUserAllFeedAjax() {
        import('@.Control.Api.FeedApi');
        $FeedApi = new FeedApi();
        $feed_list = $FeedApi->getUserAllFeed(11070004, 0, FEED_NUMS);
        
        $feed_list = $this->appendFeedUserInfo($feed_list);
        $feed_list = $this->appendFeedStat($feed_list);
        $feed_list = $this->parseFeed($feed_list);
        
        $this->ajaxReturn($feed_list, 'success',  1, 'json');
    }
    
    /**
     * 加载动态的模板信息
     */
    public function loadFeedTemplateAjax() {
        $this->display('feed');
    }
    
}