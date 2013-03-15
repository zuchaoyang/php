<?php

class LoaderFeed {
    protected $feed_size = 100;
    protected $fetchFeedObject = null;
    
    public function __construct() {
        import('RData.Feed.Loader.FetchDatabaseFeed');
        $this->fetchFeedObject = new FetchDatabaseFeed();
    }
    
    /**
     * 加载个人的动态信息
     * @param $uid
     */
    public function loadUserFeed($uid) {
        if(empty($uid)) {
            return false;
        }
        
        $mUserObjectHash = ClsFactory::Create('RModel.Common.mUserObjectHash');
        $user = $mUserObjectHash->getUserObjectHash($uid);
        
        $this->loadUserAlbumFeedZset($uid);
        $this->loadUserFeedAllZset($uid);
        $this->loadUserMyFeedZset($uid);
        
        if($user['client_type'] == CLIENT_TYPE_FAMILY) {
            $this->loadUserChildFeedZset($uid);
        }
    }
    
    /**
     * 加载班级的动态信息
     * @param $class_code
     */
    public function loadClassFeed($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        return $this->loadClassFeedAllZset($class_code);
    }
    
    /**
     * 加载用户的班级全部信息
     * @param $class_code
     */
    private function loadClassFeedAllZset($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        //获取班级成员的全部动态信息
        $class_feed_list = $this->fetchFeedObject->getClassFeedAllFromDatabase($class_code, null, 0, $this->feed_size);
        if(empty($class_feed_list)) {
            return false;
        }
        
        $dataarr = array();
        foreach($class_feed_list as $feed_id=>$feed) {
            $dataarr[] = array(
                'class_code'  => $class_code,
                'feed_id'     => $feed_id,
                'add_time'    => $feed['add_time'],
            );
        }
        
        $dClassFeedAllZset = ClsFactory::Create('RData.Feed.dClassFeedAllZset');
        
        return $dClassFeedAllZset->addClassFeedAllZsetBat($dataarr);
    }
    
    /**
     * 获取好友的相册动态信息
     * @param $uid
     */
    private function loadUserAlbumFeedZset($uid) {
        if(empty($uid)) {
            return false;
        }
        
        //获取好友的相册动态信息
        $friend_album_feed_list = $this->fetchFeedObject->getUserAlbumFeedFromDatabase($uid, null, 0, $this->feed_size);
        if(empty($friend_album_feed_list)) {
            return false;
        }
        
        $dataarr = array();
        foreach($friend_album_feed_list as $feed_id=>$feed) {
            $dataarr[] = array(
                'uid'      => $uid,
                'feed_id'  => $feed_id,
                'add_time' => $feed['add_time'],
            );
        }
        
        $dUserAlbumFeedZset = ClsFactory::Create('RData.Feed.dUserAlbumFeedZset');
        
        return $dUserAlbumFeedZset->addUserAlbumFeedZsetBat($dataarr);
    }
    
    /**
     * 获取用户的孩子动态信息
     * @param $uid
     */
    private function loadUserChildFeedZset($uid) {
        if(empty($uid)) {
            return false;
        }
        
        //获取用户的孩子动态信息
        $child_feed_list = $this->fetchFeedObject->getUserChildrenFeedFromDatabase($uid, null, 0, $this->feed_size);
        if(empty($child_feed_list)) {
            return false;
        }
        
        $dataarr = array();
        foreach($child_feed_list as $feed_id=>$feed) {
            $dataarr[] = array(
                'uid'      => $uid,
                'feed_id'  => $feed_id,
                'add_time' => $feed['add_time'],
            );
        }
        
        $dUserChildFeedZset = ClsFactory::Create('RData.Feed.dUserChildFeedZset');
        
        return $dUserChildFeedZset->addUserChildFeedZsetBat($dataarr);
    }
    
    /**
     * 获取用户的全部动态信息
     * @param $uid
     */
    private function loadUserFeedAllZset($uid) {
        if(empty($uid)) {
            return false;
        }
        
        $feed_list = $this->fetchFeedObject->getUserAllFeedFromDatabase($uid, null, 0, $this->feed_size);
        if(empty($feed_list)) {
            return false;
        }
        
        $dataarr = array();
        foreach($feed_list as $feed_id=>$feed) {
            $dataarr[] = array(
                'uid'      => $uid,
                'feed_id'  => $feed_id,
                'add_time' => $feed['add_time'],
            );
        }
        
        $dUserFeedAllZset = ClsFactory::Create('RData.Feed.dUserFeedAllZset');
        
        return $dUserFeedAllZset->addUserFeedAllZsetBat($dataarr);
    }
    
    /**
     * 获取用户的与我相关的动态信息
     * @param $uid
     */
    private function loadUserMyFeedZset($uid) {
        if(empty($uid)) {
            return false;
        }
        
        $feed_list = $this->fetchFeedObject->getUserMyFeedFromDatabase($uid, null, 0, $this->feed_size);
        if(empty($feed_list)) {
            return false;
        }
        
        $dataarr = array();
        foreach($feed_list as $feed_id=>$feed) {
            $dataarr[] = array(
                'uid'      => $uid,
                'feed_id'  => $feed_id,
                'add_time' => $feed['add_time'],
            );
        }
        
        $dUserMyFeedZset = ClsFactory::Create('RData.Feed.dUserMyFeedZset');
        
        return $dUserMyFeedZset->addUserMyFeedZsetBat($dataarr);
    }
}