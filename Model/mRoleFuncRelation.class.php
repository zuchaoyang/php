<?php
class mRoleFuncRelation extends mBase {
	protected $_dRoleFuncRelation = null;
	
	public function __construct() {
		$this->_dRoleFuncRelation = ClsFactory::Create('Data.dRoleFuncRelation');
	}

	//通过用户uid获取其可以使用的功能id
	public function getFuncCodeByUid($uid) {
	    if(empty($uid)) {
	        return false;
	    }
	    //查询“账号角色对应表”获取用户角色id
	    $mClientRoleRelation = ClsFactory::Create('Model.mClientRoleRelation');
	    $CR_relation_list = $mClientRoleRelation->getClientRoleRelationByClientAccount($uid);
        if(!empty($CR_relation_list[$uid])) {
            foreach($CR_relation_list[$uid] as $id => $info) {
                $role_codes[] = $info['role_code'];
            } 
        }
        
        //查询“角色功能对应表”获取功能id
        if(!empty($role_codes)) {
            $RF_relation_list = $this->getRoleFuncRelationByRoleCode($role_codes);
            if(!empty($RF_relation_list)) {
                foreach($RF_relation_list as $role_codes) {
                    foreach($RF_relation_list as $id => $info) {
                        $func_codes[] = $info['func_code'];
                    }
                }
            }
        }
        //务必去重
        return !empty($func_codes) ? array_unique($func_codes) : false;
	}
	
	public function getRoleFuncRelationByRoleCode($role_codes) { 
        $this->_dRoleFuncRelation->checkIds($role_codes);
	    if (empty($role_codes)) {
			return false;
		} 
		
		$role_codestr = implode(',', (array)$role_codes);
		$wheresql = "role_code in ($role_codestr) group by func_code";
		
		return $this->_dRoleFuncRelation->getInfo($wheresql);
	}
	
	public function delRoleFuncRelationCompositeKey($role_codes, $filter) {
	    if(empty($role_codes)) {
	        return false;
	    }
	    
	    $wheresql[] = "role_code in('".implode("','", (array)$role_codes)."')";
	    if(!empty($filter)) {
	        $wheresql[] = $filter;
	    }
	    
	    $func_list = $this->_dRoleFuncRelation->getInfo($wheresql);
	    if(!empty($func_list)) {
	        $relation_ids = array_keys($func_list);
	        foreach($relation_ids as $relation_id) {
	            $effect_row = $this->delRoleFuncRelation($relation_id);
	            if(empty($effect_row)) {
	                $effect_row = false;
	                break;
	            }
	        }
	    }
	    
	    return !empty($effect_row) ? $effect_row : false;
	}
	
    //删除 
	private function delRoleFuncRelation($relation_id) {
		if (empty($relation_id)) {
			return false;
		} 
		
		return $this->_dRoleFuncRelation->delRoleFuncRelation($relation_id);
	}
	
	/**
	 * todo 联合主键
	 * 添加角色功能对应关系
	 * @param $func_arr 功能数组
	 * @param $role_code 角色编号
	 * @param $cookie_account 当前用户编号
	 *  
	 * return 影响行数
	 */
	 public function addRoleFuncRelationBat($dataarr){
	 	if (empty($dataarr) || !is_array($dataarr)) {
	 		return false;
	 	}
	 	
	 	$dataarr = $this->filterRepeatDatasForAddBat($dataarr);
	    return $this->_dRoleFuncRelation->addBat($dataarr);
	    
	 }
	 
	  private function filterRepeatDatasForAddBat($dataarr) {
	     if(empty($dataarr) || !is_array($dataarr)) {
	         return false;
	     }
	     
	     //得到该表的联合主键信息
	     $compositekeys = (array)$this->_dRoleFuncRelation->getCompositeKeys();
	     
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