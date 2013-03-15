<?php
/**
 * 管理通用的rediskey信息
 * @author Administrator
 * 注明：
 * 1. 因为在不同rdata文件中可能涉及到集合求交集的情况，因此需要key的共享和集中处理
 * 2. 数据库中的int类型表示范围比php的int表示的范围要大，因此格式化整数的时候使用:%s;
 */
class RedisCommonKey {
    /**
     * 获取在线用户
     */
    public static function getLiveUserSetKey() {
        return "live:usr";
    }
    
    /**
     * 获取活跃用户
     */
    public static function getActiveUserSetKey() {
        return "active:usr";
    }    
    
    /**
     * 获取班级学生
     * @param $class_code
     */
    public static function getClassStudentSetKey($class_code) {
        return sprintf("cls:%s:student", $class_code);
    }
    
    /**
     * 获取班级老师
     * @param $class_code
     */
    public static function getClassTeacherSetKey($class_code) {
        return sprintf("cls:%s:teacher", $class_code);
    }
    
    /**
     * 获取班级家长
     * @param $class_code
     */
    public static function getClassFamilySetKey($class_code) {
        return sprintf("cls:%s:family", $class_code);
    }
    
    /**
     * 获取用户孩子
     * @param $class_code
     */
    public static function getUserChildrenSetKey($uid) {
        return sprintf("usr:%s:children", $uid);
    }
    
    /**
     * 获取用户好友
     * @param $class_code
     */
    public static function getUserFriendSetKey($uid) {
        return sprintf("usr:%s:friends", $uid);
    }
    
    /**
     * 获取用户家长
     * @param $class_code
     */
    public static function getUserParentSetKey($uid) {
        return sprintf("usr:%s:parent", $uid);
    }
    
   /**
     * 获取用户对象
     * @param $class_code
     */
    public static function getClientHashKey($uid) {
        return sprintf("usr:%s:obj", $uid);
    }
    
   /**
     * 获取用户对应班级对象
     * @param $class_code
     */
    public static function getClientClassHashKey($uid) {
        return sprintf("usr:%s:client:class", $uid);
    }       
    
   /**
     * 获取班级对象
     * @param $class_code
     */
    public static function getClassHashKey($class_code) {
        return sprintf("cls:%s:obj", $class_code);
    }   

   /**
     * 获取学校对象
     * @param $class_code
     */
    public static function getSchoolHashKey($school_id) {
        return sprintf("school:%s:obj", $school_id);
    }      
    
    /**
     * 获取redis的全部key信息
     */
    public static function getGlobalKeyPre() {
        return "global_keys:";
    }
}