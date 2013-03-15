<?php
class mAmsAccount extends mBase{
    protected $_dAmsAccount = null;
    
    public function __construct() {
        $this->_dAmsAccount = ClsFactory::Create("Data.dAmsAccount");
    }
    
    public function getAmsAccountByUid($ams_accounts) {
        if(empty($ams_accounts)) {
            return false;
        }
        
        return $this->_dAmsAccount->getAmsAccountByUid($ams_accounts);
    }
    
    public function addAmsAccount($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }

        return $this->_dAmsAccount->addAmsAccount($dataarr, $is_return_id);
    }
    
    public function modifyAmsAccount($dataarr, $amsaccount) {
        if(empty($dataarr) || empty($amsaccount) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dAmsAccount->modifyAmsAccount($dataarr, $amsaccount);
    }
}