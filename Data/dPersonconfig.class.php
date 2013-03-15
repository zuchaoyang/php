<?php

class dPersonconfig extends dBase {
		
    protected $_tablename = 'wmw_person_config';
	protected $_fields = array(
        'client_account', 
		'space_skin_id', 
		'space_access', 
		'space_name', 
		'curriculum_bg_id',
	);
    protected $_pk = 'client_account';
    protected $_index_list = array(
        'client_account'
    );

     /**
     * 按用户名读取用户配置信息
     * @param $account
     */	
	 public function getPersonConfigByaccount($clientAccount) {
		return $this->getInfoByPk($clientAccount);
    }


    /**
     * 更改空间配置信息
     * @param $account
     * @param $skinId
     * return $effect_rows
     */
    public function modifyPersonConfig($dataarr,$clientAccount) {
        
        return $this->modify($dataarr, $clientAccount);
    }
    
    /**
     * 添加空间配置信息
     * @param $account
     * @param $skinId
     * return $effect_rows
     */
    public function addPersonConfig($dataarr, $is_return_id = false) {
        return $this->add($dataarr, $is_return_id);
        
    }
    
}