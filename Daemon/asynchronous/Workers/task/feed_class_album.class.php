<?php

/**
 * 班级相册动态
 * @author guoxuewen
 *
 */
class feed_class_album{
    
    /**
     * 
     * @param int $class_code
     * @param int $feed_id
     */
    public function run($uid = null,$class_code, $feed_id) {
        if(empty($class_code) || empty($feed_id)) {
            if(C('LOG_RECORD')) Log::write('task UserLastLoginTime run error:',  "uids OR feed_id is null", Log::ERR);
            return false; 
        }
        
        $RM = ClsFactory::Create("RModel.Feed.mZsetClassAlbum");
        return $RM->setFeed($class_code, $feed_id, $feed_id);
    }
}