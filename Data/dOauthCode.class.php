<?php

class dOauthCode extends dBase{
	
	protected $_tablename = 'uc_oauth_codes';
    protected $_fields = array(
      'code',
      'client_id',
      'redirect_uri',
      'expires',
      'scope'
    );
    protected $_pk = 'code';
    protected $_index_list = array(
        'code'
    );
    
    //通过uid查询数据
    public function getOauthCodeById($ids) {
        return $this->getInfoByPk($ids);
    }
    
    public function addOauthCode($datas) {
        return $this->add($datas);
    }

}