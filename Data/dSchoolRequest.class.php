<?php
class dSchoolRequest extends dBase {
    protected $_tablename = 'bms_school_request';    
    protected $_fields = array(
        'school_request_id',
        'school_id',
        'add_account',
        'add_time'
    );
    protected $_pk = 'school_request_id';
    protected $_index_list = array(
        'school_request_id',
        'school_id',
        'add_account',
    );
    
    public function _initialize() {
        $this->connectDb('bms', true);
    }
	
    //添加学校申请信息
    function addSchoolRequest($dataarr, $is_return_id=false) {
        return $this->add($dataarr, $is_return_id);
	}
	
	//通过学校id获取学校申请信息
	public function getSchoolRequestBySchoolId($school_id) {
	    return $this->getInfoByFk($school_id, 'school_id');
	}
 
}

