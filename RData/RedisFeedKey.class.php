<?php

/**
 * 管理动态信息相关的key
 * @author Administrator
 */
class RedisFeedKey {
    public static function getFeedAsyncTaskQueueKey() {
        return 'feed:queue';
    }
    
    /**
     * 获取班级动态信息的key
     * @param $class_code
     */
    public static function getClassFeedAllZsetKey($class_code) {
        return sprintf('feed:cls:%s', $class_code);
    }
    
    /**
     * 获取用户的好友的相册动态的key
     * @param $uid
     */
    public static function getAblumAllFeedZsetKey($uid) {
        return sprintf('feed:album:%s', $uid);
    }
    
	/**
     * 获取用户的好友动态的key
     * @param $uid
     */
    public static function getUserFriendZsetKey($uid) {
        return sprintf('feed:usr:%s:friends', $uid);
    }
    
    /**
     * 获取班级相册动态的key
     * @param $uid
     */
    public static function getClassAblumFeedZsetKey($uid) {
        return sprintf('feed:cls:%s:album', $uid);
    }    
    
    /**
     * 获取用户的孩子动态信息的key
     * @param $uid
     */
    public static function getUserChildFeedZsetKey($uid) {
        return sprintf('feed:usr:%s:children', $uid);
    }
    
    /**
     * 获取用户的全部动态的key
     * @param $uid
     */
    public static function getUserFeedAllZsetKey($uid) {
        return sprintf('feed:usr:%s:all', $uid);
    }
    
    /**
     * 获取与我相关的动态信息的key
     * @param $uid
     */
    public static function getUserMyFeedZsetKey($uid) {
        return sprintf('feed:usr:%s:my', $uid);
    }
    
    /**
     * 获取与我相关的班级作业消息的key
     * @param unknown_type $uid
     */
    public static function getUserHomeworkMsgKey($uid) {
        return sprintf("msg:%s:homework", $uid);
    }
    
	/**
     * 获取与我相关的班级公告消息的key
     * @param unknown_type $uid
     */
    public static function getUserNoticeMsgKey($uid) {
        return sprintf("msg:%s:notice", $uid);
    }
    
	/**
     * 获取与我相关的班级成绩消息的key
     * @param unknown_type $uid
     */
    public static function getUserExamMsgKey($uid) {
        return sprintf("msg:%s:exam", $uid);
    }
    
    
	/**
     * 获取与我相关的好友请求消息的key
     * @param unknown_type $uid
     */
    public static function getUserReqMsgKey($uid) {
        return sprintf("msg:%s:req", $uid);
    }
    
	/**
     * 获取与我相关的好友请求回复消息的key
     * @param unknown_type $uid
     */
    public static function getUserResMsgKey($uid) {
        return sprintf("msg:%s:res", $uid);
    }
    
	/**
     * 获取与我相关的评论消息的key
     * @param unknown_type $uid
     */
    public static function getUserCommentsMsgKey($uid) {
        return sprintf("msg:%s:comments", $uid);
    }
    
	/**
     * 获取与我相关的评论消息的key
     * @param unknown_type $uid
     */
    public static function getUserPrivateMsgKey($uid) {
        return sprintf("msg:%s:privatemsg", $uid);
    }
    
}