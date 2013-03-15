<?php
/**
 * Author: lnc<lnczx0915@gmail.com>
 * 功能:UCenter Client
 * 说明:	作为与UCenter通信的接口类，并提供通用的与用户信息有关的方法
 * 
*/

class MoodApi extends ApiController {
    /**
     * 
     * 固定函数
     */
    public function __construct() {
        parent::__construct();
    }    
   
    /**
     * 
     * 固定函数
     */
    public function _initialize(){
		parent::_initialize();        
    }
    
    /**
     * 获取个人说说列表信息
     * @param $client_account
     * @param $offset
     * @param $limit
     */
    public function getPersonMoodList($client_account, $offset = 0, $limit = 10) {
        if(empty($client_account)) {
            return false;
        }
        
        import('@.Control.Api.MoodImpl.PersonMood');
        $PersonMood = new PersonMood();
        
        return $PersonMood->getPersonMoodList($client_account, $offset, $limit);
    }
    
    /**
     * 获取个人说说信息
     * @param $client_account
     * @param $mood_id
     */
    public function getPersonMood($client_account, $mood_id) {
        if(empty($client_account) || empty($mood_id)) {
            return false;
        }
        
        import('@.Control.Api.MoodImpl.PersonMood');
        $PersonMood = new PersonMood();
        
        return $PersonMood->getPersonMood($client_account, $mood_id);
    }
    
    /**
     * 发表一个说说
     * @param $client_account
     * @param $mood_datas
     */
    public function addPersonMood($client_account, $mood_datas) {
        if(empty($client_account) || empty($mood_datas) || !is_array($mood_datas)) {
            return false;
        }
        
        import('@.Control.Api.MoodImpl.PersonMood');
        $PersonMood = new PersonMood();
        
        return $PersonMood->addPersonMood($client_account, $mood_datas);
    }
    
    /**
     * 删除个人说说信息
     * @param $client_account
     * @param $mood_id
     */
    public function delPersonMood($client_account, $mood_id) {
        if(empty($client_account) || empty($mood_id)) {
            return false;
        }
        
        import('@.Control.Api.MoodImpl.PersonMood');
        $PersonMood = new PersonMood();
        
        return $PersonMood->delPersonMood($client_account, $mood_id);
    }
    
    
    /**
     * 创建班级说说
     */
    public function addClassMood($class_code, $mood_datas) {
        if(empty($class_code) || empty($mood_datas)) {
            return false;
        }
        
        import('@.Control.Api.MoodImpl.ClassMood');
        $ClassMood = new ClassMood();
        
        return $ClassMood->addClassMood($class_code, $mood_datas);
    }
    
    /**
     * 获取班级说说信息
     * @param $class_code
     * @param $mood_id
     */
    public function getClassMood($class_code, $mood_id) {
        if(empty($class_code) || empty($mood_id)) {
            return false;
        }
        
        import('@.Control.Api.MoodImpl.ClassMood');
        $ClassMood = new ClassMood();
        
        return $ClassMood->getClassMood($class_code, $mood_id);
    }
    
    /**
     * 删除班级说说
     * @param $class_code
     * @param $mood_id
     */
    public function delClassMood($class_code, $mood_id) {
        if(empty($class_code) || empty($mood_id)) {
            return false;
        }
        
        import('@.Control.Api.MoodImpl.ClassMood');
        $ClassMood = new ClassMood();
        
        return $ClassMood->delClassMood($class_code, $mood_id);
    }
    
    
    /*******************************************************************************
     * 说说的评论管理
     *******************************************************************************/
     
     /**
     * 通过Comment_id获取评论信息
     * @param $comment_ids		mixed	    说说的评论id
     * @return 					mixed    成功：评论组成的数组； 失败：false    
     */
    public function getMoodCommentsById($comment_ids) {
        if(empty($comment_ids)) {
            return false;
        }
        
        import('@.Control.Api.MoodImpl.MoodComments');
        $MoodComments = new MoodComments();
        
        return $MoodComments->getMoodCommentsById($comment_ids);
    }
    
    /**
     * 通过mood_id获取mood_id的评论信息
     * @param $mood_id			int	             说说id
     * @param $where_appends	array    附加过滤条件
     * @param $offset			int      记录的偏移位置
     * @param $limit	        int      获取的记录数
     * @return 					mixed    成功：评论组成的数组； 失败：false    
     */
    public function getMoodCommentsByMoodId($mood_id, $where_appends, $offset = 0, $limit = 10) {
        if(empty($mood_id)) {
            return false;
        }
        
        import('@.Control.Api.MoodImpl.MoodComments');
        $MoodComments = new MoodComments();
        
        return $MoodComments->getMoodCommentsByMoodId($mood_id, $where_appends, $offset, $limit);
    }
    
    /**
     * 添加说说评论信息
     * @param $comment_datas array(
     *    'mood_id',
     *    'up_id',
     *    'content',
     * 	  'client_account',
     *    'add_time',
     *    'level'
     * )
     * @return 成功:comment_id; 失败:false;
     */
    public function addMoodComments($comment_datas) {
        if(empty($comment_datas) || !is_array($comment_datas)) {
            return false;
        }
        
        import('@.Control.Api.MoodImpl.MoodComments');
        $MoodComments = new MoodComments();
        
        return $MoodComments->addMoodComments($comment_datas);
    }
    
    /**
     * 删除说说评论信息
     * @param $comment_id   说说对应的评论id
     */
    public function delMoodComments($comment_id) {
        if(empty($comment_id)) {
            return false;
        }
        
        import('@.Control.Api.MoodImpl.MoodComments');
        $MoodComments = new MoodComments();
        
        return $MoodComments->delMoodComments($comment_id);
    }
}