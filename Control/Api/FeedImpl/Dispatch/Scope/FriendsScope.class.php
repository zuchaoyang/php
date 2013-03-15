<?php
import('@.Control.Api.FeedImpl.Dispatch.Scope.ScopeBase');

class FriendsScope extends ScopeBase {
    
    public function handle($uid, $add_time, $feed_id, $feed_type) {
        if(empty($uid) || empty($feed_id)) {

            FEED_DEBUG && trigger_error('动态分发时，缺失必要参数!', E_USER_ERROR);
            
            return false;
        }
        
        $follows = $this->getFollows($uid);
        if(empty($follows)) {
            FEED_DEBUG && trigger_error("动态分发时-- {$uid} -- 的好友集合中的follow关系获取失败!", E_USER_ERROR);
            
            return false;
        }
        
        $this->dispatchUserAll($follows, $add_time, $feed_id);
        $this->dispatchUserMy($follows, $add_time, $feed_id);
        
        if($feed_type == FEED_ALBUM) {
            
            //debug
            echo "<span style='color:red'>触发了好友集合中的相册动态信息!</span>";
            
            $this->dispatchUserAlbum($follows, $add_time, $feed_id);
        }
    }
    
    /**
     * 获取用户的好友关系
     * @param $uid
     */
    protected function getFollows($uid) {
        if(empty($uid)) {
            return false;
        }
        
        //获取用户的好友关系
        $mUserFriendSet = ClsFactory::Create('RModel.Common.mUserFriendSet');
        
        return $mUserFriendSet->getOnlineUserFriendSet($uid);
    }
    
}