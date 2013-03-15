<?php
class mUser extends mBase {
    protected $_dClientAccount = null;
    protected $_dClientInfo = null;
    //当时用户信息
    static private $cuser = false;
    
    public function __construct() {
    	$this->_dClientAccount = ClsFactory::Create('Data.dClientAccount');
    	$this->_dClientInfo = ClsFactory::Create('Data.dClientInfo');
    }
    
    /********************************************************************************
     * 基本业务处理方法
     ********************************************************************************/
	/**
     * 根据uid获取用户的相关信息
     * @param $uids
     */
    public function getClientAccountById($uids) {
        if (empty($uids)) {
            return false;
        }
        
        $user_list = $this->_dClientAccount->getUserClientAccountById($uids);
        
        foreach($user_list as $uid=>$user) {
            $user_list[$uid] = $this->parseUser($user);
        }
        
        return !empty($user_list) ? $user_list : false;
    }
    
    /**
     * 获取所有用户信息
     */
    public function getUserInfo($offset,$limit) {
         $client_info = $this->_dClientInfo->getInfo(null,null,$offset,$limit);
         $account_arr = array_keys($client_info);
         $user_info = $this->getUserBaseByUid($account_arr);
         
        return !empty($user_info) ? $user_info : false; 
    }
    
    /**
     * 根据帐号和姓名查询用户信息
     */
    public function getClientAccountByAccountAndName($user_name,$accounts,$offset=null,$limit=null) {
        if(empty($user_name) || empty($accounts)) {
            return false;
        }
        
        $wheresql = "client_account in(". implode(',',$accounts) .") and client_name like '%$user_name%'";
        $client_account_list = $this->_dClientAccount->getInfo($wheresql,null,$offset,$limit);
        
        return !empty($client_account_list) ? $client_account_list : false;
    }
    
    /**
     * 通过主键获取用户信息
     * @param $uids
     */
    public function getClientInfoById($uids) {
        if(empty($uids)) {
            return false;
        }
        
        $userlist = $this->_dClientInfo->getClientInfoById($uids);
        if(!empty($userlist)) {
        	foreach($userlist as $uid=>$user) {
        		$userlist[$uid] = self::convert_char($user);
        	}
        }
        return !empty($userlist) ? $userlist : false;
    }
    
	/**
     * 修改数据库Client_info表中的数据
     * @param $datas
     * @param $uids
     */
	public function addUserClientAccountBat($dataarr) {
		if (empty($dataarr) || !is_array($dataarr)) {
			return false;
		}
		
		return $this->_dClientAccount->addBat($dataarr);
	}

	/**
     * 数据库client_info增加对应的记录
     * @param $dataarr
     */
	public function addUserClientInfoBat($dataarr) {
		if (empty($dataarr)) {
			return false;
		}
		
		//用户的首字母转换成assic
		foreach($dataarr as $key=>$datas) {
			$dataarr[$key] = self::convert_ascii($datas);
		}
		
		return $this->_dClientInfo->addBat($dataarr);
	}
    
	/**
     * 修改的client_account表的信息
     * @param  $datas
     * @param  $uids
     */
	public function modifyUserClientAccount($datas , $uid) {
		if (empty($datas) || !is_array($datas) || empty($uid)) {
			return false;
		}
	    return $this->_dClientAccount->modifyUserClientAccount($datas, $uid);
	}
	
	public function modifyUserClientInfo($datas , $uid) {
	    if(empty($datas) || !is_array($datas) || empty($uid)) {
	        return false;
	    }
	    
	    $datas = self::convert_ascii($datas);
	    return $this->_dClientInfo->modifyUserClientInfo($datas, $uid);
	}
	
	/**
     * 删除client_account表中的记录
     * @param $uids
     */	
	public function delUserClientAccount($uid) {
		if (empty($uid)) {
			return false;
		}
		
	    return $this->_dClientAccount->delUserClientAccount($uid);
	}
	
    /**
     * 删除数据库表client_info表中的记录
     * @param $uids
     */
	public function delUserClientInfo($uid) {
	    if(empty($uid)) {
	        return false;
	    }
	    
	    return $this->_dClientInfo->delUserClientInfo($uid);
	}
	
	/**
	 * 删除用户 包括 client_account 和client_info表
	 * @rerutn 返回总共影响记录的行数
	 **/
	public function delUserAllInfo($uid) {
	    if(empty($uid)) {
	        return false;
	    }
	    
	    $effect_client_account = $this->delUserClientAccount($uid);
	    $effect_client_info = $this->delUserClientInfo($uid);
	    return intval($effect_client_account) + intval($effect_client_info);
	}
    
    
    /********************************************************************************
     * 基本业务扩展封装
     ********************************************************************************/
	
