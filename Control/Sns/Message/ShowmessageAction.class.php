<?php

class ShowmessageAction extends SnsController{
	
	public function _initialize() {
        parent::_initialize();
    }
    
	public function index(){
		$this->assign("uid" , $this->user['client_account']);
		$this->display('home');
	}
	
	public function addMsg() {
		import('@.Control.Api.MsgApi');
		$msgApi = new MsgApi();
		$uid = $this->user['client_account'];
		$dataarr = array(
    		'content' => 'ssssssssssssssssss',
    		'to_account' => 11070004,
    		'add_account' => 11070004,
    		'add_time' => time(),
		);
		
		$msgApi->addExamMsg($uid);
	}
}
