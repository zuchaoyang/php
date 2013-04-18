<?php
import('@.RModel.Feed.mFeedBase');
class mZsetUserFriend extends mFeedBase {

    public function __construct() {
        import('RData.Feed.dZsetUserFriend');
        $this->_rdata = new dZsetUserFriend();
    }
    
    /**
     * 加载班级相册动态信息
     * @param $id			  我们网账号
     * @param $timeline      最后查询结果的时间点
     * @param $lastFeedId        最后查询结果的feed_id
     * @param $limit   
     */
    protected function loader ($id, $lastFeedId = 0, $limit = 10) {
        if(empty($id)){
            return false;
        }
        
        $uids = array();
        
        $mSetClientFriends = ClsFactory::Create('RModel.Common.mSetClientFriends');
	    $all_friends = $mSetClientFriends->getClientFriendsByUid($id);
	    
	    $all_count = count($all_friends);
        if ($all_count < 500) {
            $uids = $all_friends;
            unset($all_friends);
        } else {
            // 获取活跃用户库
            $mSetActiveUser = ClsFactory::Create('RModel.Common.mSetActiveUser');
            
            $active_uids = $mSetActiveUser->getActiveUserSet();
            
            //获取所有用户与活跃用户库的交集
            $r = array_intersect($all_friends, $active_uids);
            $r_count = count($r);
            
            if ($r_count > 500) {
              $uids = array_slice($r, 0, 500);
            } else {
              $r_diff = array_diff($all_friends, $r);
              $uids = array_slice($r + (array)$r_diff , 0, 500);
            }
            
        }
        
        $mFeedTimeLine = ClsFactory::Create('Model.Feed.mFeedTimeLine');

        $datas_from_db = $mFeedTimeLine->getFeedByUids($uids, $lastFeedId, $limit);
        
        $result = array();
        foreach ($datas_from_db as $key => $val) {
            $result[] = array(
            	'value' => $val['feed_id'],
                'score' => $val['feed_id']
            );
        }
        
        return $result;
    }
}