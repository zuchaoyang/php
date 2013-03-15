<?php
class mGazxRegistInfo extends mBase{
	protected $_dGazxRegistInfo = null;
	
	public function __construct() {
		$this->_dGazxRegistInfo = ClsFactory::Create('Data.dGazxRegistInfo');
	}
    /**
     * 查找关爱之星办理用户BY 家长账号
     * @param  $ids
     * @param  $filters //todo
     */
	public function getRegistInfoByParentAccount($ids) {
	    if(empty($ids)) {
	        return false;
	    }
	    
		$result = $this->_dGazxRegistInfo->getRegistInfoByParentAccount($ids);
        return !empty($result) ? $result : false;
	}

	
	public function addRegistInfo($datas , $is_return_id) {
	    if(empty($datas) || !is_array($datas)) {
    		return false;
	    }
	    
		return $this->_dGazxRegistInfo->addRegistInfo($datas , $is_return_id);
	}
	
}
