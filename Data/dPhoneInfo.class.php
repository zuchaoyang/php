<?php
class dPhoneInfo extends dBase{
    protected $_tablename = 'wmw_phone_info';
	protected $_fields = array (
        'phone_id', 
		'business_enable_time', 
		'business_enable', 
		'phone_status', 
		'flag', 
		'dbcreatetime', 
		'dbupdatetime', 
		'phone_type'
	);
	protected $_pk = 'phone_id';
	protected $_index_list = array(
	    'phone_id'
	);
	
	public function getPhoneInfoById($phone_ids) {
	    return $this->getInfoByPk($phone_ids);
	}
	
	public function addPhoneInfo($dataarr, $is_return_id) {
	    return $this->add($dataarr, $is_return_id);
	}
	
	public function delPhoneInfo($phone_id) {
	    return $this->delete($phone_id);
	}
	
	public function modifyPhoneInfo($datarr, $phone_id) {
	    return $this->modify($dataarr, $phone_id);
	}
}