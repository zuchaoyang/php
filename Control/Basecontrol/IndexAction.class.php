<?php
class IndexAction extends BmsController {
    public function _initialize(){
        parent::_initialize();
        header("Content-Type:text/html; charset=utf-8");
	}
    
	public function index(){
	    $this->display('index');
	}
    public function top(){
    	    $this->display('top');
    	}
    public function left(){
    	    $this->display('left');
    	}
    public function main(){
          $client_account = $this->user['base_account'];
          $mBmsAccount=ClsFactory::Create('Model.mBmsAccount');
		  $rsinfo=$mBmsAccount->getUserInfoByUid($client_account);
		  $rsinfo = array_shift($rsinfo);
		  $this->assign('baseinfo',$rsinfo);
    	  $this->display('main');
    	}
    public function appSchoolProcess(){
          $client_account = $this->user['base_account'];
          $mBmsAccount=ClsFactory::Create('Model.mBmsAccount');
		  $rsinfo=$mBmsAccount->getUserInfoByUid($client_account);
		  $rsinfo = array_shift($rsinfo);
		  $this->assign('baseinfo',$rsinfo);
    	  $this->display('appSchoolProcess');
    	}
    public function downfile(){
       //判断用户是否登录 
	   $mBmsAccount = ClsFactory::Create('Model.mBmsAccount');
	   $filename = WEB_ROOT_DIR.'/Public/downfile/schoolapplication.doc';
    	if (!file_exists($filename)) {
    		$filename = IMG_SERVER.'/Public/downfile/schoolapplication.doc';
        }
        
       $down_file = ClsFactory::Create('@.Common_wmw.WmwDownload');
       $down_file->downfile($filename, '教育信息化公共服务平台申请表');
    }
        
}