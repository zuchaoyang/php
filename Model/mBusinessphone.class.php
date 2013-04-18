<?php

class mBusinessphone extends mBase {
	
	protected $_dBusinessphone = null;
	
	public function __construct() {
		$this->_dBusinessphone = ClsFactory::Create('Data.dBusinessphone');
	}
	
    public function addWwwBussinessPhoneErrorLog($dataarr) {
        if (empty($dataarr) || !is_array($dataarr)) {
			return false;
		}
		
		$PhoneErrorLog_info = $this->_dBusinessphone->addWwwBussinessPhoneErrorLog($dataarr);
		
		return !empty($PhoneErrorLog_info) ? $PhoneErrorLog_info : false;
    }

	public function getbusinessphonebyalias_id($alias_ids) {
		if(empty($alias_ids)){
			return false;
		}
		
		$phonenum_uidinfo = $this->_dBusinessphone->getbusinessphonebyalias_id($alias_ids);
		return !empty($phonenum_uidinfo) ? $phonenum_uidinfo : false;
	}

	public function addbusinessphone($dataarr) {
		if (empty($dataarr) || !is_array($dataarr)) {
			return false;
		}
		
		return $this->_dBusinessphone->addbusinessphone($dataarr);
	}

	public function addphoneinfo($dataarr) {
	    if (empty($dataarr) || !is_array($dataarr)) {
	        return false;
	    }
	    
	    return $this->_dBusinessphone->addphoneinfo($dataarr);
	}

    public function batchmangebusinessphone ($dataarr) {
	    if (empty($dataarr) && !is_array($dataarr)) {
	        return false;
	    }
	    
	    $mUser = ClsFactory::Create('Model.mUser');
        foreach ($dataarr as $key1=>$val) {
        	
            foreach ($val as $key2=>$data) {
                $userinfos = $mUser->getUserBaseByUid($data['business_num']);
                if (empty($userinfos)) {
                    $errordata=array(
                        'wbp_log_bnum'=>$data['business_num'],
                        'wbp_log_phone'=>$data['mphone_num'],
                        'wbp_log_begtime'=>date("Y-m-d H:i:s"),
                        'wbp_log_error_content'=>$dataarr['XMLCONTENT'],
                        'wbp_log_error_type'=>3,
                    	'client_ip'=>$_SERVER["REMOTE_ADDR"]
                    );
                    $this->_dBusinessphone->addWwwBussinessPhoneErrorLog($errordata);
                    unset($dataarr[$key1][$key2]);
                    
                } else {
                    $dataarr[$key1][$key2]['wbp_log_flag'] = 0;
                }
            }
        }
        
        if (empty($dataarr['START']) && empty($dataarr['CANCEL'])) {
            return false;
        }
        
	    $result = $this->_dBusinessphone->batchmangebusinessphone($dataarr);
	    
	    if (!empty($result['success'])) {
	        import("@.Common_wmw.WmwString");
	        foreach ($dataarr as $key1=>$val) {
	        	
                foreach ($val as $key2=>$data) {
                	
                    if (in_array($data['business_num'], $result)) {
                    	
                    	if (!empty($data['mphone_user_name'])) {
	                        $arrinfo = array(
	                            'client_firstchar'=>WmwString::getfirstchar($data['mphone_user_name'])
	                        );
	                        $accountArr = array(
	                        	'client_name'=>$data['mphone_user_name']
	                        );
	                        $mUser->modifyUserClientAccount($accountArr,$data['business_num']);
	                        $rs = $mUser->modifyUserClientInfo($arrinfo,$data['business_num']);
                    	}
                    	if (empty($data['mphone_user_name'])) {
                    		$rs = true;
                    	}
                    		
                        if (empty($rs)) {
                            $errordata=array(
                                'wbp_log_bnum'=>$data['business_num'],
                                'wbp_log_phone'=>$data['mphone_num'],
                            	'wbp_log_begtime'=>date("Y-m-d H:i:s"),
                                'wbp_log_error_content'=>$dataarr['XMLCONTENT'],
                                'wbp_log_error_type'=>3,
                    			'client_ip'=>$_SERVER["REMOTE_ADDR"]
                            );
                            $this->_dBusinessphone->addWwwBussinessPhoneErrorLog($errordata);
                            unset($dataarr[$key1][$key2]);
                        }
                    }
                }
            }
	    }
	    
	    return !empty($result) ? $result : false;
	}
	
