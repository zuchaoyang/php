<?php
class UsermanageAction extends WmsController {
    protected $length = 10;
    public function _initialize(){
        parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		import("@.Common_wmw.Constancearr");
	}
	
	//账号搜索方法
	public function account_search() {
	    $client_account = $this->objInput->postStr('client_account');
	    $client_name  = $this->objInput->postStr('client_name');
	    
	    if(!empty($client_account) || !empty($client_name)){
	        $page = $this->objInput->getInt('page');
	    
    		if($page <= 1){
    		    $page=1;
    		}
    		$offset=($page-1)*$this->length;
    		$mUser = ClsFactory::Create('Model.mUser');
    		$account_list = array();
    		
    		if(!empty($client_account)){ //通过账号搜索
                $mark = preg_match('/^1[1-9]{1}[0-9]{9}/',$client_account); //用户输入是账号，还是手机号
                if($mark){
                    $phonenum = &$client_account;
                    $Businessphone = ClsFactory::Create ( 'Model.mBusinessphone' );
                    $phoneinfo = $Businessphone->getbusinessphonebyalias_id($phonenum);
                    $phone_id = $client_account;
                    $client_account = $phoneinfo[$phonenum]['uid'];
                }
                
        		$account_list = $mUser->getUserBaseByUid($client_account);
        		
        		if(!empty($phone_id)){
        		    $client_account = $phone_id;
        		}
        		    
    		} else if(!empty($client_name)){//通过名字查找 
    		    $accountinfo = $mUser->getUserByUsername($client_name, $offset, $this->length+1);
    		    $accounts = array_keys($accountinfo);
    		    
    		    $account_list = $mUser->getUserBaseByUid($accounts);
    		}
    		
    		if(count($account_list) < $this->length ) {
    	        $flag = true;
    	    } else{
    	        array_pop($account_list);
    	    }
    	    
    		$i = ($page-1) * $this->length;
    	    foreach($account_list as $key=>& $value){
    	        $account_list[$key]['add_time'] = date('Y-m-d H:i:s',$account_list[$key]['add_time']);
    			$account_list[$key]['id']=$i+1;
    			$i++;
    	    }
    		
    	    //业务手机号信息
    	    $client_accounts = array_keys($account_list);
    	    $mBusinessPhone = ClsFactory::Create('Model.mBusinessphone');
    	    $phoneInfo_arr = $mBusinessPhone -> getbusinessphonebyalias_id($client_accounts); //业务手机信息
    	    foreach($phoneInfo_arr as $key=>$phoneinfo){
    	        $account_list[$key]['phone_id'] = $phoneinfo['phone_id'];
    	        
    	    }
    	    
    		$this->assign('page',$page);
    		$this->assign('flag',$flag);
    		$this->assign('useraccount',$client_account);
    		$this->assign('username',$client_name);
    		$this->assign('account_list',$account_list);
	    }
		$this->display('account_manage');
		 
	}
	    
	    
	    /**
	     * 点击进入账号查看账号详细信息及家长信息
	     */
	    public function account_detail() {
	        $client_account = $this->objInput->getStr('client_account');
	        
	        //获取用户的关联数据
	        list($client_type, $family_accounts) = $this->getFamilyAccountByUid($client_account);
	        //获取用户的列表信息
            $user_list = $this->getDisplayAccountInfoByUid($family_accounts);
            
            //数据分组
            $new_user_list = array();
            
            list($left_account, $center_account, $right_account) = $family_accounts;
            if(!empty($user_list[$left_account])) {
                $new_user_list[] = & $user_list[$left_account];
            }
            if(!empty($user_list[$center_account])) {
                $new_user_list[] = & $user_list[$center_account]; 
            }
            if(!empty($user_list[$right_account])) {
                $new_user_list[] = & $user_list[$right_account];
            }
		    
            $this->assign('client_type', $client_type);
    		$this->assign('user_list', $new_user_list);
	        $this->display('account_detail');
	    }

	    
	    
        //重置密码
    	public function pwd_reset(){
            $account = $this->objInput->getStr('client_account');
            $new_pwd = MD5_PASSWORD;
            $mUser = ClsFactory::Create("Model.mUser");
            $user_info = $mUser->getClientAccountById($account);
            if($user_info[$account]['client_password'] == $new_pwd){//要更改的数据与库中数据相同时，data层execute后的结果（受影响数返回为0）显示失败，故先进行查询
                $this->showSuccess("该用户密码已重置", "/Adminuser/Usermanage/account_detail/client_account/".$account);
            }else{
                $data = array(
                	'client_account'=>$account, 
                	'client_password'=>$new_pwd, 
                	'upd_time'=>time(),
                );
                $rs = $mUser->modifyUserClientAccount($data, $account);
                if($rs){
                    $this->showSuccess("该用户密码已重置", "/Adminuser/Usermanage/account_detail/client_account/".$account);
                }else{
                    $this->showError("密码重置失败", "/Adminuser/Usermanage/account_detail/client_account/".$account);
                }
            }
    	}
    	
    	
	//冻结账号方法
	public function account_stop(){
	    $account=$this->objInput->getStr('client_account');
		$this->change_account_status($account, CLIENT_STOP_FLAG_FOREVER);
    }
        
