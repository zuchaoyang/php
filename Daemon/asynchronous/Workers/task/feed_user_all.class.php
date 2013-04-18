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
    public function run($uid, $class_code = null, $feed_id){

        if(empty($uid) || empty($feed_id)){
            if(C('LOG_RECORD')) Log::write('task UserLastLoginTime run error:',  "uid OR feed_id is null", Log::ERR);
            return false;
        }

        $RM = ClsFactory::Create("RModel.Feed.mZsetUserAll");
        $RM->setFeed($uid, $feed_id, $feed_id);

        return true;
    }
}