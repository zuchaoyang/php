<?php
class mPersonconfig extends mBase {
	
    protected $_dPersonconfig = null;
    
    public function __construct() {
        $this->_dPersonconfig = ClsFactory::Create('Data.dPersonconfig');
    }
    
    /*
     * 根据where条件查询数据
     * 
     */
    public function getPsersonConfigInfo($where, $orderby, $offset, $limit) {
        return $this->_dPersonconfig->getInfo($where, $orderby, $offset, $limit);
    }
	/**
	 * 按用户名读取用户配置信息
	 * @param $account
	 */	
	public function getPersonConfigByaccount($account) {
	    if(empty($account)){
	        return false;
	    }
	    
		return $this->_dPersonconfig->getPersonConfigByaccount($account);
	}
    
	/*更改空间配置信息
	 * @param $person?ConfigArr
	 * @param $account
	 * return $effect_rows
	 */
	public function modifyPersonConfig($personConfigArr,$account) {
	    if(empty($personConfigArr) || !is_array($personConfigArr) || empty($account)) {
	        return false;
	    }
	    
		return $this->_dPersonconfig->modifyPersonConfig($personConfigArr,$account);
	}
	
	/**
     * 添加空间配置信息
     * @param $account
     * @param $skinId
     * return $effect_rows
     */
    public function addPersonConfig($PersonConfig = array(), $is_return_id) {
        if(empty($PersonConfig) || !is_array($PersonConfig)) {
            return false;
        }
        return $this->_dPersonconfig->addPersonConfig($PersonConfig, $is_return_id);
    }
}
