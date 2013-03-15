<?php
/*
 * 关于班级成员的api
 * 调用路径 /Api/Class/Member/{stu,teacher,parents}/class_code/696
 */
class MemberApi extends ApiController {
   /**
     * 固定函数
     */
    public function __construct() {
        parent::__construct();
    }    
    public function _initialize(){
		parent::_initialize();        
    }	    

    
    public function checkFunc() {
//        dump($this->getStuList(696));
//        dump($this->getTeacherList(696));
//        dump($this->geParentsList(696));
//        dump($this->geMemberList(696));
    }
	/**
	 * 获取班级的学生列表
	 * 
	 * @param $class_code 班级id
	 * @return $stu_list 会员详情列表
	 */
    public function getStuList($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        import('@.Control.Api.Class.MemberImpl.Comment');
        $comObj = new Comment();
        $sutdent_list = $comObj->getClassMember($class_code, CLIENT_TYPE_STUDENT);
        
        return !empty($sutdent_list) ? $sutdent_list : false;
    }
    
	/**
	 * 获取班级的老师列表
	 * 
	 * @param $class_code 班级id
	 * @return teacher_list 班级老师列表
	 */
    public function getTeacherList($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        import('@.Control.Api.Class.MemberImpl.Comment');
        $comObj = new Comment();
        $teacher_list = $comObj->getClassMember($class_code, CLIENT_TYPE_TEACHER);
        
        return !empty($teacher_list) ? $teacher_list : false;
    }
    
	/**
	 * 获取班级学生的家长列表
	 * 
	 * @param  $class_code 班级id
	 * @return $parents_list 家长列表
	 */
    public function geParentsList($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        import('@.Control.Api.Class.MemberImpl.Comment');
        $comObj = new Comment();
        $parents_list = $comObj->getClassMember($class_code, CLIENT_TYPE_FAMILY);
        
        return !empty($parents_list) ? $parents_list : false;
    }
    
    
	/**
	 * 获取班级所有成员列表
	 * 
	 * @param $class_code 班级id
	 * @return $parents 家长列表
	 */
    public function geMemberList($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        import('@.Control.Api.Class.MemberImpl.Comment');
        $comObj = new Comment();
        $member_list = $comObj->getClassMember($class_code);
        
        return !empty($member_list) ? $member_list : false;
    }
}
