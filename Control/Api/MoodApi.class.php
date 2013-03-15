<?php
/**
 * Author: lnc<lnczx0915@gmail.com>
 * 功能:UCenter Client
 * 说明:	作为与UCenter通信的接口类，并提供通用的与用户信息有关的方法
 * 
*/

class MoodApi extends ApiController {
    private $ClassMood;
    private $PersonMood;
    
    /**
     * 
     * 固定函数
     */
    public function __construct() {
        parent::__construct();
        
        import('@.Control.Api.MoodImpl.ClassMood');
        $this->ClassMood = new ClassMood();
        import('@.Control.Api.MoodImpl.PersonMood');
        $this->PersonMood = new PersonMood();
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
        
        return $this->PersonMood->getPersonMoodList($client_account, $offset, $limit);
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
        
        return $this->PersonMood->getPersonMood($client_account, $mood_id);
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
        
        return $this->PersonMood->addPersonMood($client_account, $mood_datas);
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
        
        return $this->PersonMood->delPersonMood($client_account, $mood_id);
    }
    
    
    /**
     * 创建班级说说
     */
    public function addClassMood($class_code, $mood_datas) {
        if(empty($class_code) || empty($mood_datas)) {
            return false;
        }
        
        return $this->ClassMood->addClassMood($class_code, $mood_datas);
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
        
        return $this->ClassMood->getClassMood($class_code, $mood_id);
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
        
        return $this->ClassMood->delClassMood($class_code, $mood_id);
    }
    
}