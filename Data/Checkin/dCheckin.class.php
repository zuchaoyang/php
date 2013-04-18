<?php
class dCheckin extends dBase{
	
	protected $_tablename = 'wmw_checkin';
    protected $_fields = array(
      'checkin_id',
      'client_account',
      'add_time',
    );
    protected $_pk = 'checkin_id';
    protected $_index_list = array(
        'client_account'
    );
    
    
    //添加签到
    public function add_checkin($dataarr,$is_return) {
        if(empty($dataarr)) {
            return false;
        }
        
        return $this->add($dataarr,$is_return);
    }
}