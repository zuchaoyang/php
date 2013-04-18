<?php
class mAccountrelation extends mBase{
	
	protected $_dAccountrelation = null;
	
	public function __construct() {
		$this->_dAccountrelation = ClsFactory::Create('Data.dAccountrelation');
	}
	
	/**
	 * 获取用户的好友关系
	 * @param $client_accounts
	 */
	public function getAccountRelationByClientAccout($client_accounts,$orderby=null,$offset=null,$limit=null) {
	    if(empty($client_accounts)) {
	        return false;
	    }

	    return $this->_dAccountrelation->getAccountRelationByClientAccout($client_accounts,$orderby,$offset,$limit);
	}
    
	/*统计好友数量*/
	//todolist C调用的时候可能存在维度问题
    public function getAccountRelationCountByClientAccount($client_account) {
    	if (empty($client_account)) {
    		return false;
    	}
    	
    	$wheresql = "client_account in('" . implode("','", (array)$client_account) . "')";
        return $this->_dAccountrelation->getCount($wheresql);
    }

	/**
	 * 查找我的好友
	 * @param $add_account by lyt
	*/
	public function getAccountRelationByAddAccount($add_account) {
		if (empty($add_account)) {
			return false;
		}
		
		return $this->_dAccountrelation->getAccountRelationByAddAccount($add_account);
	}
	
    public function modifyAccountRelationByCompositeKey($arrInfoData,$account,$friendaccount, $filter) {

    	if (empty($arrInfoData) || empty($account) || empty($friendaccount)) {
    		return false;
    	}
    	
    	$relation_list = $this->getFriendGroupByfriendaccount($account, $friendaccount, $filter);
    	if(!empty($relation_list)) {
    	    $relation_ids = array_keys($relation_list);
    	}
    	if(!empty($relation_ids)) {
    	    foreach($relation_ids as $relation_id) {
    	        $effect_row = $this->_dAccountrelation->modifyAccountRelation($arrInfoData, $relation_id);
    	    }
    	}
    	
    	return !empty($effect_row) ? $effect_row : false;
    }
    
    public function delAccountRelationByCompositeKey($account,$friendaccount, $filters) {
        if (empty($account) || empty($friendaccount)) {
    		return false;
    	}
    	
    	$account = is_array($account) ? array_shift($account) : $account;
    	$friendaccount = is_array($friendaccount) ? array_shift($friendaccount) : $friendaccount;
    	
        $relation_list = $this->getFriendGroupByfriendaccount($account,$friendaccount, $filters);
        if(!empty($relation_list)) {
	        $relation_ids = array_keys($relation_list);
	    }
	    
	    $effect_row = 0;
	    if(!empty($relation_ids)) {
    	    foreach($relation_ids as $relation_id) {
    	        $this->delAccountRelation($relation_id) && $effect_row++;
    	    }
    	}
    	
    	return $effect_row;
    }
    
    private function getFriendGroupByfriendaccount($account,$friendaccount, $filters){

        if(empty($account) || empty($friendaccount)) {
            return false;
        }
        
        $account = is_array($account) ? array_shift($account) : $account;
        $friendaccount = is_array($friendaccount) ? array_shift($friendaccount) : $friendaccount;
        
        $wheresql[] = !empty($account) ? "client_account =$account" : "";
        $wheresql[] = !empty($friendaccount) ? "friend_account=" . $friendaccount : "";
        
        $accoaunt_relation_list = $this->_dAccountrelation->getInfo($wheresql);
        if(!empty($accoaunt_relation_list) && !empty($filters)) {
            foreach($filters as $field => $value) {
                
                $value = (array)$value;
                foreach($accoaunt_relation_list as $relation_id => $account_relation) {
                    if(isset($account_relation[$field]) && !in_array($account_relation[$field], $value)) {
                        unset($accoaunt_relation_list[$relation_id]);
                    }
                }
            }
        }
        
        return $this->_dAccountrelation->getInfo($wheresql);
    }
    
    function getaccountrelationbyuid($uids,$offset,$limit,$filters=array()){
        if(empty($uids)) return false;
        
        $wheresql = " client_account in( ".implode(',' , (array)$uids)." )";
        
        $Accountrelationinfo = $this->_dAccountrelation->getInfo($wheresql, null, $offset, $limit);
        if(!empty($Accountrelationinfo) && !empty($filters)){
            foreach($filters as $field=>$values){
                $values = is_array($values) ? $values : array($values);
                foreach($Accountrelationinfo as $id=>$relationinfo) {
                    if(isset($relationinfo[$field]) && !in_array($relationinfo[$field] , $values)) {
                        unset($Accountrelationinfo[$id]);
                    }
                }
            }
         }
         
        return !empty($Accountrelationinfo) ? $Accountrelationinfo : false;
    }
    
    public function getGroupFriendsByarrData($arrdata,$offset,$limit) {
        if(empty($arrdata)) {
            return false;
        }
        
       return $this->_dAccountrelation->getInfo($arrdata,'relation_id desc',$offset,$limit);
    }
    
    
    
    Public function getGroupFriendsByFriendGroup($group_ids,$orderby,$offset,$limit) {
        if(empty($group_ids)) {
            return false;
        }

        $wheresql = " friend_group in( ".implode(',' , (array)$group_ids)." )";
        
        $Accountrelationinfo = $this->_dAccountrelation->getInfo($wheresql,$orderby,$offset,$limit);
        
        return !empty($Accountrelationinfo) ? $Accountrelationinfo : false;
    }
    
    /**
     * 判断用户关系
     * 
     */
    public function getAccountTrelationByUidAndFriendAccount($client_account, $friend_account) {
        if (empty($client_account) || empty($friend_account)) {
            return false;
        }
        $where = array(
            "client_account='$client_account'",
            "friend_account='$friend_account'"
        ); 
        $realation_arr = $this->_dAccountrelation->getInfo($where, 'relation_id desc', 0, 1);
        
        return !empty($realation_arr) ? true : false;
    } 
    /**
     * 添加信息
     * @param $dataarr
     * @param $is_return_id
     */
    function addAccountRelation ($dataarr, $is_return_id) {
        if(empty($dataarr)) {
        	return false;
        }
        
	    return $this->_dAccountrelation->addAccountRelation($dataarr, $is_return_id);
    }
    
	/**
     * 修改关系信息
     * @param $dataarr
     * @param $relation_id
     */
    public function modifyAccountRelation ($dataarr,$relation_id) {
        if(empty($dataarr) || empty($relation_id)) {
        	return false;
        }
        
        return $this->_dAccountrelation->modifyAccountRelation($dataarr, $relation_id);
    }
    
    private function delAccountRelation($relation_id){
        if(empty($relation_id)) {
        	return false;
        }
        
        return $this->_dAccountrelation->delaccountrelation($relation_id);
    }
    
    /*****************************************************************************
     * M层辅助函数
     ****************************************************************************/
}


























