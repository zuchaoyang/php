<?php
class mBusinessPhoneLog extends mBase{
    protected $_dBusinessPhoneLog = null;
    
    public function __construct() {
        $this->_dBusinessPhoneLog = ClsFactory::Create("Data.dBusinessPhoneLog");
    }
    
    public function addBusinessPhoneLog($dataarr, $is_return_id = false) {
	    return $this->_dBusinessPhoneLog->addBusinessPhoneLog($dataarr, $is_return_id);
	}
}