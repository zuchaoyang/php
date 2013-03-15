<?php

/**
 * 用户全部动态
 * @author guoxuewen
 *
 */
class user_all {
    
    /**
     * 
     * @param array $uids
     * @param int $feed_id
     */
    public function run($uids, $feed_id){
        if(empty($uids) || empty($feed_id)){
            if(C('LOG_RECORD')) Log::write('task UserLastLoginTime run error:',  "uids OR feed_id is null", Log::ERR);
            return false;
        }
        
        $uids = array_unique((array)$uids);
        
        $RM = ClsFactory::Create("RModel.Feed.mZsetUserMy");
        foreach($uids as $uid) {
            $RM->setFeed($uid, time(), $feed_id);
        }
        
        return true;
    }
}