     //解冻标识
	public function account_start(){
	    $account=$this->objInput->getStr('client_account');
		$this->change_account_status($account, CLIENT_STOP_FLAG_NORMAL);
     }
     
        
     private function change_account_status($account, $status_flag){
		$data = array(
		    'status' => $status_flag,
			'upd_time'=>time(),
		);
		
	    $mUser = ClsFactory::Create('Model.mUser');
	    $result = $mUser->modifyUserClientAccount($data,$account);
		$this->redirect('/Usermanage/account_detail/client_account/'.$account);
     }
    	
    /**
     * 获取账号对应的手机号信息
     */
    private function getDisplayAccountInfoByUid($uids) {
        if(empty($uids)) {
            return false;
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getUserByUid($uids);
        //转换用户信息
        if(!empty($user_list)) {
            foreach($user_list as $uid=>$user)  {
                //拼接班级信息
                if(isset($user['class_info'])) {
                    $class_names = array();
                    foreach($user['class_info'] as $classinfo) {
                        $class_names[] = $classinfo['class_name'];
                    }
                    $user['class_name'] = implode('，', $class_names);
                }
                $school = reset($user['school_info']);
                $user['school_name'] = $school['school_name'];
                //去掉无用数据
                unset($user['class_info'], $user['client_class'], $user['school_info']);
                
    	        $user['stop_flag_message'] = Constancearr::stop_flag($user['status']);
    	        $user['lastlogin_date'] = date('Y-m-d H:i:s', $user['lastlogin_date']);
    	        $user['active_date'] = date('Y-m-d H:i:s', $user['active_date']);
    	        $user['client_sex_name'] = $user['client_sex'] == 1 ? "男" : "女";
    	        if(empty($user['job_address_name'])) {
    	            $user['job_address_name'] = "暂无";
    	        }
    	        
    	        $user_list[$uid] = $user;
    	    }
        }
        //查询用户业务手机
	    $mBusinessPhone = ClsFactory::Create('Model.mBusinessphone');
	    $phoneInfo_arr = $mBusinessPhone -> getbusinessphonebyalias_id($uids); //业务手机信息
	    //拼接手机号信息
	    if(!empty($phoneInfo_arr)) {
	        foreach($phoneInfo_arr as $phone) {
	            $uid = $phone['uid'];
	            if(isset($user_list[$uid])) {
	                $user_list[$uid]['phone_id'] = $phone['phone_id'];
	            }
	        }
	        unset($phoneInfo_arr);
	    }
	    
        return !empty($user_list) ? $user_list : false;
     }
     
     /**
      * 通过主账号获取关联的家长等账号信息
      * @param $uid
      */
     private function getFamilyAccountByUid($uid) {
         if(empty($uid)){
             return false;
         }
         
         $mUser = ClsFactory::Create('Model.mUser');
         $userlist = $mUser->getClientAccountById($uid);
         $user = & $userlist[$uid];
         $client_type = intval($user['client_type']);
         
         $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
         
         $family_accounts = array();
         $family_accounts[] = $uid;
         if($client_type == CLIENT_TYPE_STUDENT){
             //学生获取家庭账号
            $family_arr = $mFamilyRelation->getFamilyRelationByUid($uid);
            $family_list = & $family_arr[$uid];
            if(!empty($family_list)) {
            	foreach($family_list as $family) {
            		$family_accounts[] = $family['family_account'];
            	}
            	unset($family_list, $family_arr);
            }
         } else if($client_type == CLIENT_TYPE_FAMILY) {
             //家长获取家庭账号
             
            $family_info = $mFamilyRelation->getFamilyRelationByFamilyUid($uid);
            $family_info = array_shift($family_info[$uid]);
            $child_account = $family_info['client_account'];
            
            $family_accounts[] = $child_account;
            
            $family_list = $mFamilyRelation->getFamilyRelationByUid($child_account);
            if(!empty($family_list)){
            	$family_list = $family_list[$child_account];
	            foreach($family_list as $family_account) {
	                if($family_account['family_account'] == $uid) {
	                    continue;
	                }
	            	$family_accounts[] = $family_account['family_account'];
	            }
            }
         }
         
         return array($client_type, $family_accounts);
     }
     
     
     
   
    	
}