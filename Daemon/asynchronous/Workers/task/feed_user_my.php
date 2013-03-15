<?php

/**
 * 与我相关动态
 * @author guoxuewen
 *
 */
class feed_user_my {
    
    /**
     * 
     * @param bigint $uid 
     * @param int $feed_id
     */
    public function run($uid, $feed_id){
        if (empty($uid) || empty($feed_id)) {
            if(C('LOG_RECORD')) Log::write('task UserLastLoginTime run error:',  "uid OR feed_id is null", Log::ERR);
            return false;
        }
        
        $RM = ClsFactory::Create("RModel.Feed.mZsetUserMy");
        return $RM->setFeed($uid, time(), $feed_id);
    }
}