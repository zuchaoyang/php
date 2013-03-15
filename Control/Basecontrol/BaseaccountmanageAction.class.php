<?php
class BaseAccountManageAction extends BmsController {
    public function _initialize(){
        parent::_initialize();
        import("Libraries/common.php");
        header("Content-Type:text/html; charset=utf-8");
    }
     //获取cookie中的用户账号
	function getCookieAccount(){
		return $this->user['base_account'];
	}
    public function showBaseAccountInfo(){
        //判断用户是否登录 
	    $mBmsAccount = ClsFactory::Create('Model.mBmsAccount');
	    $client_account = $this->getCookieAccount();
		$baseAccountInfo = $mBmsAccount->getUserInfoByUid($client_account);
		$baseAccountInfo = array_shift($baseAccountInfo);
		$this->assign('baseAccountInfo', $baseAccountInfo);  
        $this->display('baseAccountManage');
    }
    public function ModifyAccount(){
        $base_account = $this->getCookieAccount();
        $base_name  = $this->objInput->postStr('base_name');
        $base_email = $this->objInput->postStr('base_email');
        $old_pwd = $this->objInput->postStr('input_old_pwd');
        $new_pwd = $this->objInput->postStr('new_pwd');
        $repeate_pwd = $this->objInput->postStr('new_pwd_check');
        
        if($base_name == ""){ 
            echo "基地名称不能为空"; //邮箱可以为空
            return false;
        } 
        $client_account = $this->getCookieAccount();
        $mBmsAccount = ClsFactory::Create('Model.mBmsAccount');
		$baseAccountInfo = $mBmsAccount->getUserInfoByUid($client_account);
		$baseAccountInfo = array_shift($baseAccountInfo);
     
		if($old_pwd != "" ||$new_pwd != ""){
		    if($baseAccountInfo['base_password'] != md5($old_pwd)){//用户输入新密码进行修改时，检测
		        echo "原密码不正确";
		        return false;
		    }else{
		        if($new_pwd == "" || $repeate_pwd == ""){
		            echo "新密码不能为空";
		            return false;
		        }elseif($new_pwd != $repeate_pwd){
                    echo "两次输入的密码不一致";
                    return false;
                }
		    }
		}
		
	    $dataArr = array('base_name' => $base_name,
	                     'base_email'=> $base_email,
	    );
	                    
	    if($new_pwd != ""){
	        $base_password = md5($new_pwd);
	        $dataArr['base_password'] = $base_password; 
	    }
	    
	    $result = $mBmsAccount->modifyBmsAccountByAccount($dataArr, $base_account);

	    if($result){
	        
	        if (!empty($new_pwd)) {
	        	$this->showSuccess("更新成功，请重新登录", "/Basecontrol/BaseAccountManage/showBaseAccountInfo");
	        } else{
	        	echo "更新成功！";
	        }
	        
	    }else{
	        echo "更新失败！";
	        return false; 
	    }
    }
    
//token 解密
function token_decode($string) {
    if(!is_string($string)) return false;
    $result =  authcode($string);
    $token_arr = explode("\t", $result);
    return $token_arr;
}
    
    
}
