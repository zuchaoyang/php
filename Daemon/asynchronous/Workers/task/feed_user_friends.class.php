<?php
/**
 * 朋友动态
 * @author guoxuewen
 *
 */
class feed_user_friends {

    /**
     * 
     * @param bigint $uids  
     * @param int $feed_id
     */
    public function run($uid, $class_code=null, $feed_id){
        if(empty($uid) || empty($feed_id)) {
            if(C('LOG_RECORD')) Log::write('task UserLastLoginTime run error:',  "uid OR feed_id is null", Log::ERR);
            return false;
        }
        
        $mSetClientFriends = ClsFactory::Create('RModel.Common.mSetClientFriends');
	    $friend_uids = $mSetClientFriends->getClientFriendsByUid($uid);

        $RM = ClsFactory::Create("RModel.Feed.mZsetUserFriend");
        $RM1 = ClsFactory::Create("RModel.Feed.mZsetUserAll");
        
        foreach($friend_uids as $uid) {
            $RM->setFeed($uid, $feed_id, $feed_id);
            $RM1->setFeed($uid, $feed_id, $feed_id);
        }
        
        return true;
    }
}