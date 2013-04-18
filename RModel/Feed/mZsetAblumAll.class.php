<?php
import('@.RModel.Feed.mFeedBase');
class mZsetAblumAll extends mFeedBase {
    
    public function __construct() {
        import('RData.Feed.dZsetAlbumAll');
        $this->_rdata = new dZsetAlbumAll();
    }
    
    /**
     * 加载班级相册动态信息
     * @param $id			  我们网账号
     * @param $lastFeedId        最后查询结果的feed_id
     * @param $limit   
     */
    protected function loader ($id, $lastFeedId = 0, $limit = 10) {
        if(empty($id)) {
            return false;
        }
        
        $uids = array();
        
        // 通过用户账号获取用户所有关系成员，包括所有班级成员，用户好友,最终结果为去重后的数组
        $mUserVm = ClsFactory::Create('RModel.mUserVm');
        
        $all_relation = $mUserVm->getUserAllRelations($id);

        if (empty($all_relation)) return false;
        
        $all_count = count($all_relation);
        if ($all_count < 500) {
            $uids = $all_relation;
            unset($all_relation);
        } else {
            // 获取活跃用户库
            $mSetActiveUser = ClsFactory::Create('RModel.Common.mSetActiveUser');
            
            $active_uids = $mSetActiveUser->getActiveUserSet();
            
            //获取所有用户与活跃用户库的交集
            $r = array_intersect($all_relation, $active_uids);
            $r_count = count($r);
            
            if ($r_count > 500) {
              $uids = array_slice($r, 0, 500);
            } else {
              $r_diff = array_diff($all_relation, $r);
              $uids = array_slice($r + (array)$r_diff , 0, 500);
            }
            
        }
        
        $mFeedTimeLine = ClsFactory::Create('Model.Feed.mFeedTimeLine');

        $datas_from_db = $mFeedTimeLine->getFeedByUidsAndType($uids, FEED_ALBUM, $lastFeedId, $limit);
        
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