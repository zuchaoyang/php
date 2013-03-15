<?php

/**
 * 用户全部动态
 * @author guoxuewen
 *
 */
class feed_user_all {
    
    /**
     * 
     * @param array $uids
     * @param int $feed_id
     */
    public function run($uid, $feed_id){
        if(empty($uid) || empty($feed_id)){
            if(C('LOG_RECORD')) Log::write('task UserLastLoginTime run error:',  "uid OR feed_id is null", Log::ERR);
            return false;
        }
        
        $mUserVm = ClsFactory::Create("RModel.mUserVm");
        $relation_uids = $mUserVm->getUserAllRelations($uid);
        
        $RM = ClsFactory::Create("RModel.Feed.mZsetUserMy");
        foreach($relation_uids as $uid) {
            $RM->setFeed($uid, time(), $feed_id);
        }
        
        return true;
    }
}