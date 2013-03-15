<?php
import('@.Control.Api.FeedImpl.Dispatch.Scope.ScopeBase');

class ParentsScope extends ScopeBase {

    public function handle($uid, $add_time, $feed_id, $feed_type) {
        if(empty($uid) || empty($feed_id)) {
            FEED_DEBUG && trigger_error('动态分发时，缺失必要参数!', E_USER_ERROR);    
        }
        
        $parent_follows = $this->getFollows($uid);
        if(empty($parent_follows)) {
            FEED_DEBUG && trigger_error("动态分发时，学生 -- {$uid} --对应的家长的follow关系获取失败!", E_USER_ERROR);
            
            return false;
        }
        
        //推送到家长的全部动态
        $this->dispatchUserAll($parent_follows, $add_time, $feed_id);
        //推送到家长的孩子动态
        $this->dispatchUserChild($parent_follows, $add_time, $feed_id);
        //推送到家长的与我相关的动态
        $this->dispatchUserMy($parent_follows, $add_time, $feed_id);
    }
    
    /**
     * 获取用户的家长信息
     * @param $uid
     */
    protected function getFollows($uid) {
        if(empty($uid)) {
            return false;
        }
        
        //获取用户的家长信息
        $mUserParentSet = ClsFactory::Create('RModel.Common.mUserParentSet');
        
        return $mUserParentSet->getOnlineUserParentSet($uid);
    }
    
}