<?php

define('FEED_NUMS', 5);
define('COMMENT_NUMS', 5);

class ListAction extends SnsController {
    
    /**
     * 获取班级的全部动态信息
     */
    public function getClassAllFeedAjax() {
        $class_code = $this->objInput->getInt('class_code');
        $last_id = $this->objInput->getInt('last_id');
        
        $last_id = max(0, $last_id);
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '您暂时没有权限查看该班动态信息!', -1, 'json');
        }
        
        import('@.Control.Api.FeedApi');
        $FeedApi = new FeedApi();
        $feed_list = $FeedApi->getClassAllFeed($class_code, $last_id, 10);
        if(empty($feed_list)) {
            $this->ajaxReturn(null, '没有更多的动态信息!', -1, 'json');
        }
        
        //权限的处理问题
        $is_class_admin = $this->isClassAdmin($class_code);
        foreach($feed_list as $feed_id=>$feed) {
            if($is_class_admin || $this->user['client_account'] == $feed['add_account']) {
                $feed['can_del'] = true;
            } else {
                $feed['can_del'] = false;
            }
            
            $feed_list[$feed_id] = $feed;        
        }
        
        $ret_list = array(
            'feed_list' => $feed_list,
            'last_id'   => min(array_keys($feed_list)),
        );
        
        $this->ajaxReturn($ret_list, '获取成功!', 1, 'json');
    }
    
    /**
     * 获取班级相应的相册的动态信息
     */
    public function getClassAlbumFeedAjax() {
        $class_code = $this->objInput->getInt('class_code');
        $last_id = $this->objInput->getInt('last_id');
        
        $last_id = max(0, $last_id);
        
        import('@.Control.Api.FeedApi');
        $FeedApi = new FeedApi();
        $feed_list = $FeedApi->getClassAlbumFeed($class_code, $last_id, 10);
        
        if(empty($feed_list)) {
            $this->ajaxReturn(null, '没有更多的动态信息!', -1, 'json');
        }
        
        $ret_list = array(
            'feed_list' => $feed_list,
            'last_id'	=> min(array_keys($feed_list)),
        );
        
        $this->ajaxReturn($ret_list, '获取成功!', 1, 'json');
    }
    
    /**
     * 获取个人相应的相册的动态信息
     */
    public function getAblumAllFeedAjax() {
        $client_account = $this->objInput->getInt('client_account');
        $last_id = $this->objInput->getInt('last_id');
        
        $last_id = max(0, $last_id);
        
        import('@.Control.Api.FeedApi');
        $FeedApi = new FeedApi();
        $feed_list = $FeedApi->getAblumAllFeed($client_account, $last_id, 10);
        
        if(empty($feed_list)) {
            $this->ajaxReturn(null, '没有更多的动态信息!', -1, 'json');
        }
        
        $ret_list = array(
            'feed_list' => $feed_list,
            'last_id'	=> min(array_keys($feed_list)),
        );
        
        $this->ajaxReturn($ret_list, '获取成功!', 1, 'json');
    }    

    
    /**
     * 获取孩子的动态信息
     */
    public function getUserChildrenFeedAjax() {
        $client_account = $this->objInput->getInt('client_account');
        $last_id = $this->objInput->getInt('last_id');
        
        $last_id = max(0, $last_id);
        
        //判断用户是否是家长
        if($this->user['client_type'] != CLIENT_TYPE_FAMILY) {
            $this->ajaxReturn(null, '只有家长才能查看孩子动态信息!', -1, 'json');
        }
        
        import('@.Control.Api.FeedApi');
        $FeedApi = new FeedApi();
        $feed_list = $FeedApi->getUserChildrenFeed($client_account, $last_id, 10);
        
        if(empty($feed_list)) {
            $this->ajaxReturn(null, '没有更多的动态信息!', -1, 'json');
        }
        
        $ret_list = array(
            'feed_list' => $feed_list,
            'last_id'	=> min(array_keys($feed_list)),
        );
        
        $this->ajaxReturn($ret_list, '获取成功!', 1, 'json');
    }
    
  	/**
     * 获取用户的全部动态信息
     */
    public function getUserAllFeedAjax() {
        $client_account = $this->objInput->getInt('client_account');
        $last_id = $this->objInput->getStr('last_id');
        
        $last_id = max(0, $last_id);
        
        import('@.Control.Api.FeedApi');
        $FeedApi = new FeedApi();
        $feed_list = $FeedApi->getUserAllFeed($client_account, $last_id, 10);
        if(empty($feed_list)) {
            $this->ajaxReturn(null, '没有更多的动态信息!', -1, 'json');
        }
        
        //处理动态的删除权限问题
        foreach((array)$feed_list as $feed_id => $feed) {
            if($feed['add_account'] == $this->user['client_account']) {
                $feed['can_del'] = true;
            }
            $feed_list[$feed_id] = $feed;
        }
        
        $ret_list = array(
            'feed_list' => $feed_list,
            'last_id' => min(array_keys($feed_list)),
        );
        
        $this->ajaxReturn($ret_list, '获取成功!',  1, 'json');
    }
    
	/**
     * 获取与我相关的动态信息
     */
    public function getUserMyFeedAjax() {
        $client_account = $this->objInput->getInt('client_account');
        $last_id = $this->objInput->getStr('last_id');
        
        $last_id = max(0, $last_id);
        
        import('@.Control.Api.FeedApi');
        $FeedApi = new FeedApi();
        $feed_list = $FeedApi->getUserMyFeed($client_account, $last_id, 10);
        if(empty($feed_list)) {
            $this->ajaxReturn(null, '没有更多的动态信息!', -1, 'json');
        }
        
        //处理动态的删除权限问题
        foreach((array)$feed_list as $feed_id => $feed) {
            if($feed['add_account'] == $this->user['client_account']) {
                $feed['can_del'] = true;
            }
            $feed_list[$feed_id] = $feed;
        }
        
        $ret_list = array(
            'feed_list' => $feed_list,
            'last_id' => min(array_keys($feed_list)),
        );
        
        $this->ajaxReturn($ret_list, '获取成功!',  1, 'json');
    }
    
    /**
     * 获取用户的好友的动态信息
     */
    public function getUserFriendFeedAjax() {
        $client_account = $this->objInput->getInt('client_account');
        $last_id = $this->objInput->getStr('last_id');
        
        $last_id = max(0, $last_id);
        
        import('@.Control.Api.FeedApi');
        $FeedApi = new FeedApi();
        $feed_list = $FeedApi->getUserFriendFeed($client_account, $last_id, 10);
        if(empty($feed_list)) {
            $this->ajaxReturn(null, '没有更多的动态信息!', -1, 'json');
        }
        
        //处理动态的删除权限问题
        foreach((array)$feed_list as $feed_id => $feed) {
            if($feed['add_account'] == $this->user['client_account']) {
                $feed['can_del'] = true;
            }
            $feed_list[$feed_id] = $feed;
        }
        
        $ret_list = array(
            'feed_list' => $feed_list,
            'last_id' => min(array_keys($feed_list)),
        );
        
        $this->ajaxReturn($ret_list, '获取成功!',  1, 'json');
    }
    
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
     * 加载动态的模板信息
     */
    public function loadFeedTemplateAjax() {
//        $tpl = $this->objInput->getStr('tpl');
//        
//        $tpl = in_array($tpl, array('big', 'middle')) ? $tpl : 'middle';
        
        $this->display('feed');
    }
    
    /**
     * 获取动态对应实体的评论信息
     * @return html
     */
    public function getEntityAjax() {
        $feed_type = $this->objInput->getInt('feed_type');
        $from_id = $this->objInput->getInt('from_id');
        
        if(empty($feed_type) || empty($from_id) || !in_array($feed_type, array(FEED_MOOD, FEED_ALBUM, FEED_BLOG))) {
            $this->ajaxReturn(null, '参数错误', -1, 'json');
        }
        
        //获取实体的相关信息
        if($feed_type == FEED_MOOD) {
            $mMood = ClsFactory::Create('Model.Mood.mMood');
            $entity_list = $mMood->getMoodById($from_id);
        } elseif($feed_type == FEED_ALBUM) {
            $mBlog = ClsFactory::Create('Model.Blog.mBlog');
            $entity_list = $mBlog->getBlogById($from_id);
        } elseif($feed_type == FEED_BLOG) {
            $mAlbumPhotos = ClsFactory::Create('Model.Album.mAlbumPhotos');
            $entity_list = $mAlbumPhotos->getPhotosByPhotoId($from_id);
        }
        
        $this->ajaxReturn($entity_list[$from_id], '获取成功!', 1, 'json');
    }
    
}