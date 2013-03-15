<?php
class dBusinessPhoneLog extends dBase{
    protected $_tablename = 'wmw_business_phone_log';
	protected $_fields = array (
        'wbp_log_id', 
		'wbp_log_bnum', 
		'wbp_log_phone', 
		'wbp_log_begtime', 
		'wbp_log_name', 
		'wbp_log_type', 
		'wbp_log_flag', 
		'wbp_log_opername', 
		'wbp_log_opertime'
	);
	protected $_pk = 'wbp_log_id';
	protected $_index_list = array(
	    'wbp_log_id'
	);
	
	public function addBusinessPhoneLog($dataarr, $is_return_id) {
	    return $this->add($dataarr, $is_return_id);
	}
}