<?php
include_once 'LoginAbstract.class.php';

class BmsLogin extends LoginAbstract {
    
    protected $app_name = 'BMS';
    protected $success_url = '/Basecontrol/Index/index';
    protected $user = array();
    
    public function login() {
        if($this->isLogined()) {
            $this->successSkip();
            exit;
        }
        $this->view->display("Basecontrol/baselogin");
    }
    
	/**
     * 检测用户的权限
     * 1. 只有学校管理员能够登录ams
     */
    protected function checkUserAccess() {
        if(empty($this->user)) {
            return false;
        }
	    
        return true;
    }
    
	/**
     * 显示错误信息
     * @param $msg
     */
    protected function showErrMsg($msg) {
        $this->view->assign('message', $msg);
        $this->view->display('Basecontrol/baselogin');
    }
    
    /**
     * ams用户初始化
     * @param $uid
     */
    protected function initUserByUid($uid){
        $mBmsAccount = ClsFactory::Create("Model.mBmsAccount");
        $UserInfo = $mBmsAccount->getUserInfoByUid($uid);
        if(empty($UserInfo)) {
           $this->showErrMsg('用户名或者密码错误!'); 
           exit;
        }
        $this->user = $UserInfo[$uid];
    }
    
}