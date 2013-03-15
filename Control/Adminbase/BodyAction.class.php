<?php
class BodyAction extends WmsController{
	public function _initialize(){
	    parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		import("@.Common_wmw.Constancearr");
		import("@.Common_wmw.WmwString");
		//判断用户是否登录 
		import("@.Control.Adminbase.WmsadminloginAction");
		
	}
	
	public function index(){
		$this->display('index');
	}
	
    //左侧
	function leftinfo() {
	    $func_type = $this->objInput->getStr('func_type');
		if(!$func_type){
			$func_type='JC';
		}
		//获取当前账号可以使用的功能id
		$cookie_account = $this->user['wms_account'];
		$mRoleFuncRelation = ClsFactory::Create('Model.mRoleFuncRelation');
        $func_codes = $mRoleFuncRelation->getFuncCodeByUid($cookie_account);
        //获取当前分类下的功能的功能列表
		if(!empty($func_codes)) {
		    $mFunc = ClsFactory::Create('Model.mFunc');
    		$order = ' func_num asc ';
    		$func_list = $mFunc->getFuncByFuncTypeAndIsShowflag($func_type, 1, $order); 
    		foreach($func_list as $func_code => $func_info) { 
    		    if(in_array($func_code, $func_codes) || $func_info['super_func_code'] == 'NOLINK') {
    		        $func_info['func_url'] = $func_info['func_url'] . '/func_code/' . $func_code . '/access/' . md5($func_info['func_code'].'WMS'. time(date('Y-m-d')).$this->user['wms_account']) . 'check/1';
    		        $user_func_list[$func_code] = $func_info;
    		    }
    		}
		}
		$this->assign('typelist',$user_func_list);
		$this->display('left');
	}
	
	//账号信息修改
	public function modifyUserInfo() {
	   $login_uid = $this->user['wms_account'];
	   $mWmsAccount = ClsFactory::Create("Model.mWmsAccount");
	   $userArr = $mWmsAccount->getWmsAccountByUid($login_uid);
       $userlist = $userArr[$login_uid];
       $this->assign('userlist', $userlist);
       $this->display('modifyaccount');
	}
	
    public function modifyUserInfo_do() {
	    $client_name = $this->objInput->postStr('client_name');
	    $client_email = $this->objInput->postStr('client_email');
	    $client_pwd = $this->objInput->postStr('client_pwd');
	    $old_client_pwd = $this->objInput->postStr("old_client_pwd");
	    $re_client_pwd = $this->objInput->postStr('re_client_pwd');
	    
	    $msg = array();
	    
	    if($client_pwd != $re_client_pwd) {
	        $msg[] = "两次输入的密码不一致！";
	    }
	    
	    $dataarr = array(
	        'wms_name'=>$client_name,
	        'wms_email'=>$client_email
	    );
	    if(!empty($client_pwd) && !empty($re_client_pwd) && ($client_pwd == $re_client_pwd) && md5($old_client_pwd) == $this->user['wms_password']) {
	        $dataarr['wms_password']=md5($client_pwd);
	    }else{
	        $msg[] = "原密码错误！";
	    }
	    
	    $mWmsAccount = ClsFactory::Create("Model.mWmsAccount");
	    $uid = $this->user['wms_account'];
	    $resault = $mWmsAccount->modifyWmsAccount($dataarr, $uid);
	    if(!empty($resault)) {
	        $msg[] = "修改成功！";
	        $this->showSuccess(array_shift($msg),'/Adminbase/Body/modifyUserInfo');
	    }else{
	        $msg[] = "修改失败或者没有任何修改！";
	        $this->showError(array_shift($msg),'/Adminbase/Body/modifyUserInfo');
	    }
	}
	
	//头部变量
	public function topinfo(){
		$this->assign('adminuser',$this->user['wms_account']);
		
		$this->display('top');
	}
	
	//body页面
	public function bodyinfo(){		
		$this->display('body');
	}
	
	//账号规则
	public function accountRule(){
	    $mAccountRule = ClsFactory::Create('Model.mAccountRule');
        $all_rules = $mAccountRule->getAccountRuleAll();
        
	    $user_flag = 1;//使用标志
	    $current_rule = $mAccountRule->getAccountRuleByUseFlag($user_flag); 
	    $current_used_rule = array_values($current_rule);
	    $current_used_rule = & $current_used_rule[0];
	    
	    $this->assign('all_rule_length', array_keys($all_rules));
	    $this->assign('current_used_rule', $current_used_rule);
        $this->display('accountrules');
	}