	//todolist 代码不规范，参数传递不规范, 需要进一步处理返回值的问题
    public function modifyBusinessPhone($businessPhone) {
	    if (empty($businessPhone) && !is_array($businessPhone)) {
	        return false;
	    }
	    
	    $phone_id = $businessPhone ['phone_id'];
		$client_account = $businessPhone ['client_account'];
		
		$this->_dBusinessphone->modifyBusinessPhone(array('account_phone_id2'=>$phone_id), $client_account);
		//todolist 等价转换处理
		$business_list = $this->getBusinessPhone($client_account);
		$business_info = & $business_list[$client_account];
		
		$business_old_phone = $business_info['account_phone_id2'];
		if(!empty($business_old_phone)) {
		    $this->_dBusinessphone->modifyBusinessPhone(array('account_phone_id1'=>$phone_id), $business_old_phone);
		}
		
		return true;
	}
	
	public function modifyPhoneInfo($phoneInfo , $phoneNum){
	    if (empty($phoneInfo) && !is_array($phoneInfo)) {
	        return false;
	    }
	    
	    return $this->_dBusinessphone->modifyphoneinfo($phoneInfo, $phoneNum);
	}
	
	public function deletePhoneInfo($phone) {
	    if (empty($phone)) {
	        return false;
	    }
	    
	    return $this->_dBusinessphone->deletePhoneInfo($phone);
	}

	public function delbusniess($phone) {
		if (empty($phone)) {
			return false;
		} 
		
		return $this->_dBusinessphone->delbusniess($phone);
	}
	
    public function getphoneinfobyphonenum($phone_ids) {
	    if (empty($phone_ids) && !is_array($phone_ids)) {
	        return false;
	    }
	    
	    return $this->_dBusinessphone->getphoneinfobyphonenum($phone_ids);
	}
	
	public function bindingTransaction($phone_del , $phone_add , $accountPhone_add) {
	    if (empty($phone_del) && empty($phone_add) && empty($accountPhone_add)) {
	        return false;
	    }

	    if (!empty($phone_del)) {
	        $phone_del = (array)$phone_del;
	        $business_phone_list = $this->_dBusinessphone->getBusinessPhone($phone_del);
            $mUser = ClsFactory::Create('Model.mUser');
            $operate_account = $mUser->getHomeCookieAccount();//todolist
            
	        foreach ($phone_del as $key => $phone) {
	            $account_arr[] = $business_phone_list[$phone]['account_phone_id2'];
    	        $userList = $mUser->getUserBaseByUid($account_arr);
	        }
	        
	        foreach ($phone_del as $key => $phone) {
	            $account = $business_phone_list[$phone]['account_phone_id2'];
    	        $dataarr = array(
        	        'wbp_log_bnum' => $account,
        	        'wbp_log_phone' => $phone,
        	        'wbp_log_begtime' => date("Y-m-d H:i:s"),
        	        'wbp_log_opertime'=> date("Y-m-d H:i:s"),
        	        'wbp_log_type'=>2,
        	        'wbp_log_flag'=>1,
        	        'wbp_log_opername'=>$operate_account,
    	            'wbp_log_name' =>$userList[$account]['client_name']
    	        );
    	        
                $this->_dBusinessphone->addWmwBusinessPhoneLog($dataarr) ;//记录解除绑定的操作日志（添加的日志在d层）
	        }
	    }
	    
	    return $this->_dBusinessphone->bindingTransaction($phone_del , $phone_add , $accountPhone_add);

	}


