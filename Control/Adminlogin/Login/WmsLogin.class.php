<?php
include_once 'LoginAbstract.class.php';

class WmsLogin extends LoginAbstract {
    
    protected $app_name = 'WMS';
    protected $success_url = '/Adminbase/Body/index';
    protected $user = array();
    public function login() {
        if($this->isLogined()) {
            $this->successSkip();
            exit;
        }
        
        $this->view->display("Adminbase/login");
    }
    
	/**
     * 检测用户的权限
     * 1. 只有学校管理员能够登录wms
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
        $this->view->display('Adminbase/login');
    }
    
    /**
     * ams用户初始化
     * @param $uid
     */
    protected function initUserByUid($uid){
        $mWmsAccount = ClsFactory::Create("Model.mWmsAccount");
        $userInfo = $mWmsAccount->getWmsAccountByUid($uid);
        if(empty($userInfo)) {
           $this->showErrMsg('用户名或者密码错误!'); 
           exit;
        }
        
        $this->user = $userInfo[$uid];
    }
}