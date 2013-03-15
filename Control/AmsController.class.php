<?php

class AmsController extends AdminController {
    
    public $user = array();
    protected $app_name = 'ams';
    protected $is_school = false;
    
	/*
	 * 构造函数
	 * 用于smarty引入头文件
	 */
	public function __construct() {
		parent::__construct();
		import("@.Control.Insert.InsertAmsFunc", null, ".php");
	}
	
    
    /**
     * 获取当前登录用户信息
     */
    protected function getCurrentUser(){
        list($uid, $passwd) = $this->getCookieTokenInfo();
        
        $mAmsAccount = ClsFactory::Create("Model.mAmsAccount");
        $user_arr = $mAmsAccount->getAmsAccountByUid($uid);
        $user = & $user_arr[$uid];
        //权限检测
        if($this->is_school === true) {
            $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
            $schoolInfo = $mSchoolInfo->getSchoolInfoByNetManagerAccount($user['ams_account']);
            $user['schoolinfo'] = current($schoolInfo[$user['ams_account']]);
        }
        $this->user = !empty($user) ? $user : array();
        return $this->user;
    }
    
	/**
     * 跳转到Ams的登录页
     * @param $url
     */
    public function to_login($url = null) {
        if(is_null($url)) {
            $url = 'http://'.$_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
        }
        header('Location:/Adminlogin/Login/login/app_name/ams?url='.urlencode($url));
    }
}