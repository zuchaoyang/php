<?php

class dOauthClient extends dBase{
	
	protected $_tablename = 'uc_oauth_clients';
    protected $_fields = array(
      'client_id',
      'app',
      'client_secret',
      'redirect_uri',
      'create_time'
    );
    protected $_pk = 'client_id';
    protected $_index_list = array(
        'client_id'
    );
    
    //通过uid查询数据
    public function getOauthClientById($ids) {
        return $this->getInfoByPk($ids);
    }
    
    public function addOauthClient($datas) {
        return $this->add($datas);
    }

}