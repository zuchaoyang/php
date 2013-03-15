<?php
import('RData.RedisFeedKey');

class dZsetUserChildren extends rBaseZset {
    
    protected $zset_max_size = 100;
    
    /**
     * 获取相应的Key
     * @param $id = client_account
     */
    public function getKey($id) {
        if(empty($id)) {
            return false;
        }
        
        return RedisFeedKey::getUserChildFeedZsetKey($id);
    }
}