	/**
	 * 通过业务号获取用户的相关数据
	 * @param $uids
	 */
	public function getUserBaseByUid($uids) {
		if (empty($uids)) {
			return false;
		}
		
		$userlist = $userinfo_list = array();
		
		$userlist = $this->getClientAccountById($uids);
		$userinfo_list = $this->getClientInfoById($uids);
		//将用户附加信息和基本信息进行合并
		if(!empty($userinfo_list)) {
		    foreach($userinfo_list as $uid=>$userinfo) {
		        if(isset($userlist[$uid])) {
		            $userlist[$uid] = array_merge((array)$userlist[$uid], (array)$userinfo);
		        }
		        unset($userinfo_list[$uid]);
		    }
		}
	    
		//追加用户的扩展信息
		if (!empty($userlist)) {
    		foreach($userlist as $uid=>$user) {
    		    $userlist[$uid] = $this->parseUser($user);
    		}
		}
		
		return !empty($userlist) ? $userlist : false;
	}
	
	/**
	 * 通过用户账号获取用户的相关信息
	 * @param $uids
	 */
	public function getUserByUid($uids) {
	    if (empty($uids)) {
	        return false;
	    }
	 
	    $userlist = $this->getUserBaseByUid($uids);
	    //根据类型将用户数据进行分组
	    $school_uids_arr = $teacher_uids_arr = array();
	    if (!empty($userlist)) {
	        foreach($userlist as $key=>$user) {
	            $client_type = intval($user['client_type']);
	            $account = $user['client_account'];
	            
	            if ($client_type == CLIENT_TYPE_TEACHER) {
	                $teacher_uids_arr[$account] = $account;        //教师账号
	            } 
	            
	            $school_uids_arr[$account] = $account;             //全部账号
	        }
	    }
	    
	    //合并学生和老师的信息
	    if (!empty($school_uids_arr) || !empty($teacher_uids_arr)) {
	        $schooluserlist = $this->getUserExtByUid($school_uids_arr);

	        //补全教师相关的学校信息
	        $teacher_userlist = $this->getTeacherExtForSchoolInfoByUid($teacher_uids_arr);
	        if (!empty($teacher_userlist)) {
	            foreach($teacher_userlist as $uid=>$val) {
	                $schooluserlist[$uid]['school_info'] = $val['school_info'];
	            }
	            unset($teacher_userlist, $teacher_uids_arr);
	        }
	        
	        //将用户的学校信息进行合并
	        if (!empty($schooluserlist)) {
    	        foreach($schooluserlist as $uid=>$schooluser) {
    	            if (isset($userlist[$uid]) && !empty($schooluser)) {
    	                $userlist[$uid] = array_merge((array)$userlist[$uid] , (array)$schooluser);
    	            }
    	        }
    	        
    	        unset($schooluserlist);
	        }
	    }

	    //转换用户的枚举类型的数据
	    if (!empty($userlist)) {
	        foreach($userlist as $uid=>$user) {
	            $userlist[$uid] = $this->parseUser($user);
	        }
	    }
	    
	    return !empty($userlist) ? $userlist : false;
	}
	
	 /********************************************************************************
     * 特殊业务处理
     ********************************************************************************/
	/**
	 * (wms专用)通过用户名字获取用户信息
	 * @param $username
	 */
	public function getUserByUsername($username, $offset = 0, $limit = 10) {
	    if (empty($username)) {
	        return false;
	    }
	    
	    $username = $this->filterStringForLike($username);
	    
	    $wheresql = "client_name like '$username%'";
	    $userinfo_list = $this->_dClientAccount->getInfo($wheresql, null, $offset, $limit);
	    $uids = array_keys($userinfo_list);
	    $userlist = array();
	    if(!empty($uids)) {
	        $userlist = $this->getClientAccountById($uids);
	        if(!empty($userlist)) {
	            foreach($userlist as $uid=>$user) {
	                if(isset($userinfo_list[$uid])) {
	                    $user = array_merge((array)$user, (array)$userinfo_list[$uid]);
	                    unset($userinfo_list[$uid]);
	                }
	                //解析并合并用户其他相关信息
	                $userlist[$uid] = $this->parseUser($user);
	            }
	        }
	    }
	    
	    return !empty($userlist) ? $userlist : false;
	}
	
