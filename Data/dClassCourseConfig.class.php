<?php

class dClassCourseConfig extends dBase {
		
    protected $_tablename = 'wmw_class_course_config';
	protected $_fields = array(
        'client_account', 
		'skin_id', 
	);
    protected $_pk = 'client_account';
    protected $_index_list = array(
        'client_account'
    );

     /**
     * 按用户名读取用户配置信息
     * @param $account
     */	
	 public function getClassCourseConfigById($clientAccount) {
		return $this->getInfoByPk($clientAccount);
    }


    /**
     * 更改空间配置信息
     * @param $account
     * @param $skinId
     * return $effect_rows
     */
    public function modifyClassCourseConfig($dataarr,$clientAccount) {
        
        return $this->modify($dataarr, $clientAccount);
    }
    
    /**
     * 添加空间配置信息
     * @param $account
     * @param $skinId
     * return $effect_rows
     */
    public function addClassCourseConfig($dataarr, $is_return_id = false) {
        return $this->add($dataarr, $is_return_id);
        
    }
    
}