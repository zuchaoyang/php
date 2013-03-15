<?php
class mWmsAccount extends mBase{
    protected $_dWmsAccount = null;
    
    public function __construct() {
        $this->_dWmsAccount = ClsFactory::Create("Data.dWmsAccount");
    }
    
    public function getWmsAccountByUid($wms_accounts) {
        if(empty($wms_accounts)) {
            return false;
        }
        return $this->_dWmsAccount->getWmsAccountByUid($wms_accounts);
    }
    
    public function getWmsAccountByName($ams_name, $offset, $limit) {
        if(empty($ams_name)) {
            return false;
        }
        
        $wherearr = array(
            "wms_name like '" . $ams_name . "%'"
        );
        
        return $this->_dWmsAccount->getInfo($wherearr, null, $offset, $limit);
    }
    
    public function addWmsAccount($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        return $this->_dWmsAccount->addWmsAccount($dataarr, $is_return_id);
    }
    
    public function modifyWmsAccount($dataarr, $wmsaccount) {
        if(empty($dataarr) || empty($wmsaccount) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dWmsAccount->modifyWmsAccount($dataarr, $wmsaccount);
    }
}