	/**
	 * (wms专用)通过用户类型获取用户信息
	 * @param $username
	 */
	public function getUserByUserType($utype, $offset = 0, $limit = 10) {
	    if ($utype == '') {
	        return false;
	    }
	    
	    // 用户类型是 整数
	    $utype = intval($utype);
	    $wheresql = "client_type='$utype'";
	    $userinfo_list = $this->_dClientAccount->getInfo($wheresql, null, $offset, $limit);
	    
	    $uids = array_keys($userinfo_list);
	    
	    $userlist = array();
	    if(!empty($uids)) {
	        $userlist = $this->getClientInfoById($uids);
	        if(!empty($userlist)) {
	            foreach($userlist as $uid=>$user) {
	                if(isset($userinfo_list[$uid])) {
	                    $user = array_merge((array)$user, (array)$userinfo_list[$uid]);
	                    unset($userinfo_list[$uid]);
	                }
	                $userlist[$uid] = $this->parseUser($user);
	            }
	        }
	    }
	    
	    return !empty($userlist) ? $userlist : false;
	}
	
	
	
	//获取用户对应的角色类型值，false表示对应的值不存在
	function getUserType($uid) {
	    if (empty($uid)) {
	        return false;
	    }
	     
        $uid = is_array($uid) ? array_shift($uid) : $uid;	    
	    $userlist = $this->getClientAccountByIdId($uid);
	    $user = & $userlist[$uid];
	    
	    return isset($user['client_type']) ? intval($user['client_type']) : false;
	}
 
	//wms获取账号列表
	public function getUserBase($offset = 0, $limit = 10) {
	    //查询client_account
	    $account_list = $this->_dClientAccount->getInfo(null, 'add_time desc', $offset, $limit);
	    
	    //查询client_info
	    if(!empty($account_list)) {
    	    $accounts = array_keys($account_list);
    	    $client_info_list = $this->_dClientInfo->getClientInfoById($accounts);       
	    }
	    
	    //合并成以键值组织的数组
	    if(!empty($client_info_list)) {
	        foreach($account_list as $account => $account_info) {
	            $client_info = & $client_info_list[$account];
	            if(!empty($client_info)) {
	                $account_list[$account] = array_merge($account_info, $client_info);
	                unset($client_info);
	            }
	        }
	    }
	    
	    return !empty($account_list) ? $account_list : false;
	}
	
	//(wms专用)通过类型和姓名查询出对应的账号
	public function getUserByNameAndType($username, $usertype, $offset = 0, $limit = 10) {
		if (empty($username)) {
			return false;
		}
		$username = $this->filterStringForLike($username);
		$usertype = intval($usertype);
		
		$wherearr = array();
		$wherearr[] = "client_type='$usertype'";
		$wherearr[] = "client_name like '%$username%'";

		$userinfo_list = $this->_dClientAccount->getInfo($wherearr, null, $offset, $limit);
		
		$uids = array_keys($userinfo_list);
	    
	    $userlist = array();
	    if(!empty($uids)) {
	        $userlist = $this->getClientInfoById($uids);
	        if(!empty($userlist)) {
	            foreach($userlist as $uid=>$user) {
	                if(isset($userinfo_list[$uid])) {
	                    $user = array_merge((array)$user, (array)$userinfo_list[$uid]);
	                    unset($userinfo_list[$uid]);
	                }
	                $userlist[$uid] = $this->parseUser($user);
	            }
	        }
	    }
	    
	    return !empty($userlist) ? $userlist : false;
	}
	
    //通过性别或省市区查找好友
	public function findFirendsBySexArea($sex, $area_id, $offset = 0, $limit = 10) {
	    $area_id = strval($area_id);	
    	$wherearr = array();
    	if (!empty($area_id) && strlen($area_id) >= 7) {
    		if (strlen($area_id) < 9) {
    			$area_id = str_pad($area_id, 9, '0', STR_PAD_LEFT);
    		}
    		$province_id = substr($area_id, 0, 3);
    		$city_id = substr($area_id, 3, 3);
    		$district_id = substr($area_id, 6, 3);
    		
    		if (!intval($city_id)) {
    			$up_limit = intval($province_id . '999999');
    			$down_limit = intval($province_id . '000000');
    			$wherearr[] = "area_id>=$down_limit and area_id<=$up_limit"; 
    		} elseif (!intval($district_id)) {
    			$up_limit = intval($province_id . $city_id. '999');
    			$down_limit = intval($province_id . $city_id . '000');
    			$wherearr[] = "area_id>=$down_limit and area_id<=$up_limit";
    		} else {
    			$wherearr[] = "area_id=" . intval($area_id);
    		}
    	}
    	//追加性别限制
    	$wherearr[] = "client_sex='$sex'";
    	$userinfo_list = $this->_dClientInfo->getInfo($wherearr, null, $offset, $limit);
    	
    	if(!empty($userinfo_list)) {
    		foreach($userinfo_list as $uid=>$user) {
    			$userinfo_list[$uid] = self::convert_char($user);
    		}
    	}
        
        return !empty($userinfo_list) ? $userinfo_list : false;
	}
	
