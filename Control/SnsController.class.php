<?php
class SnsController extends FrontController {
	/*
	 * 构造函数
	 * 用于smarty引入头文件
	 */
	public function __construct() {
		parent::__construct();
		import("@.Control.Sns.Insert.InsertSnsFunc", null,".php");
	}
	
	/**
	 * 初始化Sns当前登录的用户信息
	 */
	protected function initCurrentUser() {
	    $uid = $this->getCookieAccount();
        
        $mUser = ClsFactory::Create('RModel.mUserVm');
        $userlist = $mUser->getUserByUid($uid);
        
        $this->user = & $userlist[$uid];
	}
	
	/**
	 * 获取并检查当前用户的当前班级
	 * @param $class_code 校验班级class_code是否正确
	 */
    protected function checkoutClassCode($class_code) {
        
        $class_code_list = $this->user['class_info'];
        if(empty($class_code_list)) {
           return false; 
        }

        if(!isset($class_code_list[$class_code])){
            foreach($class_code_list as $classcode => $class_info) {
                if($this->user["client_account"] == $class_info["headteacher_account"]){
                    $class_code = $classcode;
                    break;
                }
            }
        }
        
        return !in_array($class_code, $class_code_list) ? $class_code : reset($class_code_list);
    }
    
	/**
     * 判断用户是否是对应班级的班主任
     * @param $class_code
     */
    protected function isClassAdminTeacher($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        //获取用户当前的班级对应关系
        $current_client_class = array();
        foreach((array)$this->user['client_class'] as $client_class) {
            if($client_class['class_code'] == $class_code) {
                $current_client_class = $client_class;
                break;
            }
        }
        //班级管理员的角色设置值
        $class_admin_list = array(
            TEACHER_CLASS_ROLE_CLASSADMIN,
            TEACHER_CLASS_ROLE_CLASSBOTH,
        );
        
        return in_array($current_client_class['teacher_class_role'], $class_admin_list) ? true : false; 
    }
    
    /**
     * 判断用户是否是对应班级的管理员
     * @param $class_code
     */
    protected function isClassAdmin($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        //获取用户当前的班级对应关系
        $current_client_class = array();
        foreach((array)$this->user['client_class'] as $client_class) {
            if($client_class['class_code'] == $class_code) {
                $current_client_class = $client_class;
                break;
            }
        }
        //班级管理员的角色设置值
        $class_admin_list = array(
            TEACHER_CLASS_ROLE_CLASSADMIN,
            TEACHER_CLASS_ROLE_CLASSBOTH,
        );
        
        $teacher_class_role = $current_client_class['teacher_class_role'];
        
        return in_array($teacher_class_role, $class_admin_list) || $current_client_class['class_admin'] == IS_CLASS_ADMIN;
    }
    
	/*
	 * 获取统一注销地址
	 */
	protected function getLogoutUrl() {
	   return $this->uc_client->get_uc_logout_url($this->appName);
	}	
	
    protected function getSuccessTplFile() {
        return WEB_ROOT_DIR . "/View/Template/Public/wmw_success_tips.html";
    }
    
    protected function getErrorTplFile() {
        return WEB_ROOT_DIR . "/View/Template/Public/wmw_error_tips.html";
    }
    
    protected function append_success_assign() {
        $this->assign('pathImg', IMG_SERVER."/Public/images/new/success.gif");
    }
    
    protected function append_error_assign() {
        $this->assign('pathImg', IMG_SERVER."/Public/images/new/error.jpg");
    }
    
}
