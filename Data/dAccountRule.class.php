<?php 
class dAccountRule extends dBase{

	protected $_tablename = 'wmw_account_rule';
	protected $_fields = array(
			'account_length',
	        'use_flag',
	        'use_count',
	        'add_account',
	        'add_date',
	        'upd_account',
	        'upd_date',
	);
	protected $_pk = 'account_length';
	protected $_index_list = array(
	    'account_length',
	);
	
    function modifyAccountRule($dataarr, $id){ 
        
        return $this->modify($dataarr, $id);
    }
}
