<?php

class dOauthBind extends dBase{
	
	protected $_tablename = 'uc_oauth_bind';
    protected $_fields = array(
      'id',
      'client_account',
      'social_account',
      'social_type',
      'access_token',
      'add_time'
    );
    protected $_pk = 'id';
    protected $_index_list = array(
        'client_account',
        'social_account'
    );
    
    //通过id查询数据
    public function getOauthBindById($ids) {
        return $this->getInfoByPk($ids);
    }
    
    public function getOauthBindByClientAccount($account) {
        return $this->getInfoByFk($account, 'client_account');
    }    
    
    public function getOauthBindBySocialAccount($account) {
        return $this->getInfoByFk($account, 'social_account');
    }
    
    public function getOauthBindByClientAccountAndType($params) {
        return $this->getInfo($params);
    }    

    public function getOauthBindBySocialAccountAndType($params) {
        return $this->getInfo($params);
    }     
    
    public function addOauthBind($datas) {
        return $this->add($datas);
    }
    
	public function modifyOauthBindByClientAccount($dataarr, $client_account) {
		return $this->modify($dataarr, $client_account);
	}

}