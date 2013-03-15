<?php
/**
 * Author: lnc<lnczx0915@gmail.com>
 * 功能: 动态开放接口
 * 说明:	作为C层业务的接口封装，与mFeedVm直接联系
 * 
*/

class FeedApi extends ApiController {

    /**
     * 动态分发接口
     * @param $uid       	  帐号
     * @param $from_id       产生动态的实体id
     * @param $feed_type     int    枚举值   1:说说  2：日志  3：相册
     * 
     */    
    
    public function dispatch($uid, $from_id, $feed_type) {
        //todo
    }
    
    /**
     * 创建用户动态信息
     * @param $uid       	  帐号
     * @param $from_id       产生动态的实体id
     * @param $feed_type     int    枚举值   1:说说  2：日志  3：相册
     * @param $action        int    枚举值   1: 发布， 2:评论
     */
    public function user_create($uid, $from_id, $feed_type, $action = FEED_ACTION_PUBLISH) {
        if(empty($uid) || empty($from_id) || empty($feed_type)) {
            return false;
        }
        
        import('@.Control.Api.FeedImpl.CreateFeed');
        $createFeed = new CreateFeed();
        
        return $createFeed->createPersonFeed($uid, $from_id, $feed_type, $action);
    }
    
    /**
     * 添加用户在班级空间中产生的动态
     * @param $class_code     int    用户当前所在的班级      
     * @param $uid       	  帐号
     * @param $from_id       产生动态的实体id
     * @param $feed_type     int    枚举值   1:说说  2：日志  3：相册
     * @param $action        int    枚举值   1: 发布， 2:评论
     */
    public function class_create($class_code, $uid, $from_id, $feed_type, $action = FEED_ACTION_PUBLISH) {
        if(empty($class_code) || empty($uid) || empty($from_id) || empty($feed_type)) {
            return false;
        }
        
        import('@.Control.Api.FeedImpl.CreateFeed');
        $createFeed = new CreateFeed();
        return $createFeed->createClassFeed($class_code, $uid, $from_id, $feed_type, $action);
    }
    
    /**
     * 得到班级全部动态
     * @param int $class_code  班级id
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     * 
     * @return
     * 
     */
    public function getClassAllFeed($class_code, $lastId = 0, $limit = 10){
        if(empty($class_code)) {
            return false;
        }
        
        $mFeedVm = ClsFactory::Create("RModel.mFeedVm");
        
        return $mFeedVm->getClassAllFeed($class_code, $lastId, $limit);
    }
    
    /**
     * 得到班级相册动态
     * @param int $class_code  班级id
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     */
    public function getClassAlbumFeed($class_code, $lastId = 0, $limit = 10) {
        
        if(empty($class_code)) {
            return false;
        }
        
        $mFeedVm = ClsFactory::Create("RModel.mFeedVm");
        
        return $mFeedVm->getClassAlbumFeed($class_code, $lastId, $limit);
    }
    
    /**
     * 得到所有动态
     * @param int $uid 		       我们网帐号
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     */
    public function getUserAllFeed($uid, $lastId = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $mFeedVm = ClsFactory::Create("RModel.mFeedVm");
        return $mFeedVm->getUserAllFeed($uid, $lastId, $limit);
    }
    
    /**
     * 得到孩子动态
     * @param int $uid 		       我们网帐号
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     */
    public function getUserChildrenFeed($uid, $lastId = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $mFeedVm = ClsFactory::Create("RModel.mFeedVm");
        
        return $mFeedVm->getUserChildrenFeed($uid, $lastId, $limit);
    }

    /**
     * 得到全部相册动态
     * @param int $uid 		       我们网账号
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     */
    public function getAblumAllFeed($uid, $lastId = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $mFeedVm = ClsFactory::Create("RModel.mFeedVm");
        
        return $mFeedVm->getAblumAllFeed($uid, $lastId, $limit);
    }
    
    /**
     * 得到与我相关动态
     * @param int $uid 			我们网账号
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     */
    public function getUserMyFeed($uid, $lastId = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $mFeedVm = ClsFactory::Create("RModel.mFeedVm");
        
        return $mFeedVm->getUserMyFeed($uid, $lastId, $limit);
    }
    
    /**
     * 得到朋友动态
     * @param int $uid 		       我们网账号
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     */
    public function getUserFriendFeed($uid, $lastId = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $mFeedVm = ClsFactory::Create("RModel.mFeedVm");
        
        return $mFeedVm->getUserFriendFeed($uid, $lastId, $limit);
    }    
    
}