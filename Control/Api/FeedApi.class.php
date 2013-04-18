<?php
/**
 * Author: lnc<lnczx0915@gmail.com>
 * 功能: 动态开放接口
 * 说明:	作为C层业务的接口封装，与mFeedVm直接联系
 * 
*/

class FeedApi extends ApiController {
    private $FeedParser;
    
    public function __construct() {
        parent::__construct();
        
        import('@.Control.Api.FeedImpl.FeedParser');
        $this->FeedParser = new FeedParser();
    }

    /**
     * 动态分发接口
     * @param $uid       	  帐号
     * @param $from_id       产生动态的实体id
     * @param $feed_type     int    枚举值   1:说说  2：日志  3：相册
     * 
     */
    private function person_dispatch($uid, $feed_id, $feed_type, $action = FEED_ACTION_PUBLISH) {
        if(empty($feed_id) || empty($feed_type)) {
            return false;
        }
        
        $params = array(
        	'class_code'    => "",
            'uid'		    =>  $uid,
            'feed_id'   => $feed_id,
            'feed_type'	=> $feed_type,
            'action'	=> $action
        );
        
        $params = serialize($params);
        
        return Gearman::send("feed_person_dispatch", $params, PRIORITY_NORMAL, false);
    }
    
    private function class_dispatch($class_code, $uid, $feed_id, $feed_type, $action = FEED_ACTION_PUBLISH) {
        if(empty($feed_id) || empty($feed_type)) {
            return false;
        }
        
        $params = array(
        	'class_code'    => $class_code,
            'uid'		    =>  $uid,
            'feed_id'   => $feed_id,
            'feed_type'	=> $feed_type,
            'action'	=> $action
        );
        
        $params = serialize($params);
        
        return Gearman::send("feed_class_dispatch", $params, PRIORITY_NORMAL, false);
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
        $feed_id = $createFeed->createPersonFeed($uid, $from_id, $feed_type, $action);

        $this->person_dispatch($uid, $feed_id, $feed_type, $action);
        return $feed_id;
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
        $feed_id = $createFeed->createClassFeed($class_code, $uid, $from_id, $feed_type, $action);
        
        $this->class_dispatch($class_code, $uid, $feed_id, $feed_type, $action);
        return $feed_id;
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
        
        $feed_list = $mFeedVm->getClassAllFeed($class_code, $lastId, $limit);
        
        return $this->FeedParser->parseFeed($feed_list);
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
        
        $feed_list = $mFeedVm->getClassAlbumFeed($class_code, $lastId, $limit);
        
        return $this->FeedParser->parseFeed($feed_list);
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
        $feed_list = $mFeedVm->getUserAllFeed($uid, $lastId, $limit);
        
        return $this->FeedParser->parseFeed($feed_list);
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
        $feed_list = $mFeedVm->getUserChildrenFeed($uid, $lastId, $limit);
        
        return $this->FeedParser->parseFeed($feed_list);
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
        
        $feed_list = $mFeedVm->getAblumAllFeed($uid, $lastId, $limit);
        
        return $this->FeedParser->parseFeed($feed_list);
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
        $feed_list = $mFeedVm->getUserMyFeed($uid, $lastId, $limit);
        
        return $this->FeedParser->parseFeed($feed_list);
    }
    
    /**
     * 得到单个动态信息
     * @param int $uid 			我们网账号
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     */
    public function getFeedById($feed_ids) {
        if(empty($feed_ids)) {
            return false;
        }
        
        $feed_ids = (array)$feed_ids;
        
        $mFeed = ClsFactory::Create("Model.Feed.mFeed");

        $feed_list = $mFeed->getFeedByid($feed_ids);
        
        return $this->FeedParser->parseFeed($feed_list);
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
        $feed_list = $mFeedVm->getUserFriendFeed($uid, $lastId, $limit);
        
        return $this->FeedParser->parseFeed($feed_list);
    }    
    
}