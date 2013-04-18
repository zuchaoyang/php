<?php
class mFeedVm {

    public function __construct() {
        
    }
    
    /**
     * 得到班级全部动态
     * @param int $class_code  班级id
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     * @return
     * 
     */
    public function getClassAllFeed($class_code, $lastId = 0, $limit = 10){
        if(empty($class_code)) {
            return false;
        }
        
        $mZsetClassAll = ClsFactory::Create("RModel.Feed.mZsetClassAll");
        
        $feed_id_list = $mZsetClassAll->getFeedById($class_code, $lastId, $limit);
        return $this->getFeed($feed_id_list);
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
        
        $mZsetClassAlbum = ClsFactory::Create("RModel.Feed.mZsetClassAlbum");
        
        $feed_id_list = $mZsetClassAlbum->getFeedById($class_code, $lastId, $limit);

        return $this->getFeed($feed_id_list);
    }
    
    /**
     * 得到所有动态
     * @param int $id 			班级id
     * @param int $timeline    修改时间
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     */
    public function getUserAllFeed($uid, $lastId = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $mZsetUserAll = ClsFactory::Create("RModel.Feed.mZsetUserAll");
        $feed_id_list = $mZsetUserAll->getFeedById($uid, $lastId, $limit);

        return $this->getFeed($feed_id_list);
    }
    
    /**
     * 得到孩子动态
     * @param int $uid 			班级id
     * @param int $timeline    修改时间
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     */
    public function getUserChildrenFeed($uid, $lastId = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $mZsetUserChildren = ClsFactory::Create("RModel.Feed.mZsetUserChildren");
        
        $feed_id_list = $mZsetUserChildren->getFeedById($uid, $lastId, $limit);
        
        return $this->getFeed($feed_id_list);
    }
    
    /**
     * 得到全部相册动态
     * @param int $uid 			我们网账号
     * @param int $timeline    修改时间
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     */
    public function getAblumAllFeed($uid, $lastId = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $mZsetAblumAll = ClsFactory::Create("RModel.Feed.mZsetAblumAll");
        
        $feed_id_list = $mZsetAblumAll->getFeedById($uid, $lastId, $limit);
        
        return $this->getFeed($feed_id_list);
    }
    
    /**
     * 得到与我相关动态
     * @param int $uid 			我们网账号
     * @param int $timeline    修改时间
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     */
    public function getUserMyFeed($uid, $lastId = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $mZsetUserMy = ClsFactory::Create("RModel.Feed.mZsetUserMy");
        
        $feed_id_list = $mZsetUserMy->getFeedById($uid, $lastId, $limit);
        
        return $this->getFeed($feed_id_list);
    }
    
    /**
     * 得到朋友动态
     * @param int $uid 			我们网账号
     * @param int $timeline    修改时间
     * @param int $lastId      最后查询feed_id
     * @param int $limit       查询数量
     */
    public function getUserFriendFeed($uid,$lastId = 0, $limit = 10) {
        if(empty($uid)) {
            return false;
        }
        
        $mZsetUserFriend = ClsFactory::Create("RModel.Feed.mZsetUserFriend");
        
        $feed_id_list = $mZsetUserFriend->getFeedById($uid, $lastId, $limit);
        
        return $this->getFeed($feed_id_list);
    }
    
    
    /**
     * 获取feed内容并且添加relation_id
     * @param array $feed_id  array(
     * 								'feed_id' => array('id' => feed_id),
     * 								'timeline' => array('id.feed_id' => timeline)
     * 							)
     */
    
    private function getFeed($datas) {
        if(empty($datas)) {
            return false;
        }

        $mFeed = ClsFactory::Create("Model.Feed.mFeed");
        
        $feed_ids = array();
        $len = count($datas);

        foreach($datas as $key => $val) {
            $feed_ids[] = $key;
        }
        $feed_list = $mFeed->getFeedByid($feed_ids);
        return $feed_list;
    }
    
}