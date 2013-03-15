<?php

/**
 * 活跃用户的集合
 * @author lnczx
 */

import('RData.RedisCommonKey');

class dSetActiveUser extends rBaseSet {
    
    /**
     * 获取活跃用户的集合
     */
    public function getActiveUserSet() {
                
        $keys = $this->getActiveUserKey();
        
        return $this->sUnion($keys);
    }

    /**
     * 获取活跃用户的集合Key
     */
    private function getActiveUserKey($date = ACTIVE_DATE) {
        
        // 默认取前7天的数据
        if (empty($date) || $date <=0 ) {
            $date = ACTIVE_DATE;
        }
        
        $redis_key = RedisCommonKey::getActiveUserSetKey();
        
        /* redis keys to union, based on last $date 
         * key = action:user:2013:01:01
         *       action:user:2013:01:02
         *       ..
         *       action:user:2013:01:07
         * */
        $keys = array();
        $i = 0;
        for($i = 0 ; $i <= $date; $i++) {
            $cur_time = time() - 3600 * 24 * $i;
            $key_part = date("Y:m:d", $cur_time);
            $keys[] = "$redis_key:$key_part"; // define the key
        }
         
        return $keys;
    }

    /**
     * 添加活跃用户
     * @param $account
       e.g. key = live:user:10:1
     */    
    function addActiveUser($id) {
        /* current y:m:d to make up the redis key */
        $now  = time();
        $redis_key = RedisCommonKey::getActiveUserSetKey();
        $key = $redis_key . ':' . date("Y:m:d");;

        $this->sAdd($key, $id); // add the user to the set
        $ttl = $this->ttl($key) ; // check if key has an expire
        if($ttl == -1) { // if it do not have, set it to ACTIVE_MINUTE + 1
            // ACTIVE_MINUTE  define in  config/define.php
            $this->expireAt($key, $now + 3600 * 24 * (ACTIVE_MINUTE + 1));
        }
        return true ;
    }
}
