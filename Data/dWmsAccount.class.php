<?php
class dWmsAccount extends dBase{
    protected $_tablename = 'wmw_wms_account';
    protected $_fields = array(
        'wms_account',
        'wms_password',
        'wms_name',
        'wms_email',
        'add_time',
    );
    
    protected $_pk = 'wms_account';
    protected $_index_list = array(
        'wms_account'
    );
    
    public function getWmsAccountByUid($wms_accounts) {
        return $this->getInfoByPk($wms_accounts);
    }
    
    public function addWmsAccount($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function modifyWmsAccount($dataarr, $wmsaccount) {
        return $this->modify($dataarr, $wmsaccount);
    }
}