<?php

/**
 * 个人好友列表相册动态
 * @author guoxuewen
 *
 */
class feed_user_album {

    /**
     * 
     * @param array $uids  好友列表
     * @param int $feed_id
     */
    public function run($uid, $class_code=null, $feed_id){
        if(empty($uid) || empty($feed_id)) {
            if(C('LOG_RECORD')) Log::write('task UserLastLoginTime run error:',  "uids OR feed_id is null", Log::ERR);
            return false;
        }
        
        $mSetClientFriends = ClsFactory::Create('RModel.Common.mSetClientFriends');
	    $friend_uids = $mSetClientFriends->getClientFriendsByUid($uid);

        $RM = ClsFactory::Create("RModel.Feed.mZsetAblumAll");

        foreach($friend_uids as $friend_uid) {
            $RM->setFeed($friend_uid, $feed_id, $feed_id);
        }
        
        return true;
    }
}