    /**
     * 获取某个长度范围内的账号数量
     * @param $account_length
     */
	public function getUserTotalByAccountLength($account_length) {
		 if(empty($account_length)) {
		 	return false;
		 }
		 
		 $range_min = str_pad('1', $account_length, '0', STR_PAD_RIGHT);
	     $range_max = str_pad('9', $account_length, '9', STR_PAD_RIGHT);
	     
	     $wheresql = "client_account>='$range_min' and client_account<='$range_max'";
	     return $this->_dClientAccount->getCount($wheresql);
	}
	
    //查找家长关联的孩子账号
    public  function getChildaccount($account) {
        $clientInfo = $this->getUserBaseByUid($account);
        foreach($clientInfo as $val) {
            if ($val['client_type'] == 0) {
                $client_info[$val['client_account']] = $val;
            } else {
            	unset($val);
            }
        }
        
        return !empty($client_info) ? $client_info : false;
    }
    
	/*
     * 查询用户查看资源网站的等级权限 
     * 规则: 
     * 0：不能查看任何资源  1：能查看同步资源  3：能查看所有资源
     * 老师等级为3
     * 学生与其家长保持一致，若绑定了手机，则等级为3，否则等级为1
     * 黑龙江联通策略学校没有绑定手机号的学生和家长等级为0
     * 广州执信中学_1033和小学_1034无论何种情况等级均为0
     * 2012-04-17
     */
    public function getResourceUserLevel($client_account) {
        if (empty($client_account)) {
            return false; 
        }
        
        $user_info = $this->getUserByUid($client_account);
        $user_school_info = reset($user_info[$client_account]['school_info']);
        $operation_strategy = $user_school_info['operation_strategy'];
        $school_id = $user_school_info['school_id'];
        
        $client_type = $user_info[$client_account]['client_type'];
        $mBusinessphone = ClsFactory::Create('Model.mBusinessphone');
        $mRelation = ClsFactory::Create('Model.mFamilyRelation');
        
        /**
         * 老师学习资源不做权限限制
         *  2012-09-25 增加默认无运营策略的学校用户学习资源权限全开放
         *  2012-11-15 增加辽宁运营策略的学校用户学习资源权限全开放
         *  2012-11-16 增加广东运营策略的学校用户学习资源权限全开放
         *  2013-01-18 关闭广东运营策略的学校用户学习资源权限全开放
        */
        if ($client_type == CLIENT_TYPE_TEACHER || $operation_strategy == OPERATION_STRATEGY_DEFAULT || $operation_strategy == OPERATION_STRATEGY_LN) {
            return 3;
        }
        
        //家长
        if ($client_type == CLIENT_TYPE_FAMILY) {
        	if ($school_id == 1033 || $school_id == 1034) {
        		return 0;
        	}
        	
        	$family_info = $mRelation->getFamilyRelationByFamilyUid($client_account);
            $family_info = array_shift($family_info[$client_account]);
            $child_account = $family_info['client_account'];
            $family_list = $mRelation->getFamilyRelationByUid($child_account);
            
            $family_accounts = array();
            if(!empty($family_list)){
            	$family_list = $family_list[$child_account];
	            foreach($family_list as $family_account){
	            	$family_accounts[] = $family_account['family_account'];
	            }
            }
            
            $phone_info = $mBusinessphone->getbusinessphonebyalias_id($family_accounts);
            
            //黑龙江联通策略
        	if ($operation_strategy == OPERATION_STRATEGY_HLJ) {
        		return !empty($phone_info) ? 3 : 0;
        	}
        	
            return !empty($phone_info) ? 3 : 1;
        }
        
        //学生
        if ($client_type == CLIENT_TYPE_STUDENT) {
        	if ($school_id == 1033 || $school_id == 1034) {
        		return 0;
        	}
        	
            $family_arr = $mRelation->getFamilyRelationByUid($client_account);
            $family_list = & $family_arr[$client_account];

            $family_accounts = array();
            if(!empty($family_list)) {
            	foreach($family_list as $family) {
            		$family_accounts[] = $family['family_account'];
            	}
            	unset($family_list, $family_arr);
            }
            

            $phone_info = $mBusinessphone->getbusinessphonebyalias_id($family_accounts);

            //黑龙江联通策略
        	if ($operation_strategy == 2) {
        		return !empty($phone_info) ? 3 : 0;
        	}
        	
            return !empty($phone_info) ? 3 : 1;
        }
        
        return 0;
    }
    
