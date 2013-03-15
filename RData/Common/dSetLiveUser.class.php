<?php

/**
 * 在线用户的集合
 * @author lnczx
 */

import('RData.RedisCommonKey');

class dSetLiveUser extends rBaseSet {
    
    
    /**
     * 获取在线用户的集合测试接口
     */
//    public function getLiveUserTest() {
//        
//        $this->delete('test');  
//        $this->sadd("test","111");  
//        $this->sadd("test","222");  
//        $this->sadd("test","333");  
//        $this->sadd("test1","111");  
//        $this->sadd("test1","444");  
//        var_dump($this->sUnionstore('new', implode(",", array("test","test1")) ));  //结果：int(4)
//        $return = call_user_func_array(array($this, "sUnionstore"), array("new", "test", "test1"));
//        $return = call_user_func_array(array($this, "sInter"), array("new", "test1", "test"));
//        print_r($return);  //结果:Array ( [0] => 111 [1] => 222 [2] => 333 [3] => 444 )  
//        
//    }    
    
    /**
     * 获取在线用户的集合
     */
    public function getLiveUserSet() {
                
        $keys = $this->getLiveUserKey();
        
        return $this->sUnion($keys);
    }
    
    
    /**
     * 获取某个用户是否在线
     */
    public function isLiveUser($id) {
        
        if (empty($id)) {
            return false;
        }
        
        $live_keys = $this->getLiveUserKey();
        $online_usr_tmp_key = 'online:usr:'.time();
        array_unshift($live_keys, $online_usr_tmp_key);
        call_user_func_array(array($this, "sUnionstore"), $live_keys);        
        
        $is_exist = $this->sIsMember($online_usr_tmp_key, $id);
        
        //最后删除掉临时key
        $this->delete($online_usr_tmp_key);          
        
        return $is_exist ? $id : false;
    }    
    
    /**
     * 获取一组用户与在线用户交集
     */
    public function getSomeLiveUser($ids) {
        
        if (empty($ids)) {
            return false;
        }
        
        $ids = (array)$ids;
        
        $live_keys = $this->getLiveUserKey();
        $online_usr_tmp_key = 'online:usr:'.time();
        array_unshift($live_keys, $online_usr_tmp_key);
        call_user_func_array(array($this, "sUnionstore"), $live_keys);
        
        //将ids加入临时set方法
        $tmp_key = 'onlie:someusr:'.time();
        $pipe = $this->multi(Redis::PIPELINE);
        foreach($ids as $id) {
            $pipe->sAdd($tmp_key, $id);
        }
        
        $replies = $pipe->exec();

        $result = call_user_func_array(array($this, "sInter"), array($tmp_key, $online_usr_tmp_key));
        
        //最后删除掉临时key
        $this->delete($online_usr_tmp_key);               
        $this->delete($tmp_key);
        
        return $result;
    }       
    
    
        
    /**
     * 获取班级在线用户的集合，即在线用户和班级用户的交集
     * params $id = class_code
     */
    public function getLiveClassUserSet($id) {
        
        if (empty($id)) {
            return false;
        }
        
        $keys = array();
        $keys[] = $class_family_key = RedisCommonKey::getClassFamilySetKey($id);
        $keys[] = $class_student_key = RedisCommonKey::getClassStudentSetKey($id);
        $keys[] = $class_teacher_key = RedisCommonKey::getClassTeacherSetKey($id);
        
        //采用先获取在线用户集合，并存入online:user:time()中
        $live_keys = $this->getLiveUserKey();
        $online_usr_tmp_key = 'online:usr:'.time();
        array_unshift($live_keys, $online_usr_tmp_key);
        call_user_func_array(array($this, "sUnionstore"), $live_keys);

        
        //在将班级所有成员，存入cls:usr:all:time()中
        $cls_usr_all_tmp_key = 'cls:usr:all:'.time();
        array_unshift($keys, $cls_usr_all_tmp_key);
        call_user_func_array(array($this, "sUnionstore"), $keys);

        
        
        //再进行取并集操作
        $result = call_user_func_array(array($this, "sInter"), array($cls_usr_all_tmp_key, $online_usr_tmp_key));
        
        //最后删除掉临时key
        $this->delete($online_usr_tmp_key);
        $this->delete($cls_usr_all_tmp_key);
               
        return  $result;
    }
    
    /**
     * 获取用户好友与的集合,即在线用户和用户好友的交集
     * params $id = class_code
     */
    public function getLiveUserFriendsSet($id) {
        
        if (empty($id)) {
            return false;
        }
        
        $keys = $this->getLiveUserKey();
        
        $keys[] = $client_friends_key = RedisCommonKey::getUserFriendSetKey($id);
        $keys[] = $client_children_key = RedisCommonKey::getUserChildrenSetKey($id);
        $keys[] = $client_parent_key = RedisCommonKey::getUserParentSetKey($id);
        
        
        //采用先获取在线用户集合，并存入online:user:time()中
        $live_keys = $this->getLiveUserKey();
        $online_usr_tmp_key = 'online:usr:'.time();
        array_unshift($live_keys, $online_usr_tmp_key);
        call_user_func_array(array($this, "sUnionstore"), $live_keys);

        
        //在将用户好友及家庭所有成员，存入cls:usr:all:time()中
        $usr_friends_all_tmp_key = 'usr:friends:all:'.time();
        array_unshift($keys, $usr_friends_all_tmp_key);
        call_user_func_array(array($this, "sUnionstore"), $keys);

        
        
        //再进行取并集操作
        $result = call_user_func_array(array($this, "sInter"), array($usr_friends_all_tmp_key, $online_usr_tmp_key));
        
        //最后删除掉临时key
        $this->delete($online_usr_tmp_key);
        $this->delete($usr_friends_all_tmp_key);        
        
        
        return  $result;
    }    

    /**
     * 获取在线用户的集合Key
     */
    private function getLiveUserKey($minute = ACTIVE_MINUTE) {
        
        // 默认取10分钟数据
        if (empty($minute) || $minute <=0 ) {
            $minute = ACTIVE_MINUTE;
        }
        
        $redis_key = RedisCommonKey::getLiveUserSetKey();
        
		/* current hour and minute */
        $now = time();
        $min = date("i",$now);
        $hor = date("G",$now);

        /* redis keys to union, based on last $minutes 
         * key = live:user:10:10
         *       live:user:10:09
         *       ..
         *       live:user:10:00
         * */
        $keys = array();
        $set_hor = $hor;
        for($i = 0 ; $i <= $minute; $i++) {
            $cur_time = time() - 60* $i;
            $min = date("i", $cur_time);
            $hor = date("G",$cur_time);
            $keys[] = "$redis_key:$hor:$min"; // define the key
        }
         
        return $keys;
    }    
    
        
    /**
     * 添加在线用户
     * @param $account
       e.g. key = live:user:10:1
     */    
    function ping($id) {
        /* current hour:minute to make up the redis key */
        $now  = time();
        $min  = date("G:i",$now);
        $redis_key = RedisCommonKey::getLiveUserSetKey();
        $key = $redis_key . ':' . $min;

        $this->sAdd($key, $id); // add the user to the set
        $ttl = $this->ttl($key) ; // check if key has an expire
        if($ttl == -1) { // if it do not have, set it to ACTIVE_MINUTE + 1
            // ACTIVE_MINUTE  define in  config/define.php
            $this->expireAt($key, $now + (ACTIVE_MINUTE + 1) * 60);
        }
        
        return true ;
    }    
    
}
