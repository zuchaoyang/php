<?php
/**
 * 负责根据不同的情况从数据库中加载数据
 * @author Administrator
 *
 */
class FetchDatabaseFeed  {
	/**
     * 获取班级成员的动态信息
     * @param $class_code
     */
    public function getClassFeedAllFromDatabase($class_code, $where_appends, $offset = 0, $limit = 10) {
        if(empty($class_code)) {
            return false;
        }
        
        $class_uids = $this->getClassMembers($class_code);
        
        $mFeed = ClsFactory::Create('Model.Feed.mFeed');
        $class_feed_list = $mFeed->getFeedByAddAccount($class_uids, $where_appends, $offset, $limit);
        
        return !empty($class_feed_list) ? $class_feed_list : false;
    }
    
	/**
     * 获取用户的好友相册动态信息
     * @param $uid
     */
    public function getUserAlbumFeedFromDatabase($uid, $where_appends, $offset = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $friend_uids = $this->getFriendMembers($uid);

        $where_arr = array(
            'feed_type' => FEED_ALBUM,
        );
        if(!empty($where_appends)) {
            $where_arr = array_merge((array)$where_arr, (array)$where_appends);
        }
        
        $mFeed = ClsFactory::Create('Model.Feed.mFeed');
        $album_feed_list = $mFeed->getFeedByAddAccount($friend_uids, $where_arr, $offset, $limit);
        
        return !empty($album_feed_list) ? $album_feed_list : false;
    }
    
	/**
     * 从数据库获取用户的动态信息
     * @param $uid
     * @param $where_appends
     * @param $offset
     * @param $limit
     */
    public function getUserAllFeedFromDatabase($uid, $where_appends, $offset = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $user = $this->getUser($uid);
        $client_type = $user['client_type'];
        
        $follows = array();
        
        //如果是家长优先获取孩子的动态信息
        if($client_type == CLIENT_TYPE_FAMILY) {
            $child_uids = $this->getChildMembers($uid);
            $follows = array_merge((array)$follows, (array)$child_uids);
        }
        
        //其次用户关心的是好友的动态信息
        $friend_uids = $this->getFriendMembers($uid);
        $follows = array_merge((array)$follows, (array)$friend_uids);
        
        if(in_array($client_type, array(CLIENT_TYPE_STUDENT, CLIENT_TYPE_TEACHER))) {
            $class_code_list = array_keys($user['class_info']);
            foreach((array)$class_code_list as $class_code) {
                $class_uids = $this->getClassMembers($class_code);
                $follows = array_merge((array)$follows, (array)$class_uids);
            }
        }
        
        //follows关系最多为500
        if(count($follows) > 500) {
            $follows = array_slice($follows, 0, 500);
        }
        
        $mFeed = ClsFactory::Create('Model.Feed.mFeed');
        
        return  $mFeed->getFeedByAddAccount($follows, $where_appends, $offset, $limit);
    }
    
	/**
     * 从数据库获取用户的动态信息
     * @param $uid
     * @param $where_appends
     * @param $offset
     * @param $limit
     */
    public function getUserChildrenFeedFromDatabase($uid, $where_appends, $offset = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $mUserChildrenSet = ClsFactory::Create('RModel.Common.mUserChildrenSet');
        $child_uids = $mUserChildrenSet->getUserChildrenSet($uid);
        
        $mFeed = ClsFactory::Create('Model.Feed.mFeed');
        return  $mFeed->getFeedByAddAccount($child_uids, $where_appends, $offset, $limit);
    }
    
	/**
     * 从数据库获取用户的动态信息
     * @param $uid
     * @param $where_appends
     * @param $offset
     * @param $limit
     */
    public function getUserMyFeedFromDatabase($uid, $where_appends, $offset = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $follows = array();
        
        $user = $this->getUser($uid);
        $client_type = $user['client_type'];
         //如果是家长优先获取孩子的动态信息
        if($client_type == CLIENT_TYPE_FAMILY) {
            $child_uids = $this->getChildMembers($uid);
            $follows = array_merge((array)$follows, (array)$child_uids);
        }
        
        //获取用户的好友关系
        $friend_follows = $this->getFriendMembers($uid);
        $follows = array_merge((array)$follows, (array)$friend_follows);
        
        //follows关系最多为500
        if(count($follows) > 500) {
            $follows = array_slice($follows, 0, 500);
        }
        
        $mFeed = ClsFactory::Create('Model.Feed.mFeed');
        return  $mFeed->getFeedByAddAccount($follows, $where_appends, $offset, $limit);
    }
    
    /**
     * 获取用户的基本信息
     * @param $uid
     */
    private function getUser($uid) {
        if(empty($uid)) {
            return false;
        }
        
        $mUserObjectHash = ClsFactory::Create('RModel.Common.mUserObjectHash');
        return $mUserObjectHash->getUserObjectHash($uid);
    }
    
	/**
     * 获取班级成员的动态信息
     * @param $class_code
     */
    private function getClassMembers($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        $mClassStudentSet = ClsFactory::Create('RModel.Common.mClassStudentSet');
        $mClassTeacherSet = ClsFactory::Create('RModel.Common.mClassTeacherSet');
        
        $student_uids = $mClassStudentSet->getClassStudentSet($class_code);
        $teacher_uids = $mClassTeacherSet->getClassTeacherSet($class_code);
        
        //合并班级的成员列表
        return array_unique(array_merge((array)$student_uids, (array)$teacher_uids));
    }
    
	/**
     * 获取用户好友的全部动态信息
     * @param $uid
     */
    private function getFriendMembers($uid) {
        if(empty($uid)) {
            return false;
        }
        
        $mUserFriendSet = ClsFactory::Create('RModel.Common.mUserFriendSet');
        
        return $mUserFriendSet->getUserFriendSet($uid);
    }
    
	/**
     * 获取用户的孩子信息动态
     * @param $uid
     */
    private function getChildMembers($uid) {
        if(empty($uid)) {
            return false;
        }
        
        $mUserChildrenSet = ClsFactory::Create('RModel.Common.mUserChildrenSet');
        return $mUserChildrenSet->getUserChildrenSet($uid);
    }
}