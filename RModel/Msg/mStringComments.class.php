<?php
class mStringComments {
    private $_dStringComments = null;
    
    public function __construct() {
        $this->_dStringComments = ClsFactory::Create("RData.Msg.dStringComments");
    }

	/**
     * 得到关于$uid的未查看的评论消息数量
     * @param bigint $uid
     */
    public function getMsg($uid){
        if(empty($uid)) {
            return false;
        }
        
        return $this->_dStringComments->stringGet($uid);
    }

    /**
     * 添加关于$uid的未查看的评论消息数量
     * @param bigint $uid
     * @param int $num
     */
    public function setMsg($uid, $value){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringComments->stringGet($uid, $value);
    }
    
    /**
     * 清除关于$uid的未查看的评论消息
     * @param bigint $uid
     */
    public function clearMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringComments->keyDel($uid);
    }
    
    /**
     * 关于$uid的未查看的评论消息+1
     * @param bigint $uid
     */
    public function incrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringComments->stringIncr($uids);
    }    
    
    /**
     * 关于$uid的未查看的评论消息-1
     * @param bigint $uid
     */
    public function decrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringComments->stringDecr($uids);
    }

	/**
     * 推送消息to uid
     * @param $uid
     */
    public function publishMsg($id, $msg_type = '') {
        if(empty($id) || empty($msg_type) ){
            return false;
        }
        
        $to_accounts = $this->getToAccount($id);
        $mLiveUsr = ClsFactory::Create('RModel.Common.mSetLiveUser');
        $send_accounts = $mLiveUsr->getSomeLiveUser($to_accounts);        
        
        if ($send_accounts) {
            $this->_dStringComments->stringPublic($send_accounts, $msg_type);
        }
        
        if(!empty($to_accounts)) {
            $this->incrMsg($to_accounts);
        }
        
        return true;
    }
    
    private function getToAccount($comments_id){
    
        if(empty($comments_id)){
            return false;
        }         
        $Comments_M = ClsFactory::Create('Model.Feed.mFeedComments');
        $Comments_list = $Comments_M->getFeedCommentsById($comments_id);
        
        if(!empty($Comments_list)) {
            $Comments_list = reset($Comments_list);
            $Feed_M = ClsFactory::Create('Model.Feed.mFeed');
            if($Comments_list['level'] == 1){
                $feed_info = $Feed_M->getFeedById($Comments_list['feed_id']);
                $feed_info = reset($feed_info);
                $to_account[$feed_info['add_account']] = $feed_info['add_account'];
            }else{
                $Comments_list = $Comments_M->getFeedCommentsById($Comments_list['up_id']);
                $Comments_list = reset($Comments_list);
                $to_account[$Comments_list['client_account']] = $Comments_list['client_account'];
            }
        }
        
        return !empty($to_account) ? $to_account : false;
    }
    
    
}