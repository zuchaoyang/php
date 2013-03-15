<?php
class mFamilyRelation extends mBase{
	protected $_dRelation = null;
	
	public function __construct() {
	    $this->_dRelation = ClsFactory::Create('Data.dFamilyRelation');
	}
	
	public function getFamilyRelationByUid($client_accounts){
	    if(empty($client_accounts)) {
	        return false;
	    }
	    
		return $relation_arr = $this->_dRelation->getFamilyRelationByUid($client_accounts);
	}
	
	public function getFamilyRelationByFamilyUid($family_accounts) {
	    if(empty($family_accounts)) {
	        return false;
	    }
	    
	    return $this->_dRelation->getFamilyRelationByFamilyUid($family_accounts);
	}
	
	//todolist  暂时替代方案
	public function modifyFamilyRelationByFamilyUid($datas, $family_accounts, $filters) {
	    if(empty($datas) || !is_array($datas) || empty($family_accounts)) {
	        return false;
	    }
	    
	    $family_accounts = is_array($family_accounts) ? current($family_accounts) : $family_accounts;
	    $relation_list = $this->getFamilyRealtionByCompositeKeys(null, $family_accounts);
	    if(!empty($relation_list)) {
	        $relation_ids = array_keys($relation_list);
	        foreach($relation_ids as $relation_id) {
	            $effect_row = $this->modifyFamilyRelation($relation_id);
	            if(empty($effect_row)){
	                $effect_row = false;
	                break;
	            }
	        }
	    }
	    
	    return !empty($effect_row) ? $effect_row : false;;
	}
	
	private function modifyFamilyRelation($dataarr, $relation_id) {
	    if(empty($relation_id)) {
	        return false;
	    }
	    
	    $this->_dRelation->modifyFamilyRelationByRelationId($relation_id);
	}
	
	public function addFamilyRelation($dataarr, $is_return_id=false){
	    if(empty($dataarr)) {
	        return false;
	    }
	    
		return $this->_dRelation->addFamilyRelation($dataarr, $is_return_id);
	}
	
	public function addFamilyRelationBat($dataarr){
	    if(empty($dataarr)) {
	        return false;
	    }
	    $dataarr = $this->filterRepeatDatasForAddBat($dataarr);
	    return $this->_dRelation->addBat($dataarr);
	}
	
	private function getFamilyRealtionByCompositeKeys($client_account, $family_account, $filters) {
	    if(empty($client_account) && $family_account) {
	        return false;
	    }
	    
	    $wheresql = array();
	    if(!empty($client_account)) {
	        $wheresql[] = "client_account in('".implode("','", (array)$client_account)."')";
	    }
	    
	    if(!empty($family_account)) {
	        $wheresql[] = "client_account in('".implode("','", (array)$family_account)."')";
	    }
	    
	    $familyrelation_list = $this->_dRelation->getInfo($wheresql);
	    if(!empty($familyrelation_list) && !empty($filters)) {
	        foreach($filters as $field => $value) {
	            $value = (array)$value;
	            foreach($familyrelation_list as $relation_id => $familyrelation) {
	                if(isset($familyrelation[$field]) && !is_array($familyrelation[$field], $value)) {
	                   unset($familyrelation_list[$relation_id]) ;
	                }
	            }
	        }
	    }
	    
	    return !empty($familyrelation_list) ? $familyrelation_list : false;
	}
	
	public function modifyFamilyRelationByCompositeKeys() {
	    
	}
	
	public function delFamilyRelationByCompositeKeys($uids, $filters) {
	    if(empty($uids)) {
	        return false;
	    }
	    
	    $relation_arr = $this->getFamilyRealtionByCompositeKeys($uids, null, $filters);
	    if(!empty($relation_arr)) {
	        $relation_ids = array();
	        foreach($relation_arr as $relation_list) {
	            $relation_ids = array_merge($relation_ids, array_keys($relation_list));
	        }
	    }
	    
	    foreach($relation_ids as $relation_id) {
	        $effect_row = $this->delFamilyRelation($relation_id);
	        if(empty($effect_row)) {
	            $effect_row = false;
	            break;
	        }
	    }
	    
	    return !empty($effect_row) ? $effect_row : false;
	}
	
	
	private function delFamilyRelation($relation_id){
	    if(!empty($relation_id)) {
	        return false;
	    }
	    
	    return $effect_rows = $this->delFamilyRelation($relation_id);
	}
	
	private function filterRepeatDatasForAddBat($dataarr) {
	     if(empty($dataarr) || !is_array($dataarr)) {
	         return false;
	     }
	     
	     //得到该表的联合主键信息
	     $compositekeys = (array)$this->_dRelation->getCompositeKeys();
	     
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
