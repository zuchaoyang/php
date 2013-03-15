<?php
import('@.RModel.Feed.mFeedBase');
class mZsetUserAll extends mFeedBase {
    
    public function __construct() {
        import('RData.Feed.dZsetUserAll');
        $this->_rdata = new dZsetUserAll();
    }
        
    /**
     * 加载与我相关全部动态信息
     * 1. 我产生的动态
     * 2. 我朋友的动态.
     * 3. 我所在班级的动态.
     * @param $id			  我们网帐号
     * @param $lastFeedId        最后查询结果的feed_id
     * @param $limit   
     */
    protected function loader($id, $lastFeedId = 0, $limit = 10) {
        if(empty($id)) {
            return false;
        }
        
        $uids = array();

        // 通过用户账号获取用户所有关系成员，包括所有班级成员，用户好友,最终结果为去重后的数组
        $mUserVm = ClsFactory::Create('RModel.mUserVm');
        
        $all_friends = $mUserVm->getUserAllRelations($id);

        if (empty($all_friends)) return false;
        
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
                $r_adds = array_merge($r, (array)$r_diff);
                $uids = array_slice($r_adds, 0, 500);
            }
            
        }
        array_unshift($uids, $id);
        $mFeedTimeLine = ClsFactory::Create('Model.Feed.mFeedTimeLine');

        $datas_from_db = $mFeedTimeLine->getFeedByUids($uids, $lastFeedId, $limit);
        
        $result = array();
        foreach ($datas_from_db as $key => $val) {
            $result[] = array(
                'value' => $val['feed_id'],
                'score' => $val['feed_id'],
            );
        }
        return $result;
    }
}