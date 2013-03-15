<?php

class dOauthToken extends dBase{
	
	protected $_tablename = 'uc_oauth_tokens';
    protected $_fields = array(
      'id',
      'access_token',
      'client_id',
      'expires',
      'scope'
    );
    protected $_pk = 'id';
    protected $_index_list = array(
        'id',
        'access_token'
    );
    
    //通过uid查询数据
    public function getOauthTokenByToId($ids) {
        return $this->getInfoByPk($ids);
    }    
    
    //通过uid查询数据
    public function getOauthTokenByToAccessToken($ids) {
        return $this->getInfoByFk($ids, 'access_token');
    }
    
    public function addOauthToken($datas) {
        return $this->add($datas);
    }

}