	//修改账号规则
	public function updAccountRule() {
        $account_length = $this->objInput->getInt('account_length_set'); 
        if(empty($account_length)) {
            $this->showError("账号规则修改失败", "/Adminbase/Body/accountrule");
        }
        
        $mAccountRule = ClsFactory::Create('Model.mAccountRule');
        $current_rule = $mAccountRule->getAccountRuleByUseFlag($use_flag = 1);
        if(!empty($current_rule)) { 
            $ids = array_keys($current_rule);
        }
        $reset_data = array(
			'use_flag'=>'0',
			'upd_account'=>$this->user['wms_account'],
			'upd_date'=>date('Y-m-d H:i:s',time())
		);
		if(count($ids) > 1) {
		    foreach($ids as $id) {
		        $reset_rs = $mAccountRule->modifyAccountRule($reset_data, $id);
		    }
		}else {
		    $id = array_shift($ids);
		    $reset_rs = $mAccountRule->modifyAccountRule($reset_data, $id);
		}
         
        if($reset_rs) {
            $set_data = array(
                'account_length' => $account_length,
                'use_flag'=>'1',
				'add_account'=>$this->user['wms_account'],
				'add_date'=>date('Y-m-d H:i:s',time())
            );
            $set_rs = $mAccountRule->modifyAccountRule($set_data, $account_length);
            if($set_rs) {
                $this->redirect('Body/accountrule');
            }
        }
        $this->showError("账号规则修改失败", "/Adminbase/Body/accountrule");
	}
	
	//已发账号统计
	public function alaccount() {
	    $mUser=ClsFactory::Create('Model.mUser');  
	    $mAccountRule = ClsFactory::Create('Model.mAccountRule');
	    $mAccountLock = ClsFactory::Create('Model.mAccountLock');
	      
	    $all_rule_list = $mAccountRule->getAccountRuleAll();
	    foreach($all_rule_list as $account_length => $rule_info) {
	        $count_used = $mUser->getUserTotalByAccountLength($account_length);
	        $count_locked = $mAccountLock->getAccountLockTotalByAlength($account_length); 
	        $count_available = $rule_info['use_count']-$count_used-$count_locked;
	        
	        $all_rule_list[$account_length]['count_used'] = intval($count_used);
	        $all_rule_list[$account_length]['count_locked'] = intval($count_locked);
	        $all_rule_list[$account_length]['count_available'] = $count_available;
	    }

	    $this->assign('count_list', $all_rule_list);
	    $this->display('account_statistics');
	}
	
	//锁定账号
	public function accountblock() {
	    $page = $this->objInput->getInt('page');
	    $lock_account = $this->objInput->getInt('lock_account');
        $account_length = $this->objInput->getInt('account_length');
        
        $page = max(1, $page);
        $lock_account = max(0, $lock_account);
        $account_length = max(0, $account_length);
        
        $limit = 10;
        $offset = ($page-1)*$limit;
        
        $mAccountLock = ClsFactory::Create('Model.mAccountLock');
        if(!empty($lock_account)) {     //通过账号查询 
              $account_list = $mAccountLock->getAccountLockById($lock_account); 
        } else if(!empty($account_length)) { //查询指定长度号段
            $account_list = $mAccountLock->getAccountLockByAccountLength($account_length, $offset, $limit+1);
        } else { //不限不查询
            $account_list = $mAccountLock->getAccountLock($offset, $limit+1);
        }
        
        if(count($account_list) <= $limit) {
            $is_last_page = true;
        }

        $mAccountRule = ClsFactory::Create('Model.mAccountRule');
        $all_rules = $mAccountRule->getAccountRuleAll();
        
        $this->assign('page', $page);
        $this->assign('is_last_page', $is_last_page);
        $this->assign('lock_account', $lock_account);
        $this->assign('account_length', $account_length);
        $this->assign('account_list', array_slice($account_list, 0, $limit));
        $this->assign('all_rules', $all_rules);
        
        $this->display('blockaccounts');                    	            
	}
	
