<?php
class mCommunicateInfo extends mBase {
	
	protected $_dCommunicateInfo = null;
	public function __construct() {
		$this->_dCommunicateInfo = ClsFactory::Create('Data.dCommunicateInfo');
	}
	
	//通过孩子信息得到老师与家长的沟通内容
	public function getCommunicateByChildId($childid) {
		if(!isset($childid) || empty($childid)) {
			return false;
		}
		
		return $this->_dCommunicateInfo->getCommunicateByChildId($childid);
	}

	/**
	 * 发送沟通信息
	 * @param $CommunicateData
	 * @param $is_return_id
	 */
    public function addCommunicate($CommunicateData , $is_return_id) {
    	if(empty($CommunicateData)) {
    		return false;
    	}
    	
		return $this->_dCommunicateInfo->addCommunicate($CommunicateData , $is_return_id);
    }

    /**
     * 批量添加沟通信息
     * @param $dataarr
     */
    public function addCommunicateBat($dataarr) {
    	if(empty($dataarr)) {
    		return false;
    	}
    	
	    return $this->_dCommunicateInfo->addBat($dataarr);
    }
  

}
