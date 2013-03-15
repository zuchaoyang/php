<?php
class dSquadron extends dBase {
    protected $_tablename = null;
    protected $_fields = array();
    protected $_pk = null;
    protected $_index_list = array();
    
	public function _initialize() {
         $this->connectDb('wm_cy', true);
    }
    
    public function switchToSquadron() {
        $this->_tablename = 'squadron';
        $this->_fields = array(
            'squadron_id',
    		'squadron_name',
    		'wmw_uid',
            'squadron_logo',
    		'db_createtime',
    		'db_updatetime'
        );
        $this->_pk = 'squadron_id';
        $this->_index_list = array(
            'squadron_id',
            'wmw_uid',
        );
    }
    
    public function switchToSquadronDuties() {
        $this->_tablename = 'squadron_duties';
        $this->_fields = array(
            'squadron_duties_id',
    	    'squadron_duties_name',
    	    'db_createtime',
    	    'db_updatetime'
        );
        $this->_pk = 'squadron_duties_id';
        $this->_index_list = array(
            'squadron_duties_id'
        );
    }
    
    public function switchToSquadronMemberDuties() {
        $this->_tablename = 'squadron_member_duties';
        $this->_fields = array(
            'squadron_member_duties_id',
    	    'squadron_id',
    	    'wmw_uid',
    	    'squadron_duties_id',
    	    'db_createtime',
    	    'db_updatetime',
    	    'db_delete'
        );
        $this->_pk = 'squadron_member_duties_id';
        $this->_index_list = array(
            'squadron_member_duties_id',
            'squadron_id',
            'wmw_uid',
        );
    }
    
	//$_field_squadron
	public function getSquadronById($squadron_ids) {
		$this->switchToSquadron();
		
		return $this->getInfoByPk($squadron_ids);
	}
	
	public function getSquadronByUid($wmw_uids) {
	    $this->switchToSquadron();
	    
	    return $this->getInfoByFk($wmw_uids, 'wmw_uid');
	}
	
	public function addSquadron($datas, $is_return_id = false){
	    $this->switchToSquadron();
	    
	    return $this->add($datas, $is_return_id);
	}
	
	public function modifySquadron($datas, $squadron_id) {
	    $this->switchToSquadron();
	    
	    return $this->modify($datas, $squadron_id);
	}
	
	public function delSquadronById($squadron_id) {
	    $this->switchToSquadron();
	    
	    return $this->delete($squadron_id);
	}
	
	public function getSquadronDutiesById($squadron_duties_ids) {
	    $this->switchToSquadronDuties();
	    
	    return $this->getInfoByPk($squadron_duties_ids);
	}
	
	public function addSquadronDuties($dataarr, $is_return_id = false){
	    $this->switchToSquadronDuties();
	    
	    return $this->add($dataarr, $is_return_id);
	}
	
	public function modifySquadronDuties($dataarr, $squadron_duties_id) {
	    $this->switchToSquadronDuties();
	    
	    return $this->modify($dataarr, $squadron_duties_id);
	}
	
	public function delSquadronDutiesById($squadron_duties_id) {
	    $this->switchToSquadronDuties();
	    
	    return $this->delete($squadron_duties_id);
	}
	
	//$_field_squadron_member_duties
    public function getSquadronMemberDutiesById($squadron_member_duties_ids) {
        $this->switchToSquadronMemberDuties();
        
        return $this->getInfoByPk($squadron_member_duties_ids);
    }
    
    public function getSquadronMemberDutiesBySquadronId($squadron_ids) {
        $this->switchToSquadronMemberDuties();
        
        return $this->getInfoByFk($squadron_ids, 'squadron_id');
    }
    
    public function getSquadronMemberDutiesByUid($wmw_uids) {
        $this->switchToSquadronMemberDuties();
        
        return $this->getInfoByFk($wmw_uids, 'wmw_uid');
    }
    
    public function addSquadronMemberDuties($dataarr) {
        $this->switchToSquadronMemberDuties();
        
        return $this->add($dataarr);
    }
    
    public function modifySquadronMemberDuties($dataarr, $squadron_member_duties_id) {
        $this->switchToSquadronMemberDuties();
        
        return $this->modify($dataarr, $squadron_member_duties_id);
    }
    
    public function delSquadronMemberDuties($squadron_member_duties_id) {
        $this->switchToSquadronMemberDuties();
        
        return $this->delete($squadron_member_duties_id);
    }
    
}