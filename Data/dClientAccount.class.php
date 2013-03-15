<?php

class dClientAccount extends dBase {
    protected $_tablename = 'wmw_client_account';
    protected $_fields = array(
        'client_account',
    	'client_name',
        'client_password',
        //'client_email',
        'client_headimg',
        'client_type',
        'status',
        //'add_date',
        //'upd_date',
        //'upd_account',
        //'stop_flag',
        //'stop_date',
        //'vip_flag',
        //'vip_valid_date',
        //'client_pwd',
        //'client_score',
        //'internet_status',
        'add_time',
  		'upd_time',
        'active_date',
        'lastlogin_date',        
    
    );
    protected $_pk = 'client_account';
    protected $_index_list = array(
        'client_account',
    );
    
    public function _initialize() {
        $this->connectDb('main', true);
    }
    
    /**
     * 通过主键获取相关信息
     * @param $client_accounts
     */
    public function getUserClientAccountById($uids) {
        return $this->getInfoByPk($uids);
    }
    
    /**
     * 更新用户数据
     * @param $datas
     * @param $client_account
     */
    public function modifyUserClientAccount($datas, $uid) {
        return $this->modify($datas, $uid);
    }
    
    /**
     * 删除用户数据
     * @param $client_account
     */
    public function delUserClientAccount($uid) {
        return $this->delete($uid);
    }
    
}