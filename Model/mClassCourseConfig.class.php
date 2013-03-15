<?php
class mClassCourseConfig extends mBase {
	
    protected $_dClassCourseConfig = null;
    
    public function __construct() {
        $this->_dClassCourseConfig = ClsFactory::Create('Data.dClassCourseConfig');
    }
    
    /*
     * 根据where条件查询数据
     * 
     */
    public function getClassCourseConfigInfo($where, $orderby, $offset, $limit) {
        return $this->_dClassCourseConfig->getInfo($where, $orderby, $offset, $limit);
    }
	/**
	 * 按用户名读取用户课程皮肤配置信息
	 * @param $account
	 */	
	public function getClassCourseConfigById($account) {
	    if(empty($account)){
	        return false;
	    }
	    
		return $this->_dClassCourseConfig->getClassCourseConfigById($account);
	}
    
	/*更改课程皮肤配置信息
	 * @param $person?ConfigArr
	 * @param $account
	 * return $effect_rows
	 */
	public function modifyClassCourseConfig($datas,$account) {
	    if(empty($datas) || !is_array($datas) || empty($account)) {
	        return false;
	    }

		return $this->_dClassCourseConfig->modifyClassCourseConfig($datas,$account);
	}
	
	/**
     * 添加课程皮肤配置信息
     * @param $account
     * @param $skinId
     * return $effect_rows
     */
    public function addClassCourseConfig($datas, $is_return_id) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dClassCourseConfig->addClassCourseConfig($datas, $is_return_id);
    }
    
	/**
     * 批量添加课程皮肤配置信息（主要用于旧数据的处理）
     * @param $account
     * @param $skinId
     * return $effect_rows
     */
    public function addBatClassCourseConfig($datas) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dClassCourseConfig->addBat($datas);
    }
}
