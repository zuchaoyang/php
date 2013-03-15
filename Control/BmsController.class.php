<?php

class BmsController extends AdminController {
    protected $app_name = 'bms';
	/**
     * 获取当前登录用户信息
     */
    protected function getCurrentUser(){
        list($uid, $passwd) = $this->getCookieTokenInfo();
        
        $mBmsAccount = ClsFactory::Create("Model.mBmsAccount");
        $user_arr = $mBmsAccount->getUserInfoByUid($uid);
        $user = & $user_arr[$uid];
        //权限检测
        return !empty($user) ? $user : array(); 
    }
	
	/**
     * 跳转到Ams的登录页
     * @param $url
     */
    public function to_login($url = null) {
        if(is_null($url)) {
            $url = 'http://'.$_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
        }
        header('Location:/Adminlogin/Login/login/app_name/bms?url='.urlencode($url));
    }
}
