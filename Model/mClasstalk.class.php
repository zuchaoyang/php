<?php

class mClasstalk extends mBase {
    
	protected $_dClasstalk = null;
	
	public function __construct() {
		$this->_dClasstalk = ClsFactory::Create('Data.dClasstalk');
	}

	//保存
    public function addClassTalk($arrTalkData,$is_return_id = false) {
    	if (empty($arrTalkData)) {
			return false;    		
    	}
    	
		return $this->_dClasstalk->addClassTalk($arrTalkData,$is_return_id);
    }
	
	public function getTalkcontentinfoById($id) {
		if (empty($id)) {
			return false;    		
    	}
    	
		return $this->_dClasstalk->getTalkcontentinfoById($id);
	}
	
	
	/**
     * 最新一条数据 @sign_content
     * @param $account
     */	

    public function getLastClassTalkcontentinfoByaccount($account) {
    	if (empty($account)) {
			return false;    		
    	}
    	$wherearr = "add_account=$account";
		$orderby = "talk_id desc";
		//取最后一条
		$talkcontentinfo_list = $this->_dClasstalk->getInfo($wherearr, $orderby, 0, 1);
		return current($talkcontentinfo_list);
	}
	



	












}
