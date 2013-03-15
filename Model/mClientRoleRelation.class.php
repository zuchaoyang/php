<?php

class mClientRoleRelation extends mBase {
    
    protected $_dClientRoleRelaton = null;
    
    public function __construct() {
        $this->_dClientRoleRelaton = ClsFactory::Create('Data.dClientRoleRelation');
    }

    public function getClientRoleRelationTotalByRoleCode($role_code){
        if(empty($role_code)) {
            return false;
        }
        $role_code = is_array($role_code) ? array_shift($role_code) : $role_code;
        
        $wheresql = "role_code= $role_code";
        return $this->_dClientRoleRelaton->getCount($wheresql);
    }
    
    /**
     * todo 联合主键
     * 根据用户编号获取用户角色列表
     * @parma $client_account 用户编号  
     * @reurn 用户权限列表  
     */ 
    public function getClientRoleRelationByClientAccount($client_account) {
        if (empty($client_account)) {
    		return false;
    	}
    	return $this->_dClientRoleRelaton->getClientRoleRelationByClientAccount($client_account);
    }
    
    
    public function delClientRoleRelationByCompositeKey($client_account, $filter = array()) {
        if(empty($client_account)) {
            return false;
        }
        
        $client_account = is_array($client_account) ? $client_account : array($client_account);
        $role_code = is_array($role_code) ? $role_code : array($role_code);
        
        $wheresql[] = !empty($client_account) ? "client_account in('".implode("','", $client_account)."')" : null;
        
        $role_list = $this->_dClientRoleRelaton->getInfo($wheresql);
        
        if(!empty($role_list) && !empty($filter)) {
            foreach($filter as $feild => $value) {
                $value = (array)$value;
                foreach($role_list as $relation_id => $clientRoleRealtion) {
                    if(isset($clientRoleRealtion[$feild]) && !in_array($clientRoleRealtion[$feild], $value)) {
                        unset($role_list[$relation_id]);
                    }
                }
            }
        }
        
        $effect_rows = 0;
        if(!empty($role_list)) {
            $relation_ids = array_keys($role_list);
            foreach($relation_ids as $relation_id) {
                $this->delClientRoleRelation($relation_id) && $effect_rows++;
            }
        }
        
        return $effect_rows;
    }
    
    /**
     * todo 联合主键
     * 根据用户编号删除用户角色关系
     * @param $client_account 用户编号
     * @return 影响记录的行数
     */
    
    private function delClientRoleRelation($relation_id) {
        if (empty($relation_id)) {
    		return false;
    	}
    	
    	return $this->_dClientRoleRelaton->delClientRoleRelation($relation_id);
    }

    /**
	 * todo 联合主键
	 * 添加会员角色对应关系
	 * @param $role_arr 角色数组
	 * @param $client_account 会员编号
	 * @param $cookie_account 添加人编号
	 *  
	 * return 影响行数
	 */
	 public function addClientRoleRelationBat($dataarr) {
	 	if (empty($dataarr) || !is_array($dataarr)) {
	 		return false;
	 	}

	 	$dataarr = $this->filterRepeatDatasForAddBat($dataarr);
	 	
	    return $this->_dClientRoleRelaton->addBat($dataarr);
	 }
	 
	 private function filterRepeatDatasForAddBat($dataarr) {
	     if(empty($dataarr) || !is_array($dataarr)) {
	         return false;
	     }
	     
	     //得到该表的联合主键信息
	     $compositekeys = (array)$this->_dClientRoleRelaton->getCompositeKeys();
	     
	     //第一次过滤掉联合主键信息不存在的数据
	     if(!empty($compositekeys)) {
	         $new_dataarr = array();
    	     foreach($dataarr as $key=>$datas) {
    	         $key_for_unique = "";
    	         $passed_check = true;
    	         foreach($compositekeys as $field) {
    	             if(empty($datas[$field])) {
    	                 unset($dataarr[$key]);
    	                 $passed_check = false;
    	                 break;
    	             }
    	             $key_for_unique .= $datas[$field] . "_";
    	         }
    	         
    	         if($passed_check && !empty($key_for_unique)) {
    	             $md5_key = md5($key_for_unique);
    	             $new_dataarr[$md5_key] = $datas;
    	         }
    	     }
    	     $dataarr = & $new_dataarr;
	     }
	     
	     return $dataarr;
	 } 
}