    /********************************************************************************
     * 不同用户登录的cookie解析
     ********************************************************************************/
    
    //获取cookie中的token    todolist
	public function getCookieTokenInfo($token_name) {
		if (empty($token_name)) {
			return false;
		}
		
		$token = $_COOKIE[$token_name];
		
		$token_arr = token_decode($token);
//		list($passwd, $uid) = empty($token_arr) || count($token_arr) < 2 ? array('', '') : $token_arr;
		list($client_id, $username, $access_token, $expires_in, $scope) = $token_arr;
		$passwd = '';
		return array('uid' => $username, 'passwd'=> $passwd);
	}
	
    //前台获取用户cookie中的账号  todolist
	public function getHomeCookieAccount() {
		$cookie_info = $this->getCookieTokenInfo(SNS_SESSION_TOKEN);
		$uid = $cookie_info['uid'];
		
		return !empty($uid) ? $uid : false;
	}
    

	/**
	 * 获取oa登录用户的uid
	 */
	public function getOaCookieAccount() {
	    $cookie_info = $this->getCookieTokenInfo(SNS_SESSION_TOKEN);
	    return !empty($cookie_info) ? array($cookie_info['uid'], $cookie_info['passwd']) : false;
	}
	
    /********************************************************************************
     * 判断用户登录权限相关的函数
     ********************************************************************************/
	
	//获取token中的用户名和密码
    public function getCurrentUser() {
        if (!empty(self::$cuser)) return self::$cuser;
        $cookie_info = $this->getCookieTokenInfo(SNS_SESSION_TOKEN);
        $uid = $cookie_info['uid'];
        if (!empty($uid)) {
            $userlist = $this->getUserByUid($uid);
            $user = $userlist[$uid];
            self::$cuser = $user;
        }
        return !empty($user) ? $user : false;
    }
	
	
	/**
	 * 判断当前用户是否登录Oa系统
	 */
	public function isOaLogined() {
	    list($uid, $passwd) = $this->getOaCookieAccount();
	    if (!empty($uid)) {
	        $userlist = $this->getUserByUid($uid);
	        $user = & $userlist[$uid];
	        //检测用户是否有权使用oa系统
	        $mDptMember = ClsFactory::Create('Model.mDepartmentMembers');
	        $dptmember_arr = $mDptMember->getDepartmentMembersByUid($uid);
	        $dptmember_list = & $dptmember_arr[$uid];
	        if ($user['client_password'] == $passwd && !empty($dptmember_list)) {
	            return true;
	        }
	    }
	    
	    return false;
	}
	
	/**
	 * 获取当前登录的oa用户信息
	 */
	public function getOaCurrentUser() {
	    list($uid, $passwd) = $this->getOaCookieAccount();
	    $user = array();
	    if (!empty($uid)) {
	        $userlist = $this->getUserByUid($uid);
	        $user = & $userlist[$uid];
	        
    	    //获取用户所在部门的基本信息
            $mDptMember = ClsFactory::Create('Model.mDepartmentMembers');
            $dpt_member_arr = $mDptMember->getDepartmentMembersByUid($uid, GET_OA_DPT_MEMBER_WITH_ACCESS);
            $dpt_member_list = & $dpt_member_arr[$uid];
            
            $dpt_ids = $role_access_arr = array();
            if (!empty($dpt_member_list)) {
                foreach($dpt_member_list as $dpt_member) {
                    $role_access_arr[] = $dpt_member['access_name_arr'];
                    $dpt_id = $dpt_member['dpt_id'];
                    $dpt_ids[] = $dpt_id;
                } 
                unset($dpt_member_list);
            }
            
            $dpt_list = array();
            if (!empty($dpt_ids)) {
                $mDpt = ClsFactory::Create('Model.mDepartment');
                $dpt_list = $mDpt->getDepartmentById($dpt_ids);
            }
            
            $user['dpt_list'] = & $dpt_list;
            
            //处理用户的权限问题
            $total_access_list = array();
            if (!empty($role_access_arr)) {
                foreach($role_access_arr as $access_list) {
                    foreach($access_list as $key=>$val) {
                        if (!isset($total_access_list[$key])) {
                            $total_access_list[$key] = $val;
                        }
                    }
                }
            }
            $user['access_name_arr'] = $total_access_list;
	    }
	    
	    return !empty($user) ? $user : false;
	}
	