	//通过班级号class_code获得该办的家长业务手机号，用于班级群发，如布置作业，通告等
	public function getParentBPhoneByClassCode($class_code) {
        if(empty($class_code)){
            return false;
        }
        
        $class_code = intval($class_code);
	    $mClientClass  = ClsFactory::Create('Model.mClientClass');
        $classUserArr = $mClientClass->getClientClassByClassCode($class_code);
        $classUserList = & $classUserArr[$class_code];

        $studentList = $familyList = array(); //声明数组
        if (!empty($classUserList)) {
        	
            foreach ($classUserList as $client_account=>$client_info) {
                $uid = intval($client_info['client_account']);
                
                if ($uid <= 0) {
                    continue;
                }
                
                if ($client_info['client_type'] == CLIENT_TYPE_STUDENT) {
                    $studentlist[] = $uid;
                } elseif ($client_info['client_type'] == CLIENT_TYPE_FAMILY) {
                    $familylist[] = $uid;
                }
            }
        }
        
        //获取家长账号
        if (!empty($studentlist) && !empty($familylist)) {
            $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
    		$familyRelations = $mFamilyRelation->getFamilyRelationByUid($studentlist);//通过family_relation表获得家长的账号信息。

            foreach ($familyRelations as $stuAccount => $parent) {//获得家长账号数组。
    	        foreach ($parent as $parentAccount) {
    	            $tmp = intval($parentAccount['family_account']);
    	            
    	            if ($tmp > 0) {
    	                $parentAccounts[] = $tmp;
    	            }
    	        }
            }
        }
        
        if (!empty($familylist) && !empty($parentAccounts)) {
           $parentAccounts = array_intersect($familylist , $parentAccounts);
           $phone_list = $this->getbusinessphonebyalias_id($parentAccounts);//通过家长账号获得business_phones
           
           if (!empty($phone_list)) {
              $phone_arr = array();
              
              foreach ($phone_list as $uid=>$phoneInfo) {
              	
                 if ($phoneInfo['business_enable'] == BUSINESS_ENABLE_YES) {
                    $phone_id = ($phoneInfo['phone_id']);//手机号不能intval
                     
                    if ($phone_id > 0) {
                        $phone_arr[] = $phone_id;
                    }
                 }
              }
           }
        }
        
	    return !empty($phone_arr) ? $phone_arr : false;

	}

    public function changeuidtophonenum($uids) {
        if (empty($uids)) {
            return false;
        }
        
	    $phonenums = $this->_dBusinessphone->changeuidtophonenum($uids);
	    
	    return !empty($phonenums) ? $phonenums : false;
    }
    
    public function getBusinessPhone($phone_ids) {
    	 if (empty($phone_ids)) {
    	 	return false;
    	 }
    	 
    	 $business_phone_list = $this->_dBusinessphone->getBusinessPhone($phone_ids);
    	 return $business_phone_list;
    }
    
    //wms 对手机绑定用户的统计
	public function phoneusernum($type) {
	    if (empty($type)) {
	        return false;
	    }
	    
	    $business_phone_num = $this->_dBusinessphone->phoneusernum($type);
	    
	    return !empty($business_phone_num) ? $business_phone_num : false;
	}
	/*
	 * 根据运营商检测手机号是不符合号段
	 * $opernum int 运营策略代号
	 * $phones array 一维所需检测的手机号数组
	 */
	public function checkPhoneNumByOperator($opernums, $phones) {
		if (empty($opernums) || empty($phones)) {
			return false;
		}
		
		import("@.Common_wmw.WmwString");
		$operArr = WmwString::operatorArr($opernums);
		$returnArr = array();
		
		foreach ($phones as $key=>&$val) {
			
			if ($val != '') {
				$vallen = strlen($val);
				
				if ($vallen === 11) {
					$cutstr = substr($val,0,3);
					
					if (in_array($cutstr,$operArr)) {
						$returnArr['success'][$val] = $val;
						unset($phones[$key]);
					} else {
						$returnArr['error'][$val] = $val;
						unset($phones[$key]);
					}
					
				} else {
					$returnArr['error'][$val] = $val;
					unset($phones[$key]);
				}
			}
		}
		
		return !empty($returnArr) ? $returnArr : false;
	}
	
	
}

