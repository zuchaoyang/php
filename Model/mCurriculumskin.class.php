<?php
class mCurriculumskin extends mBase {
	
	protected $_dCurriculumskin = null;
	public function __construct() {
		$this->_dCurriculumskin = ClsFactory::Create('Data.dCurriculumskin');
	}

	//读取所有课程表模版
	public function getCurriculumSkinList() {
		return $this->_dCurriculumskin->getInfo();
	}
	
	public function getCurriculumSkinById($skin_ids) {
	    if(empty($skin_ids)) {
	        return false;
	    }
	    
	    return $this->_dCurriculumskin->getCurriculumSkinById($skin_ids);
	}
}
