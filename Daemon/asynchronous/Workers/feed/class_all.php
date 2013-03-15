<?php

/**
 * 班级全部动态
 * @author guoxuewen
 *
 */
class class_all {
    
    /**
     * 
     * @param int $class_code
     * @param int $feed_id
     */
    public function run($class_code, $feed_id){
        if (empty($class_code) || empty($feed_id)) {
            if(C('LOG_RECORD')) Log::write('task UserLastLoginTime run error:',  "class_code OR feed_id is null", Log::ERR);
            return false;
        }

        $RM = ClsFactory::Create("RModel.Feed.mZsetClassAll");

        return $RM->setFeed($class_code, time(), $feed_id);
    }
}