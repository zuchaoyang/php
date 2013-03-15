<?php
class mClassInfoHistory extends mBase{

	protected $_dClassInfoHistory = null;
	
	public function __construct() {
		$this->_dClassInfoHistory = ClsFactory::Create('Data.dClassInfoHistory');
	}
  
	public function addClassInfoHistory($datas, $is_return_id = false) {
		if (empty($datas)) {
			return false;
		}
		
		return $this->_dClassInfoHistory->addClassInfoHistory($datas, $is_return_id);
	}
}
