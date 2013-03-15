<?php
class mSquadron extends mBase {
    protected $_dSquadron = null;
    
    public function __construct() {
        $this->_dSquadron = ClsFactory::Create('Data.dSquadron');
    }
    
	/**
     * 通过中队Id获取中队的基本信息
     * @param $team_ids
     */
    public function getSquadronById($squadron_ids) {
        if(empty($squadron_ids)) {
            return false;
        }
        
        return $this->_dSquadron->getSquadronById($squadron_ids);
    }
    
    /**
     * 通过中队Uid获取中队的基本信息
     * @param $Uids
     */
    public function getSquadronByUid($Uids) {
        if(empty($Uids)) {
            return false;
        }
        
        return $this->_dSquadron->getSquadronByUid($Uids);
    }
    /**
     * 通过中队squadron_id获取中队的基本关系
     * @param $squadron_id
     */
	public function getSquadronMemberDutiesBySquadronId($squadron_ids) {
        if(empty($squadron_ids)) {
            return false;
        }

        return $this->_dSquadron->getSquadronMemberDutiesBySquadronId($squadron_ids);
    }
 	/**
     * 通过中队Id获取团队成员的完整信息
     * @param $team_ids
     */
    public function getSquadronMemberDutiesAllBySquadronId($squadron_ids) {
        if(empty($squadron_ids)) {
            return false;
        }
        
        $squadron_member_duties_list = $this->getSquadronMemberDutiesBySquadronId($squadron_ids);
        //获取用户的账号信息
        $uids = array();
        if(!empty($squadron_member_duties_list)) {
            foreach($squadron_member_duties_list as $squadron_id=>$member_list) {
                foreach($member_list as $val) {
                    $uid = intval($val['wmw_uid']);
                    if($uid <= 0) {
                        continue;
                    }
                    $uids[$uid] = $uid;
                }
            }
        }
        
        //追加用户的基本信息
        if(!empty($uids)) {
            $mUser = ClsFactory::Create('Model.mUser');
            $userlist = $mUser->getUserBaseByUid($uids);
            //从文件获取小队成员的职责名
            
            foreach($squadron_member_duties_list as $squadron_id=>$member_list) {
                foreach($member_list as $key=>$val) {
                    $uid = intval($val['wmw_uid']);
                    $val = isset($userlist[$uid]) ? array_merge($val, $userlist[$uid]) : $val;
                    //小队成员的职责名
                    $squadron_duties_id = intval($val['squadron_duties_id']);
                    $squadron_duties_list = $this->getSquadronDutiesById($squadron_duties_id);
                    $val['squadron_duties_name'] = isset($squadron_duties_list[$squadron_duties_id]) ? $squadron_duties_list[$squadron_duties_id] : "暂无";
                    
                    $member_list[$key] = $val;
                }
                $squadron_member_duties_list[$squadron_id] = $member_list;
            }
            unset($userlist);
        }
        
        return !empty($squadron_member_duties_list) ? $squadron_member_duties_list : false;
    }
	/**
     * 获取成员职责信息
     */
    public function getSquadronDutiesById($squadron_duties_id) {
    	$squadron_duties_lists = $this->_dSquadron->getSquadronDutiesById($squadron_duties_id);
    	$new_squadron_duties_list = array();
		if(!empty($squadron_duties_lists)){
			foreach($squadron_duties_lists as $squadron_duties_list){
				$new_squadron_duties_list[$squadron_duties_list['squadron_duties_id']] = $squadron_duties_list['squadron_duties_name'];
			}
		}
		
		return !empty($new_squadron_duties_list) ? $new_squadron_duties_list : false;
    }
    
    public function getSquadronMemberDutiesByUid($wmw_uids){
    	if(empty($wmw_uids)){
    		return false;
    	}
    	
    	return $this->_dSquadron->getSquadronMemberDutiesByUid($wmw_uids);
    }
    
    public function addSquadronDuties($dataarr, $return_insertid = false){
    	if(empty($dataarr)){
    		return false;
    	}

    	return $this->_dSquadron->addSquadronDuties($dataarr, $return_insertid = false);
    }
   
	public function addSquadron($dataarr, $return_insertid = false){
		if(empty($dataarr)){
    		return false;
    	}

    	return $this->_dSquadron->addSquadron($dataarr, $return_insertid = false);
    }
    
	public function addSquadronMemberDuties($dataarr){
		if(empty($dataarr)){
    		return false;
    	}

    	return $this->_dSquadron->addSquadronMemberDuties($dataarr);
    }
    

    
 	public function modifySquadronDuties($dataarr, $squadron_duties_id){
    	if(empty($dataarr) || empty($squadron_duties_id)){
    		return false;
    	}

    	return $this->_dSquadron->modifySquadronDuties($dataarr, $squadron_duties_id);
    }
    
    /**
     * 批量增加中队成员职责信息
     * @param $dataarr
     */
    public function addSquadronDutiesBat($dataarr = array()) {
        if(empty($dataarr)) {
            return false;
        }
        
        $this->_dSquadron->switchToSquadronDuties();

        return $this->_dSquadron->addBat($dataarr);
    }
   
	public function modifySquadron($dataarr, $squadron_id){
		if(empty($dataarr) || empty($squadron_id)){
    		return false;
    	}

    	return $this->_dSquadron->modifySquadron($dataarr, $squadron_id);
    }
    
	public function modifySquadronMemberDuties($dataarr, $squadron_member_duties_id){
		if(empty($dataarr) || empty($squadron_member_duties_id)){
    		return false;
    	}

    	return $this->_dSquadron->modifySquadronMemberDuties($dataarr,$squadron_member_duties_id);
    }
    
    public function delSquadronMemberDuties($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dSquadron->delSquadronMemberDuties($id);
    }
    
    /******************************************************************************
     * 特殊业务处理
     *****************************************************************************/
    /**
     * 通过中队成员职务名称获取中队职位的相关信息
     * @param $name_arr
     */
    public function getSquadronDutiesByNames($name_arr = array()) {
        if(empty($name_arr)) {
            return false;
        }
        
        $this->_dSquadron->switchToSquadronDuties();
        
        $wheresql = "squadron_duties_name in('" . implode("','", (array)$name_arr) . "')";
        
        return $this->_dSquadron->getInfo($wheresql);
    }
    
    public function addSquadronMemberDutiesBat($dataarr){
    	if(empty($dataarr)){
    		return false;
    	}
    	
    	$this->_dSquadron->switchToSquadronMemberDuties();
    	
    	return $this->_dSquadron->addBat($dataarr);
    }
    
	public function delSquadronMemberDutiesBat($squadron_member_duties_ids) {
    	if(empty($squadron_member_duties_ids)){
    		return false;
    	}
    	
    	$effect_rows = 0;
    	foreach((array)$squadron_member_duties_ids as $id) {
    	    $this->delSquadronMemberDuties($id) && $effect_rows++;
    	}
    	
    	return $effect_rows;
    }
    
}
