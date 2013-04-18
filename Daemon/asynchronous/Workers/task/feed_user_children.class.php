<?php

/**
 * 孩子动态
 * @author guoxuewen
 *
 */
class feed_user_children {

    /**
     * 
     * @param array $uid
     * @param int $feed_id
     */
    public function run($uid, $class_code = null, $feed_id){

        if(empty($uid) || empty($feed_id)) {
            if(C('LOG_RECORD')) Log::write('task feed_user_children run error:',  "uid OR feed_id is null", Log::ERR);
            return false;
        }
        
        $mUserVm = ClsFactory::Create("RModel.mUserVm");
        $user_info = $mUserVm->getUserBaseByUid($uid);

        if(empty($user_info) || $user_info[$uid]['client_type'] != CLIENT_TYPE_STUDENT) {
            return false;
        }

        $mSetClientParent = ClsFactory::Create('RModel.Common.mSetClientParent');
        $parent_uids = $mSetClientParent->getClientParentByUid($uid);

        $RM = ClsFactory::Create("RModel.Feed.mZsetUserChildren");
        foreach($parent_uids as $uid) {
            $RM->setFeed($uid, $feed_id, $feed_id);
        }
        
        return true;
    }
}