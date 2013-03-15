<?php
include_once 'LoginAbstract.class.php';

class AmsLogin extends LoginAbstract {
    protected $app_name = 'AMS';
    protected $success_url = "/Amscontrol/Index/index";
    protected $user = array();
    
    public function login() {
        if($this->isLogined()) {
            $this->successSkip();
            exit;
        }
        $this->view->display('Amscontrol/amslogin');
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
        $this->view->display('Amscontrol/amslogin');
    }
    
    /**
     * ams用户初始化
     * @param $uid
     */
    protected function initUserByUid($uid){
        $mAmsAccount = ClsFactory::Create("Model.mAmsAccount");
        $userInfo = $mAmsAccount->getAmsAccountByUid($uid);
        if(empty($userInfo)) {
           $this->showErrMsg('用户名或者密码错误!'); 
           exit;
        }
        $this->user = $userInfo[$uid];
        
    }
}