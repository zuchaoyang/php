<?php

/**
 * 在线用户集合封装
 * @author lnczx
 */


class mSetLiveUser {
    protected $_dSetLiveUser = null;
    
    public function __construct() {
        import('RData.Common.dSetLiveUser');
        $this->_dSetLiveUser = new dSetLiveUser();
    }
    
    
    /**
     * 获取在线用户的集合
     */
    public function getLiveUserTest() {
        
        return $this->_dSetLiveUser->getLiveUserTest();
    }       
    
    /**
     * 获取在线用户的集合
     */
    public function isLiveUser($id) {
        return $this->_dSetLiveUser->isLiveUser($id);
    }    

    /**
     * 获取某一组用户与在线用户的集合
     */
    public function getSomeLiveUser($ids) {
        return $this->_dSetLiveUser->getSomeLiveUser($ids);
    }           

    /**
     * 获取在线用户的集合
     */
    public function getLiveUserSet() {
        return $this->_dSetLiveUser->getLiveUserSet();
    }
    
    /**
     * 获取班级在线用户的集合，即在线用户和班级用户的交集
     */
    public function getLiveClassUserSet($id) {
        return $this->_dSetLiveUser->getLiveClassUserSet($id);
    }    
    
    /**
     * 获取用户好友与的集合,即在线用户和用户好友的交集
     */
    public function getLiveUserFriendsSet($id) {
        return $this->_dSetLiveUser->getLiveUserFriendsSet($id);
    }     
    
    /**
     * 添加在线用户
     * @param $account
     
     */    
    function ping($account) {
        if(empty($account)) {
            return false;
        }
        
        return $this->_dSetLiveUser->ping($account);
    }
}
