<?php
class dBusinessPhoneErrorLog extends mBase{

    public $_tablename = 'wmw_business_phone_error_log';
    public $_fields = array(
    	'wbp_log_id', 
		'wbp_log_bnum', 
		'wbp_log_phone', 
		'wbp_log_begtime', 
		'wbp_log_error_content', 
		'wbp_log_error_flag', 
		'wbp_log_error_type', 
		'client_ip' 
    );
    public $_pk = 'wbp_log_id';
    public $_index_list = array(
        'wbp_log_id'
    );
    public function addBusinessPhoneErrorLog($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
}