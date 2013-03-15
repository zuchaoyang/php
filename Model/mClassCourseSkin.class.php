<?php
class mClassCourseSkin extends mBase {
	
	protected $_dClassCourseSkin = null;
	public function __construct() {
		$this->_dClassCourseSkin = ClsFactory::Create('Data.dClassCourseSkin');
	}

	/*
	 * 根据条件读取所有课程表模版 默认读取所有
	 */
	public function getClassCourseSkinList($where = null, $orderby = null, $offset = null, $limit = null) {

		return $this->_dClassCourseSkin->getInfo($where, $orderby, $offset, $limit);
	}
	
	public function getClassCourseSkinById($skin_ids) {
	    if(empty($skin_ids)) {
	        return false;
	    }
	    
	    return $this->_dClassCourseSkin->getClassCourseSkinById($skin_ids);
	}
	
	/*
	 * 批量添加课程皮肤
	 */
	public function addBatClassCourseSkin($datas) {
	    if(empty($datas) || !is_array($datas)) {
	        return false;
	    }
	    
	    return $this->_dClassCourseSkin->addBat($datas);
	}
}