	//搜索锁定账号
	public function searchlock(){
		$account_length=trim($this->objInput->getStr('account_length'));
		$lock_account = trim($this->objInput->getStr('lock_id'));
		$this->accountblock($lock_account,$account_length);
	}
	//添加锁定账号
	
	public function addlock() {
        $this->display('addlockaccount');
	} 
	
	public function saveNewLockAccount() {
	    $lock_account = $this->objInput->postStr('lock_account');
        if(empty($lock_account)) {
            echo "请输入要锁定的账号";
            exit;
        }
        $mAccountLock = ClsFactory::Create('Model.mAccountLock');
        $lock_account_info = $mAccountLock->getAccountLockById($lock_account);
        if(!empty($lock_account_info)) {
            $this->showError("指定账号已锁定或者已被注册", "/Adminbase/Body/addlock");
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
		$userinfo = $mUser->getUserBaseByUid($lock_account);
		if(!empty($userinfo)) { //账号已被使用
			$this->showError("指定账号已锁定或者已被注册", "/Adminbase/Body/addlock");
		}
		
	    $data=array(
			'lock_account' => $lock_account,
			'account_length' => strlen($lock_account),
			'add_account' => $this->user['wms_account'],
			'add_date' => date('Y-m-d H:i:s',time())
		);
		$add_rs = $mAccountLock->addAccountLock($data); 
	    if($add_rs) {
	        $this->showSuccess("指定账号添加成功", "/Adminbase/Body/addlock");
	    }else{
	        $this->showError("指定账号已锁定或者已被注册", "/Adminbase/Body/addlock");
	    }
	}
	
	//删除锁定账号
	public function deletelock($account1=''){
	    $lock_account = $this->objInput->getInt('lock_account');
	    if(empty($lock_account)) {
            echo "请选择要删除的账号";
            exit;
        }
		$mAccountLock = ClsFactory::Create('Model.mAccountLock');
		$del_rs = $mAccountLock->delAccountLock($lock_account);
		
		if($del_rs){
			$this->redirect('Body/accountblock');
		}else{
			$this->showError("账号删除失败", "/Adminbase/Body/accountblock");
		}
	}
	//指定账号申请页面
	public function dsaccount(){	
		$this->display('dsaccount');
	}
	
	//添加指定账号
	public function updsaccount(){
		$clientCount = $this->objInput->postStr('client_account');
		$clientPwd = $this->objInput->postStr('client_pwd');
		$reclientPwd = $this->objInput->postStr('re_client_pwd');
		$email = $this->objInput->postStr("client_email");
		$client_type = $this->objInput->postStr('client_type');
		$client_name = $this->objInput->postStr('client_name');
		
		if($clientPwd != $reclientPwd) {
		    $this->showError("两次密码不一致", "/Adminbase/Body/dsaccount");
		    exit;
		}
		
		$dataarr = array(
			$client_type.'_account'=>$clientCount,
			$client_type.'_name'=>$client_name,
			$client_type.'_password'=>md5($clientPwd),
			$client_type.'_email'=>$email,
			'add_time'=>time(),
			'add_account'=>$this->user['wms_account']
		);
            
		$resault = false;
		if($client_type == 'wms') {
			$resault = $this->addWms($clientCount, $dataarr);
		}else if($client_type == 'base') {
			$resault = $this->addBms($clientCount, $dataarr);
		}
            
		if($resault){
			$this->showSuccess("账号注册成功", "/Adminbase/Body/dsaccount");
		}else{
			$this->showError("账号注册失败", "/Adminbase/Body/dsaccount");
		}

	}
	
	private function addWms($uid, $dataarr) {
	    if(empty($dataarr)) {
	        return false;
	    }
	    
	    $mWmsAccount = ClsFactory::Create("Model.mWmsAccount");
	    $userinfo = $mWmsAccount->getWmsAccountByUid($uid);
	    
	    if(!empty($userinfo)){
	    	return false;
	    }
	    
        return $mWmsAccount->addWmsAccount($dataarr);
	}
	
	private function addBms($uid, $dataarr) {
	    if(empty($dataarr)) {
	        return false;
	    }
	    
	    $mBaseUser = ClsFactory::Create("Model.mBmsAccount");
	    $userinfo = $mBaseUser->getUserInfoByUid($uid);
	    
		if(!empty($userinfo)){
	    	return false;
	    }
	    
        return $mBaseUser->addBmsAccount($dataarr);
	}
}
