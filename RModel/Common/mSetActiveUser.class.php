<?php

/**
 * 活跃用户集合封装
 * @author lnczx
 */


class mSetActiveUser {
    protected $_dSetActiveUser = null;
    
    public function __construct() {
        import('RData.Common.dSetActiveUser');
        $this->_dSetActiveUser = new dSetActiveUser();
    }

    /**
     * 获取活跃用户的集合
     */
    public function getActiveUserSet() {
        return $this->_dSetActiveUser->getActiveUserSet();
    }
    
    /**
     * 添加活跃用户
     * @param $account
     
     */    
    function addActiveUser($uid) {
        if(empty($uid)) {
            return false;
        }
        
        return $this->_dSetActiveUser->addActiveUser($uid);
    }
}
