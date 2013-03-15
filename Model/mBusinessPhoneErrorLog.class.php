<?php
class mBusinessPhoneErrorLog extends mBase{
    protected $_dBusinessPhoneErrorLog = null;
    
    public function __construct() {
        $this->_dBusinessPhoneErrorLog = ClsFactory::Create("Data.dBusinessPhoneErrorLog");
    }
    
    public function addBusinessPhoneErrorLog($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dBusinessPhoneErrorLog->addBusinessPhoneErrorLog($dataarr, $is_return_id);
    }
}