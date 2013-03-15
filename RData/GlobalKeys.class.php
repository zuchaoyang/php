<?php
import('RData.RedisCommonKey');

/**
 * redis中的所有key的维护，支持的key维护量大概在 10,000,000左右(单个集合10w左右)
 * @author Administrator
 */
class GlobalKeys extends rBase {
    /**
     * 添加班级的学生集合信息
     * @param $class_code
     * @param $student_accounts
     */
    public function addKey($keys) {
        if(empty($keys)) {
            return false;
        }
        
        $keys = array_unique((array)$keys);
        
        $pipe = $this->multi(Redis::PIPELINE);
        foreach($keys as $key) {
            $redis_key = $this->getRedisKey($key);
            $pipe->sAdd($redis_key, $key);
        }
        $replies = $pipe->exec();
        $add_nums = $this->getPipeSuccessNums($replies);
        
        return $add_nums ? $add_nums : false;
    }
    
	/**
     * 删除班级集合中的数据
     * @param $class_code
     * @param $family_accounts
     */
    public function delKey($keys) {
        if(empty($keys)) {
            return false;
        }
        
        $keys = array_unique((array)$keys);
        
        $pipe = $this->multi(Redis::PIPELINE);
        foreach($keys as $key) {
            $redis_key = $this->getRedisKey($key);
            $pipe->sRem($redis_key, $key);
        }
        $replies = $pipe->exec();
        $delete_nums = $this->getPipeSuccessNums($replies);
        
        return $delete_nums ? $delete_nums : false;
    }
    
   /**
     * 判断家长对应的集合是否存在
     * @param $class_code
     */
    public function isExists($key) {
        
        if(empty($key)) {
            return false;
        }
        
        $redis_key = $this->getRedisKey($key);
        return $this->sIsMember($redis_key, $key);
    }
    
    /**
     * 获取对应的key在redis中的保存key
     * @param $key
     */
    private function getRedisKey($key) {
        $redis_key_pre = RedisCommonKey::getGlobalKeyPre();
        $hash = $this->hash($key);
        
        return $redis_key_pre . $hash;
    }
    
    /**
     * 基于time33的离散均匀的散列算法
     * --原因: 如果将redis中所有的key列表保存在一个集合中，会导致集合过大从而影响数据的查询效率，二次散列可以减小查找的压力
     * --目的：将redis所有的key的维护散列到不同的集合中去，提高查询效率。
     * --注意: 针对于所有key的操作只有添加和查找，因此这样做的代价不高,这种做法要在合适的应用场景下运用
     * @param $str	string 需要散列的key值
     */
    private function hash($str) {
        $hash = 0;
        for($i = 0, $len = strlen($str); $i < $len; $i++) {
            $hash = (int)(($hash<<5) + $hash + ord($str{$i})) & 0x7fffffff;
        }
        
        return $hash % 1000;
    }
}
