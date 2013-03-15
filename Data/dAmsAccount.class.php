<?php
class dAmsAccount extends dBase{
    protected $_tablename = 'wmw_ams_account';
    protected $_fields = array(
        'ams_account',
        'ams_password',
        'ams_name',
        'ams_email',
        'add_time',
    );
    
    protected $_pk = 'ams_account';
    protected $_index_list = array(
        'ams_account'
    );
    
    public function getAmsAccountByUid($ams_accounts) {
        return $this->getInfoByPk($ams_accounts);
    }
    
    public function addAmsAccount($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function modifyAmsAccount($dataarr, $amsaccount) {
        return $this->modify($dataarr, $amsaccount);
    }
}