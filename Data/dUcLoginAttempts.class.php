<?php

class dUcLoginAttempts extends dBase {
    protected $_tablename = 'uc_login_attempts';
    protected $_fields = array(
        'client_account',
    	'client_ip',
        'attempts',
  		'upd_time',    
    );
    protected $_pk = 'client_account';
    protected $_index_list = array(
        'client_account',
    );
    
    /**
     * 通过主键获取相关信息
     * @param $uids
     */
    public function getLoginAttemptsById($uids) {
        return $this->getInfoByPk($uids);
    }
    
    /**
     * 更新用户数据
     * @param $datas
     * @param $client_account
     */    
    
    public function addLoginAttempts($datas, $is_return_id) {
        return $this->add($datas, $is_return_id);
    }
        
    
    /**
     * 更新用户数据
     * @param $datas
     * @param $client_account
     */
    public function modifyLoginAttempts($datas, $uid) {
        return $this->modify($datas, $uid);
    }
    
    /**
     * 删除用户数据
     * @param $client_account
     */
    public function delLoginAttempts($uid) {
        return $this->delete($uid);
    }
    
}