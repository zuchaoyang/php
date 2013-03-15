<?php
abstract class AdminController extends Controller {
    
	protected $app_name = "";
	
	/*
	 * 构造函数
	 * 用于smarty引入头文件
	 */
	public function __construct() {
		parent::__construct();
	}
	
	public function _initialize() {
	    $this->_initApp();
	}
	protected function _initApp() {
	    //获取当前用户信息
	    $this->user = $this->getCurrentUser();
	    //权限用户是否登录
	    if(empty($this->user)) {
	        $this->to_login();
	        exit;
	    }
	    
	    $this->checkAccess();
	}
	
    //获取cookie中的token
	protected function getCookieTokenInfo() {
	    $token_name = $this->getTokenName();
		$token = $_COOKIE[$token_name];
		$token_arr = token_decode($token);
		list($uid, $passwd) = empty($token_arr) || count($token_arr) < 2 ? array('', '') : $token_arr;
		return array($uid, $passwd);
	}
	
	//获取当前的用户信息
	abstract protected function getCurrentUser();
	
	//获取当前的用户TokenName
	protected function getTokenName(){
	    return constant(strtoupper($this->app_name) . '_SESSION_TOKEN');
	}
	
	protected function checkAccess() {
	    return true;
	}
    
	/**
    * 检查登录账号类型//检测学校管理员AMS后台系统的权限
    */
    protected function checkLoginerInSchool($uid, $schoolid) {
        $mAmsAccount = ClsFactory::Create("Model.mAmsAccount");
        $userInfo = $mAmsAccount->getAmsAccountByUid($uid);
        $mSchool = ClsFactory::Create('Model.mSchoolInfo');
        $schoolinfo_arr = $mSchool->getSchoolInfoByNetManagerAccount($uid);
        $schoolInfo = & $schoolinfo_arr[$uid];
        foreach($schoolInfo as $key=>$val) {
            $userId = $val['net_manager_account'];
            $school_id = $key;
            break;
        }
        
        if ($uid==$userId && $school_id == $schoolid) {
            return true;
        }
        
        return false;
    }
}