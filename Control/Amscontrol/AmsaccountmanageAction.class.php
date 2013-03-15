<?php
class AmsaccountmanageAction extends AmsController{
    //作者：郭学文
    public function _initialize(){
        parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		import("@.Common_wmw.Constancearr");
		
	    $this->assign('username', $this->user['ams_name']);
    }
    
    public function showAmsUserInfo() {
        $mAmsAccount = CLsFactory::Create("Model.mAmsAccount");
        $AmsAccount = $mAmsAccount->getAmsAccountByUid($this->user['ams_account']);
        $this->assign('userinfo', $AmsAccount[$this->user['ams_account']]);
        $this->display('userinfo');
    }
    
    public function modifyAmsUserInfo() {
        $client_name = $this->objInput->postStr('client_name');
        $old_client_pwd = $this->objInput->postStr('old_client_pwd');
	    $client_email = $this->objInput->postStr('client_email');
	    $client_pwd = $this->objInput->postStr('client_pwd');
	    $re_client_pwd = $this->objInput->postStr('re_client_pwd');
	    $msg = array();
	    
	    
	    if($client_pwd != $re_client_pwd) {
	        $msg[] = "两次输入的密码不一致！";
	    }
	    
	    $dataarr = array(
	        'ams_name'=>$client_name,
	        'ams_email'=>$client_email
	    );
	    
	    if(!empty($client_pwd) && !empty($re_client_pwd) && ($client_pwd == $re_client_pwd) && md5($old_client_pwd) == $this->user['ams_password']) {
	        $dataarr['ams_password']=md5($client_pwd);
	    }else{
	        $msg[] = "原密码错误！";
	    }
	    if(empty($msg)) {
	        $mAmsAccount = ClsFactory::Create("Model.mAmsAccount");
    	    $uid = $this->user['ams_account'];
    	    $resault = $mAmsAccount->modifyAmsAccount($dataarr, $uid);
	    }
	    if(!empty($resault)) {
	        $msg[] = "修改成功！";
	        $this->showSuccess(array_shift($msg),'/Amscontrol/Amsaccountmanage/showAmsUserInfo');
	    }else{
	        $msg[] = "修改失败或者没有任何修改！";
	        $this->showError(array_shift($msg),'/Amscontrol/Amsaccountmanage/showAmsUserInfo');
	    }
    }
}