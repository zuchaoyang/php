<?php
import('RData.RedisFeedKey');

class dStringNotice extends rBaseString {

    // REDIS_USER_MAX_LIFE  define in  config/define.php
    protected $expire_time = REDIS_USER_MAX_LIFE;    
    
    /**
     * 获取相应的Key
     * @param $id = client_account
     */
    public function getKey($id) {
        if(empty($id)) {
            return false;
        }
        
        return RedisFeedKey::getUserNoticeMsgKey($id);
    }    
    
}