	/********************************************************************************
     * 用户一些属性的判定函数
     ********************************************************************************/
    /**
     * 检测当前用户是否需要激活
     * @return true 表示需要激活，false表示不需要激活
     */
    public function isActivated($uid) {
    	//需要激活的用户列表：学生、教师、家长
    	$needActivateClientTypeArr = array(CLIENT_TYPE_STUDENT , CLIENT_TYPE_TEACHER , CLIENT_TYPE_FAMILY);
    	//保证传入的用户的uid是唯一的 
    	$userlist = $this->getUserBaseByUid($uid);
        $user = $userlist[$uid];
    	$client_type = intval($user['client_type']);
    	if (intval($user['status']) == CLIENT_STOP_FLAG && in_array($client_type , $needActivateClientTypeArr)) {
           return true;
        } 
        
    	return false;
    }
	
	/********************************************************************************
     * 辅助类函数
     ********************************************************************************/
	/**
	 * 翻译用户中的部分数据
	 * @param  $user
	 */
	public function parseUser($user) {
	    if (empty($user)) {
	        return false;
	    }
	    
	    import("@.Common_wmw.Constancearr");
	    import("@.Common_wmw.Pathmanagement_sns");
	    require_once('Libraries/common.php');
	    
	    //用户类型
	    if (isset($user['client_type'])) {
	        $client_type = intval($user['client_type']);
	        $user['client_type_name'] = Constancearr::client_type($client_type);
	    }
	    
	    //职称
	    if (isset($user['client_title'])) {
	        $client_title = intval($user['client_title']);
	        $user['client_title_name'] = Constancearr::client_title($client_title);
	    }
	    
	    //职务
	    if (isset($user['client_job'])) {
	        $client_job = intval($user['client_job']);
	        $user['client_job_name'] = Constancearr::client_job($client_job);
	    }
	    
	    //血型
	    if (isset($user['client_blood_type'])) {
	        $client_blood_type = intval($user['client_blood_type']);
	        $user['client_blood_type_name'] = Constancearr::client_bloodtype($client_blood_type);
	    }
	    
	    //行业
	    if (isset($user['client_trade'])) {
	        $client_trade = intval($user['client_trade']);
	        $user['client_trade_name'] = Constancearr::client_trade($client_trade);
	    }
	    
	    //生肖
	    if (isset($user['client_zodiac'])) {
	        $client_zodiac = intval($user['client_zodiac']);
	        $user['client_zodiac_name'] = Constancearr::client_zodiac($client_zodiac);
	    }
	    
	    //星座
	    if (isset($user['client_constellation'])) {
	        $client_constellation = intval($user['client_constellation']);
	        $user['client_constellation_name'] = Constancearr::client_constellation($client_constellation);
	    }
	    
	    //处理用户的班级信息，学生和对应的老师对应的信息保存的格式是不一样的
	    if (isset($user['class_info'])) {
	        //如果是学生信息
	        if (!empty($user['class_info'])) {
	            $classlist = $user['class_info'];
	            if (!empty($classlist) && is_array($classlist)) {
	                foreach($classlist as $key=>$class) {
	                    $class['grade_id_name'] = Constancearr::class_grade_id(intval($class['grade_id']));
	                    $classlist[$key] = $class;
	                }
	                $user['class_info'] = $classlist;
	            }
	        }
	    }
	    
	    //解析班级成员信息
	    if (isset($user['client_class'])) {
	        $clientclasslist = $user['client_class'];
	        if (!empty($clientclasslist)) {
	            foreach($clientclasslist as $key=>$clientclass) {
	                $clientclass['client_class_role_name'] = Constancearr::classleader($clientclass['client_class_role']);
	                $clientclasslist[$key] = $clientclass;
	            }
	            $user['client_class'] = $clientclasslist;
	        }
	    }
	    
	    //亲属关系
	    if (isset($user['family_relation'])) {
	        if (isset($user['family_relation']['family_type'])) {
	            $family_type = intval($user['family_relation']['family_type']);
	            $user['family_type_name'] = Constancearr::family_relationtype($family_type);
	        } else{
	            $familyrelationlist = $user['family_relation'];
	            if (!empty($familyrelationlist) && is_array($familyrelationlist)) {
	                foreach($familyrelationlist as $key=>$relation) {
	                    $relation['family_type_name'] = Constancearr::family_relationtype(intval($relation['family_type']));
	                    $familyrelationlist[$key] = $relation;
	                }
	                $user['family_relation'] = $familyrelationlist;
	            }
	        }
	    }
	    
	    $default_user_header_img = IMG_SERVER.'/Public/images/head_pic.jpg';
	    
	    //获取用户头像信息
	    if (!empty($user['client_headimg'])) {
	        import("@.Common_wmw.Pathmanagement_sns");
	        $img_url = Pathmanagement_sns::getHeadImg($user['client_account']) . $user['client_headimg'];
	        $file_path = str_replace("\\" , "/" , WEB_ROOT_DIR.$img_url);
	        $user['client_headimg_url'] = is_file($file_path) ? $img_url : $default_user_header_img;
	    } else {
	        $user['client_headimg_url'] = $default_user_header_img;
	    }
	    
	    //用户地区信息
	    if (isset($user['area_id']) && !empty($user['area_id'])) {
	        $namearr = getAreaNameList($user['area_id']);
	        if (!empty($namearr)) {
	            $user['area_id_namearr'] = $namearr;
	            $user['area_id_name'] = implode("" , $namearr);
	        } else {
	            $user['area_id_name'] = "暂无";
	        }
	    } else {
	        $user['area_id_name'] = "暂无";
	    }
	    
	    return $user;
	}

