<?php

/**
 * 用户对应的孩子集合,
 * 1. 基本的get set del 方法封装在rBase Set 封装基本方法
 * 2. 特殊业务要在本类中扩展编写
 * 
 * @author lnczx
 */

import('RData.RedisCommonKey');

class dSetClientChildren extends rBaseSet {
    
    // REDIS_USER_MAX_LIFE  define in  config/define.php
    protected $expire_time = REDIS_USER_MAX_LIFE;
    
    /**
     * 获取相应的Key
     * @param $id
     */
    public function getKey($id) {
        if(empty($id)) {
            return false;
        }
        
        return RedisCommonKey::getUserChildrenSetKey($id);
    }      
        
}
