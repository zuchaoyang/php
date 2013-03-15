<?php
class mSchoolRequest extends mBase{
    protected $_dSchoolRequest = null;
    
    public function __construct() {
        $this->_dSchoolRequest = ClsFactory::Create('Data.dSchoolRequest');
    }
    
    //根据分页显示学校的申请信息
    function getSchoolRequestByUid($uid,$offset = 0,$limit = 10){
        $uid = 'baseadmin';
        if(empty($uid)) {
            return false;
        }
        //不支持多用户查询
        $uid = is_array($uid) ? array_shift($uid) : $uid;
        $wheresql = array(
            "add_account='$uid'",
        );
        $orderby = 'add_time desc';        
        
        return $this->_dSchoolRequest->getInfo($wheresql, $orderby, $offset, $limit);
    }
    
    //根据学校id查询学校的申请信息
    function getSchoolRequestBySchool_id($school_ids){
        if(empty($school_ids)) {
            return false;
        }
        
        return $this->_dSchoolRequest->getSchoolRequestBySchoolId($school_ids);
    }
    
    //添加学校申请信息
	public function addSchoolRequest($schoolInfo, $is_return_id=false){
	    if(empty($schoolInfo)) {
	        return false;
	    }
		
	    return $this->_dSchoolRequest->addSchoolRequest($schoolInfo, $is_return_id);
	}
}