	/**
	 * 获取教师的学校的基本扩展信息 
	 * @param $teacher_uids
	 */
	protected function getTeacherExtForSchoolInfoByUid($teacher_uids) {
	    if (empty($teacher_uids)) {
	        return false;
	    }
	    
	    $userlist = $schoolidlist = array();
	    //处理教师的相关信息,schoolteacher表中的数据关系优先考虑
	    if (!empty($teacher_uids)) {
	        $mSchoolTeacher = ClsFactory::Create('Model.mSchoolTeacher');
	        $schoolteacherlist = $mSchoolTeacher->getSchoolTeacherByTeacherUid($teacher_uids);
            foreach((array)$schoolteacherlist as $uid=>$list) {
                if (empty($list)) {
                    continue;
                }
                $schoolteacher = reset($list);
                $schoolid = intval($schoolteacher['school_id']);
                $schoolidlist[$uid] = $schoolid;
            }
	    }
	    
	    if (!empty($schoolidlist)) {
	        $schoolids = array_values($schoolidlist);
	        $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
	        $schoolinfo_list = $mSchoolInfo->getSchoolInfoById($schoolids);
	        if (!empty($schoolinfo_list)) {
	            foreach($schoolidlist as $uid=>$schoolid) {
	                if (!empty($schoolinfo_list[$schoolid])) {
	                    $userlist[$uid]['school_info'][$schoolid] = $schoolinfo_list[$schoolid];
	                }
	            }
	        }
	    }
	    
	    return !empty($userlist) ? $userlist : false;
	}
	
	/**
	 * 获取用户的通用扩展信息包括用户的班级关系，班级信息和学校信息
	 * @param $uids_arr
	 */
	protected function getUserExtByUid($uids_arr) {
	    if (empty($uids_arr)) {
	        return false;
	    }
	    
	    $userlist = array();
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $clientclasslist = $mClientClass->getClientClassByUid($uids_arr);
	    
	    $classcodes = $classcodelist = array();
	    if (!empty($clientclasslist)) {
	        foreach($clientclasslist as $uid=>$classlist) {
	            $userlist[$uid]['client_class'] = $classlist;
	            foreach((array)$classlist as $clientclass) {
	                $class_code = intval($clientclass['class_code']);
	                $classcodelist[$uid][] = $class_code;
	                $classcodes[] = $class_code;
	            }
	        }
	        unset($clientclasslist);
	    }
	    
	    $schoolidlist = array();
	    if (!empty($classcodes)) {
	        $classcodes = array_unique($classcodes);
	        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
	        $classinfolist = $mClassInfo->getClassInfoBaseById($classcodes);
	        
	        if (!empty($classinfolist)) {
                foreach($classcodelist as $uid=>$list) {
                    foreach((array)$list as $class_code) {
                        if (!empty($classinfolist[$class_code])) {
                            $classinfo = $classinfolist[$class_code];
                            $userlist[$uid]['class_info'][$class_code] = $classinfo;
                            //一个用户只能对应一个学校信息
                            $school_id = max(0, intval($classinfo['school_id']));
                            $school_id && $schoolidlist[$uid] = $school_id;
                        }
                    }
                }
                unset($classinfolist);
	        }
	    }
	    
	    if (!empty($schoolidlist)) {
	        $schoolids = array_values($schoolidlist);
	        $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
	        $schoolinfo_list = $mSchoolInfo->getSchoolInfoById($schoolids);
	        if (!empty($schoolinfo_list)) {
	            foreach($schoolidlist as $uid=>$schoolid) {
	                if (!empty($schoolinfo_list[$schoolid])) {
	                    $userlist[$uid]['school_info'][$schoolid] = $schoolinfo_list[$schoolid];
	                }
	            }
	            unset($schoolinfo_list);
	        }
	    }
	    
	    return !empty($userlist) ? $userlist : false;
	}
    
	
    //自动生成账号
    public function createNewUid() {        
        
		$mAccountRule = ClsFactory::Create('Model.mAccountRule');
        $new_account = '';
		$user_flag = 1;//使用标志
	    $current_rule = $mAccountRule->getAccountRuleByUseFlag($user_flag);
		$current_rule = array_shift($current_rule);
        $new_account  = $this->createAccount($current_rule['account_length']);
		//查询数据库$clientCoutn是否存在--$icount
		$client_account = $this->getUserBaseByUid($new_account);
		if (!empty($client_account)) {
			$new_account = $this->createNewUid();
		}
		//查询数据库wmw_account_lock锁定账号表--账号是否被锁定
		$mAccountLock = ClsFactory::Create('Model.mAccountLock');
		$account_lock = $mAccountLock->getAccountLockById($new_account);
		if (!empty($account_lock)) {
			$new_account = $this->createNewUid();
		}
		
		return $new_account;			
	}
	
