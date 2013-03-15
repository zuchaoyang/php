<?php
abstract class WmsController extends AdminController {
    protected $app_name = 'wms';
    
	/**
     * 获取当前登录用户信息
     */
    protected function getCurrentUser(){
        list($uid, $passwd) = $this->getCookieTokenInfo();
        $mAmsAccount = ClsFactory::Create("Model.mWmsAccount");
        $user_arr = $mAmsAccount->getWmsAccountByUid($uid);
        $user = & $user_arr[$uid];
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
        header('Location:/Adminlogin/Login/login/app_name/wms?url='.urlencode($url));
    }
    
    //用户权限的检测
    protected function checkAccess() {
        //是否检测用户的访问权限
        $is_check = $this->objInput->getInt('check');
        if(empty($is_check)) {
           return true; 
        }
        
        $func_code = $this->objInput->getStr('func_code');
        $access = $this->objInput->getStr('access');
        $access_key = $this->getaccessKey($func_code);
        if(empty($func_code) || empty($access) || $access !=$access_key) {
            exit("没有权限查看此内容");
        }
    }
    
    private function getaccessKey($func_code) {
        return md5($func_code.'WMS'. time(date('Y-m-d')) . $this->user['wms_account']);
    }
}
