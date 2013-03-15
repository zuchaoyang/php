<?php
/**
 * 用户与班级关系,
 * 1. 基本的get set del 方法封装在rBase HASH 封装基本方法
 * 2. 特殊业务要在本类中扩展编写
 * 
 * @author lnczx
 */
import('RData.RedisCommonKey');

class dHashClientClass extends rBaseHash {
    
    protected $hash_json_fields = true;
    
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
        
        return RedisCommonKey::getClientClassHashKey($id);
    }
        
}