	/* todo 系统发号器-产生随机数
	 * 以后修改系统发号机制时可删除此方法
	 */
	private function createAccount($num1){
				
		if($num1 <= 0) {
			$num = 8;
		} else {
			$num = $num1-1;
		}
		$connt = 0;
		while($connt<$num){
			$a[]=mt_rand(0,9);//产生随机数
			$connt=count($a);
		}
		foreach ($a as $key => $value){
			$val.=$value;			
		}
		$one  = mt_rand(1,9);
		$str = $one.$val;		
		return  $str;
	}
	
	
	/**
	 * 函数不是线程安全,每次批量获取的账号数据不要太多，保证在500个以内
	 * @param $nums
	 */
	public function getClientCountBat($nums) {
	    if (empty($nums) || $nums <= 0) {
	        return false;
	    }
	    
	    
	    $mAccountRule = ClsFactory::Create('Model.mAccountRule');
		
		$user_flag = 1;//使用标志
	    $current_rule = $mAccountRule->getAccountRuleByUseFlag($user_flag);
		$current_rule = array_shift($current_rule);
        $len = $current_rule['account_length'];

        $return_accounts = array();
	    $while_counter = 0;    //外层循环最多3次，保证不出现死循环
	    do {
    	    $rand_accounts = array();
    	    $test_times = 0;
    	    while(count($rand_accounts) < 2 * $nums && $test_times < 3 * $nums) {
    	        $account = $this->createAccount($len);
    	        if (!isset($rand_accounts[$account])) {
    	            $rand_accounts[$account] = $account;
    	        }
    	        $test_times++;
    	    }
    	    
    	    //账号过滤,1. 排除已经存在的账号信息；2. 排除已经锁定的信息
    	    $user_list = $this->getUserBaseByUid($rand_accounts);
    	    if (!empty($user_list)) {
    	        $exists_accounts = array_keys($user_list);
    	        unset($user_list);
    	        foreach($exists_accounts as $account) {
    	            unset($rand_accounts[$account]);
    	        }
    	    }
    	    
    	    $mAccountLock = ClsFactory::Create('Model.mAccountLock');
    		$lock_list = $mAccountLock->getAccountLockById($rand_accounts);
    		if (!empty($lock_list)) {
    		    $locked_accounts = array_keys($lock_list);
    		    unset($lock_list);
    		    foreach($locked_accounts as $account) {
    		        unset($rand_accounts[$account]);
    		    }
    		}
    		
    		$return_accounts = array_unique(array_merge((array)$return_accounts, (array)$rand_accounts));
    		
	    } while(count($return_accounts) < $nums && $while_counter++ < 3);
		
		return array_slice($rand_accounts, 0, $nums, true);
	}
	
    /**
     * 过滤like查询条件
     * @param $str
     */
    protected function filterStringForLike($str) {
        if(empty($str)) {
            return false;
        }
        
        //需要处理$username中的特殊字符
	    $str = addslashes(htmlspecialchars(strip_tags(trim($str))));
	    //不允许自带特殊字符
	    return str_replace(array("%" , "_") , "" , $str);
    }
    
     /**
     * 
     *字母转换为ASCII 
     */
     static protected function convert_ascii($datas) {
        if(empty($datas)) {
           return false; 
        }
        if(isset($datas['client_firstchar'])) {
        	$datas['client_firstchar'] = ord(strtoupper($datas['client_firstchar']));
        }
        
        return $datas;
    }
    
    /***
     * 
     *ASCII转换为 字母
     */
    static protected function convert_char($datas) {
        if(empty($datas)) {
            return false;
        }
        
        if(isset($datas['client_firstchar'])) {
        	$datas['client_firstchar'] = chr($datas['client_firstchar']);
        }
        
        return $datas;
    }
    
    
    
}

    
