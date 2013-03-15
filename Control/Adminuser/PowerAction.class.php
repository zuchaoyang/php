<?php
class PowerAction extends WmsController {
	const LENGTH = 15;
	public function _initialize(){
	    parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		import("@.Common_wmw.Constancearr");
	}
	
	//权限查询
	public function powerSear($account='', $client_name=''){
		$current_page = $this->objInput->getStr('page');
		if($this->objInput->getStr('client_name')!=''){
			$client_name=$this->objInput->getStr('client_name');
		}
		$current_page = max(1, $current_page);
		$offset = ($current_page-1)*self::LENGTH;
		
		$limit = self::LENGTH+1;
		$account_Model=ClsFactory::Create('Model.mWmsAccount');
		$url_arr = explode('/',$_SERVER['REQUEST_URI']);
		if(count($url_arr)>4){
			$url_arr = array_splice($url_arr, 0,4);
			$url = implode('/',$url_arr);
		}elseif(strpos($_SERVER['REQUEST_URI'], '?')){
			$url = substr($_SERVER['REQUEST_URI'],0,strrpos($_SERVER['REQUEST_URI'],'?'));
		}else{
			$url = $_SERVER['REQUEST_URI'];
		}
		
		if($account != ''){
			$account_info = $account_Model->getWmsAccountByUid($account); //account
			$this->assign('client_account',$account);
		}else if($client_name != ''){
			$account_info = $account_Model->getWmsAccountByName($client_name, $offset, $limit); //name
			$url.='/client_name/'.$client_name;
			$this->assign('client_name',$client_name);
		}else{
			$account_info ='';
		}
		$page['pre'] = $url;
		$page['next'] = $url;
		$prepage = $current_page-1;
		$prepage = max(1, $prepage);
		if(count($account_info) < $limit){
			$nextpage = $current_page;
			
		}else{
			$nextpage = $current_page+1;
			array_pop($account_info);
		}
		
		$page['pre'] .= '/page/'.$prepage;
		$page['next'] .= '/page/'.$nextpage;
		if($account_info) {
			$i=$offset+1;
			foreach($account_info as $key=>&$client_info){
				$account_info[$key]['id']=$i++;
			}
		}else {
			unset($account_info);
		}
		
		if($client_type == ""){
			$client_type = "null";
		}
		$this->assign('client_type',$client_type); //假动作,当client_type为空时给的一个默认值
		$this->assign('page',$page); //  赋值分页输出 
		$this->assign('accountinfo',$account_info);
		$this->assign('type',Constancearr::client_type());
		
		$this->display('powerSear');
	}
	//权限搜索
	public function powerSearch(){
		if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
			$account=trim($this->objInput->postStr('client_account'));
			$client_name=trim($this->objInput->postStr('client_name'));
		} else {
			$account=trim($this->objInput->getStr('client_account'));
			$client_name=trim($this->objInput->getStr('client_name'));
		}
		$this->powerSear($account,$client_name);
	}
	
	//角色列表
	public function addRole() {
	    $page = $this->objInput->getInt('page');
	    
	    $page = max(1, $page);    
	    $mRoleInfo = ClsFactory::Create('Model.mRoleInfo');
	    $limit = 15;
	    $offset = ($page-1)*$limit; 
	    $role_info_list = $mRoleInfo->getRoleInfo($offset, ($limit+1));
	    if(count($role_info_list) <= $limit) {
	        $is_last = true;
	    }
	    
	    //查询角色的使用人数
	    if(!empty($role_info_list)) {
	        $mClientRoleRelation = ClsFactory::Create('Model.mClientRoleRelation');
	        foreach($role_info_list as $role_code => $role) {
                $count_user = $mClientRoleRelation->getClientRoleRelationTotalByRoleCode($role_code);
                $role_info_list[$role_code]['count_user'] = intval($count_user);            
	        }
	    }    
	    
	    $role_list = array_splice($role_info_list, 0, $limit);
	    $this->assign('is_last', $is_last);
	    $this->assign('page', $page);
	    $this->assign('role_list', $role_list);
	    
	    $this->display('addRole');    
	}
 
    public function addNewRole() {
        $this->display('addNewRole');
    }
	
	public function saveRole() { 
	    $role_name = $this->objInput->getStr('role_name');
	    
	    if(empty($role_name)) {
	        $this->assign('tip', '角色名称不能为空,');
	        $this->assign('tip1', '点击返回');
	        $this->assign('url', '/Adminuser/Power/addNewRole');
	        $this->display("Tips");
	        exit;
	    }
	    $mRoleInfo = ClsFactory::Create('Model.mRoleInfo');
	    $role_info = $mRoleInfo->getRoleInfoByRoleName($role_name);
	    if(!empty($role_info)) {
	        $this->assign('tip', '角色已存在,');
	        $this->assign('tip1', '点击返回');
	        $this->assign('url', '/Adminuser/Power/addNewRole');
            
	        $this->display("Tips");
	        exit;	        
	    } 
	    
	    $role_data = array(
	        'role_name' => $role_name,
	        'add_account' => $this->user['wms_account'],
	        'add_date' => date("Y-m-d H:i:s"), 
	    );
	    
	    
	    
	    $add_rs = $mRoleInfo->addRoleInfo($role_data); 
	    if($add_rs){
            $this->assign('tip', '添加成功,');
	        $this->assign('tip1', '点击返回');
	        $this->assign('url', '/Adminuser/Power/addRole');
            
	        $this->display("Tips");
	    }else{
	        $this->assign('tip', '添加失败，请重新尝试,');
	        $this->assign('tip1', '点击返回');
	        $this->assign('url', '/Adminuser/Power/addNewRole');
            
	        $this->display("Tips");	       
	    }
	}
	
	//角色功能
	public function roleFunc($role_code=''){
		// 功能列表
		$mFunc = ClsFactory::Create('Model.mFunc');	
		$order = 'func_type,func_num ';
		$func_info = $mFunc->getFunc(1, false, $order);
        
		if (!empty($role_code)) {
		    //根据角色查功能
		    $mRoleFuncRelation = ClsFactory::Create('Model.mRoleFuncRelation');
		    $role_func_info= $mRoleFuncRelation->getRoleFuncRelationByRoleCode($role_code); 
		    $role_func_info = & $role_func_info;

		    if(!empty($role_func_info)) {
    		    foreach ($role_func_info as $id => $relation_info) {
    		        $func_codes[] = $relation_info['func_code'];
    		    }
    		    $func_codes = array_unique($func_codes); 
		        foreach ($func_info as $id => $info) {
		            if(in_array($info['func_code'], $func_codes)) {
		                $func_info[$id]['flag'] = true;
		            } else {
		                $func_info[$id]['flag'] = false;
		            }
		        }
		    }
		} 
		
		//角色列表
		$mRoleInfo = ClsFactory::Create('Model.mRoleInfo');
		$role_info = $mRoleInfo->getRoleInfo();
		
		$this->assign('roles',$role_info);
		$this->assign('funcs',$func_info);
		$this->assign('selected_role_code', $role_code);
		
		$this->display('roleFuncConn');
	}
	//角色关联功能搜索角色
	public function searchRole($code=''){
		$role_code=$this->objInput->postStr('rolecode');
		if($code != ''){$role_code=$code;}
		
		$mRoleInfo = ClsFactory::Create('Model.mRoleInfo');
		$role_list = $mRoleInfo->getRoleInfoById($role_code);
		$role_info = array_shift($role_list);
		
		
		$this->assign('role',$role_info);
		$this->roleFunc($role_info['role_code']);
	}

	//点击角色显示功能
	public function clickrole(){
		$role_code = $this->objInput->getStr('code');
		if($role_code!=''){
			$this->assign('role',$role_code);
			$this->roleFunc($role_code);
		}else{
			$this->searchRole($role_code);
		}
	}
	
	//todo 没有对是否修改成功进行判断处理
	//修改角色功能
	public function updrolefunc(){
		$func_arr = explode(',',$this->objInput->postStr('func_str'));
		$role_code = $this->objInput->postInt('role_code');
		$cookie_account = $this->user['wms_account'];
		
		//去尾
		array_pop($func_arr);
		$mRoleFuncRelation = ClsFactory::Create('Model.mRoleFuncRelation');
		//通过角色id删除角色 功能对应关系
		$effect_rows = $mRoleFuncRelation->delRoleFuncRelationCompositeKey($role_code);
		//添加对应关系
		if (!empty($func_arr)) {
			$nowdate = date('Y-m-d H:i:s');
			foreach ($func_arr as $func) {
				$data_arr[] = array(
					'role_code'=>$role_code,
			        'func_code'=>$func,
			        'add_account'=>$cookie_account,
			        'add_date'=>date('Y-m-d H:i:s')
				);
			}
			
		}
		$mRoleFuncRelation->addRoleFuncRelationBat($data_arr);
	    
		$this->assign('upd_success', 1);
		$this->assign('role',$_POST);
		$this->roleFunc($role_code);
	}
	
	//账号角色
	//显示权限修改
	public function powerUpd(){
		//账号信息表
		$account = $this->objInput->getInt('account');
		$success = $this->objInput->getStr('upd_success');
		$account_Model = ClsFactory::Create('Model.mWmsAccount');
		$account_info = $account_Model->getWmsAccountByUid($account);
		//账号角色关联表	
		$mClientRoleRelation = ClsFactory::Create('Model.mClientRoleRelation');
		$tmp_client_role_info = $mClientRoleRelation->getClientRoleRelationByClientAccount($account);
		$client_role_info = $tmp_client_role_info[$account];
		//角色表
		$mRoleInfo = ClsFactory::Create('Model.mRoleInfo');
		$role_list = $mRoleInfo->getRoleInfo();

		//处理$client_role_info 与 $role_list 相融合
		if (!empty($role_list) && !empty($client_role_info)) {
			foreach ($role_list as $key=>$val) {
				$sameflag = false;
				foreach ($client_role_info as $k=>$v) {
					if ($val['role_code'] == $v['role_code']) {
						$role_list[$key]['flag'] = true;
						$sameflag = true;
					}
				}
				
				if (!$sameflag) {
					$role_list[$key]['flag'] = false;
				}
			}	
		}
		
		$this->assign('success',$success);
		$this->assign('account_info',$account_info[$account]);
		$this->assign('rolelist',$role_list);
		$this->assign('type',Constancearr::client_type());

		$this->display('updPower');
	}
	
	//todo 没有对是否修改成功做相应的处理
	//修改权限处理
	public function updPower(){
		$relation_id = $this->objInput->postInt('relation_id');
		$client_account = $this->objInput->postInt('client_account');
		$role_arr=explode(',', $this->objInput->postStr('role_str'));
		$cookie_account = $this->user['wms_account'];
		
		//去尾
		array_pop($role_arr);
		$mClientRoleRelation = ClsFactory::Create('Model.mClientRoleRelation');
		//通过角色id删会员角色对应关系
		$mClientRoleRelation->delClientRoleRelationByCompositeKey($client_account);
		//添加对应关系
		if(!empty($role_arr)) {
			$nowdate = date('Y-m-d H:i:s');
			foreach ($role_arr as $role) {
				$data_arr[] = array(
					'client_account'=>$client_account,
					'role_code'=>$role,
					'add_account'=>$cookie_account,
					'add_date'=>$nowdate
				);		
			}	
		}
		
		$mClientRoleRelation->addClientRoleRelationBat($data_arr);
			
		$this->redirect('Power/powerUpd/account/' . $client_account . '/upd_success/success');
	}
}

